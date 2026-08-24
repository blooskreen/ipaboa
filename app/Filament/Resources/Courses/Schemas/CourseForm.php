<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('A course can belong to more than one category.'),

                TextInput::make('hours')
                    ->label('Training hours')
                    ->numeric()
                    ->step('0.25')
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Credited toward the 8-hour season requirement once approved.'),

                Select::make('content_type')
                    ->options(Course::TYPES)
                    ->default(Course::TYPE_LINK)
                    ->required()
                    ->live(),

                TextInput::make('content_url')
                    ->label('Content URL')
                    ->url()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('content_type') !== Course::TYPE_TEXT)
                    ->helperText('Embed or link URL. Videos are linked, never uploaded.'),

                TagsInput::make('instructors')
                    ->placeholder('Add an instructor and press Enter')
                    ->columnSpanFull(),

                FileUpload::make('image_path')
                    ->label('Course image')
                    ->image()
                    ->disk('public')
                    ->directory('courses')
                    ->maxSize(4096)
                    ->imageEditor()
                    ->imageEditorAspectRatios(['16:9'])
                    ->imageCropAspectRatio('16:9')
                    ->helperText('Recommended 1200 x 675 px (16:9 widescreen), JPG or PNG, under 2 MB. Other sizes are accepted and cropped to 16:9, so keep the subject centered. Maximum file size 4 MB.')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('courses/attachments')
                    ->fileAttachmentsVisibility('public')
                    ->columnSpanFull(),

                RichEditor::make('body')
                    ->label('Written content')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('courses/attachments')
                    ->fileAttachmentsVisibility('public')
                    ->visible(fn ($get) => $get('content_type') === Course::TYPE_TEXT)
                    ->columnSpanFull(),

                Toggle::make('requires_approval')
                    ->default(true)
                    ->helperText('On: hours wait for leadership approval. Off: hours credit immediately on completion.'),

                Toggle::make('produces_certificate')
                    ->helperText('Issues a PDF certificate when the completion is approved.'),

                Toggle::make('is_first_year')
                    ->label('First-year course'),

                Toggle::make('is_published')
                    ->helperText('Members can only see and enroll in published courses.'),
            ]);
    }
}
