<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use App\Models\Question;
use App\Support\QuestionCsv;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(Question::TYPES)
                    ->default(Question::TYPE_MC)
                    ->required()
                    ->live(),

                TextInput::make('points')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),

                Textarea::make('prompt')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                TagsInput::make('options')
                    ->label('Answer choices')
                    ->placeholder('Type a choice and press Enter')
                    ->live()
                    ->visible(fn ($get) => $get('type') === Question::TYPE_MC)
                    ->helperText('True/False choices are filled in automatically.')
                    ->columnSpanFull(),

                Select::make('correct_answer')
                    ->label('Correct answer')
                    ->multiple()
                    ->options(fn ($get) => match ($get('type')) {
                        Question::TYPE_TF => ['True' => 'True', 'False' => 'False'],
                        Question::TYPE_MC => collect((array) $get('options'))
                            ->filter()
                            ->mapWithKeys(fn ($o) => [$o => $o])
                            ->all(),
                        default => [],
                    })
                    ->visible(fn ($get) => in_array($get('type'), [Question::TYPE_MC, Question::TYPE_TF], true))
                    ->required(fn ($get) => in_array($get('type'), [Question::TYPE_MC, Question::TYPE_TF], true))
                    ->columnSpanFull(),

                TagsInput::make('correct_answer')
                    ->label('Accepted answers')
                    ->placeholder('Type an accepted answer and press Enter')
                    ->visible(fn ($get) => $get('type') === Question::TYPE_SHORT)
                    ->helperText('Leave empty to send this question to the grading queue. Add answers to auto-grade it (case and whitespace insensitive).')
                    ->columnSpanFull(),

                Textarea::make('explanation')
                    ->rows(2)
                    ->helperText('Shown only when the quiz reveals full answers.')
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('prompt')
            ->columns([
                TextInputColumn::make('sort_order')
                    ->label('#')
                    ->type('number')
                    ->rules(['integer', 'min:0']),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Question::TYPES[$state] ?? $state),

                TextColumn::make('prompt')
                    ->wrap()
                    ->limit(90)
                    ->searchable(),

                TextColumn::make('points')
                    ->alignEnd(),

                TextColumn::make('correct_answer')
                    ->label('Answer key')
                    ->formatStateUsing(fn ($state) => filled($state) ? implode(' / ', (array) $state) : 'Hand graded')
                    ->color(fn ($state) => filled($state) ? 'gray' : 'warning')
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')->options(Question::TYPES),
            ])
            ->headerActions([
                CreateAction::make(),

                Action::make('import')
                    ->label('Import CSV')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('gray')
                    ->modalHeading('Import questions from CSV')
                    ->modalSubmitActionLabel('Import')
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV file')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                            ->storeFiles(false)
                            ->required(),

                        Radio::make('mode')
                            ->label('If this quiz already has questions')
                            ->options([
                                'append'  => 'Append - add these after the existing questions',
                                'replace' => 'Replace - delete every existing question first',
                            ])
                            ->default('append')
                            ->live()
                            ->required(),

                        Toggle::make('confirm_replace')
                            ->label('Yes, permanently delete the existing questions')
                            ->helperText('This cannot be undone. Export first if you want a backup.')
                            ->visible(fn ($get) => $get('mode') === 'replace')
                            ->rules(['accepted']),
                    ])
                    ->action(function (array $data) {
                        $file = $data['file'] ?? null;

                        if (is_array($file)) {
                            $file = reset($file);
                        }

                        if (! $file) {
                            Notification::make()->title('No file received')->danger()->send();

                            return;
                        }

                        $result = QuestionCsv::parse((string) file_get_contents($file->getRealPath()));

                        // All or nothing: a file with any error imports no rows at all.
                        if ($result['errors'] !== []) {
                            Notification::make()
                                ->title('Import stopped - nothing was changed')
                                ->body(implode(chr(10), array_slice($result['errors'], 0, 10)))
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        if ($result['rows'] === []) {
                            Notification::make()->title('No questions found in that file')->warning()->send();

                            return;
                        }

                        $quiz = $this->getOwnerRecord();

                        DB::transaction(function () use ($quiz, $result, $data) {
                            if (($data['mode'] ?? 'append') === 'replace') {
                                $quiz->questions()->delete();
                                $start = 0;
                            } else {
                                $start = (int) $quiz->questions()->max('sort_order');
                            }

                            foreach ($result['rows'] as $i => $row) {
                                $row['sort_order'] = $start + (($i + 1) * 10);
                                $quiz->questions()->create($row);
                            }
                        });

                        Notification::make()
                            ->title(count($result['rows']) . ' questions imported')
                            ->success()
                            ->send();
                    }),

                Action::make('export')
                    ->label('Export CSV')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(function () {
                        $quiz = $this->getOwnerRecord();
                        $csv  = QuestionCsv::toCsv($quiz->questions()->get());
                        $name = Str::slug($quiz->title ?: 'quiz') . '-questions.csv';

                        return response()->streamDownload(
                            fn () => print ($csv),
                            $name,
                            ['Content-Type' => 'text/csv'],
                        );
                    }),

                Action::make('template')
                    ->label('Download template')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('gray')
                    ->action(fn () => response()->streamDownload(
                        fn () => print (QuestionCsv::template()),
                        'question-import-template.csv',
                        ['Content-Type' => 'text/csv'],
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
