<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Collection;

final class QuestionCsv
{
    public const HEADERS = ['type', 'prompt', 'options', 'correct_answer', 'points', 'explanation'];

    /** Accept friendly spellings, not just the short codes. */
    protected const TYPE_ALIASES = [
        'mc'              => Question::TYPE_MC,
        'multiple choice' => Question::TYPE_MC,
        'multiple_choice' => Question::TYPE_MC,
        'tf'              => Question::TYPE_TF,
        'true/false'      => Question::TYPE_TF,
        'true false'      => Question::TYPE_TF,
        'truefalse'       => Question::TYPE_TF,
        'short'           => Question::TYPE_SHORT,
        'short answer'    => Question::TYPE_SHORT,
        'shortanswer'     => Question::TYPE_SHORT,
    ];

    public static function template(): string
    {
        return self::build([
            self::HEADERS,
            ['mc', 'Which of these is NOT a violation?', 'Traveling|Double dribble|Carry|Legal screen', 'Legal screen', '1', 'A legal screen is a permitted action.'],
            ['tf', 'The possession arrow changes after every jump ball.', '', 'False', '1', ''],
            ['short', 'Name the three documents an official should consult.', '', '', '3', 'Rulebook, casebook, points of emphasis.'],
        ]);
    }

    public static function toCsv(Collection $questions): string
    {
        $rows = [self::HEADERS];

        foreach ($questions as $q) {
            $rows[] = [
                $q->type,
                (string) $q->prompt,
                implode('|', (array) $q->options),
                implode('|', (array) $q->correct_answer),
                (string) $q->points,
                (string) $q->explanation,
            ];
        }

        return self::build($rows);
    }

    protected static function build(array $rows): string
    {
        $h = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($h, $row);
        }

        rewind($h);
        $out = stream_get_contents($h);
        fclose($h);

        return $out;
    }

    /**
     * Parse and validate. Returns every problem found rather than stopping at
     * the first, so one upload tells you everything to fix.
     *
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    public static function parse(string $contents): array
    {
        // Excel writes a UTF-8 BOM that would corrupt the first header name.
        $contents = (string) preg_replace('/^\xEF\xBB\xBF/', '', $contents);

        $h = fopen('php://temp', 'r+');
        fwrite($h, $contents);
        rewind($h);

        $header = fgetcsv($h);

        if ($header === false) {
            fclose($h);

            return ['rows' => [], 'errors' => ['The file appears to be empty.']];
        }

        $header = array_map(fn ($c) => strtolower(trim((string) $c)), $header);

        foreach (['type', 'prompt'] as $required) {
            if (! in_array($required, $header, true)) {
                fclose($h);

                return ['rows' => [], 'errors' => ["Missing required column: {$required}"]];
            }
        }

        $rows   = [];
        $errors = [];
        $line   = 1;

        while (($data = fgetcsv($h)) !== false) {
            $line++;

            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $i => $col) {
                $row[$col] = isset($data[$i]) ? trim((string) $data[$i]) : '';
            }

            $type = self::TYPE_ALIASES[strtolower($row['type'] ?? '')] ?? null;

            if ($type === null) {
                $errors[] = "Line {$line}: unknown type \"{$row['type']}\". Use mc, tf or short.";
                continue;
            }

            if (($row['prompt'] ?? '') === '') {
                $errors[] = "Line {$line}: prompt is empty.";
                continue;
            }

            $options = self::split($row['options'] ?? '');
            $correct = self::split($row['correct_answer'] ?? '');

            if ($type === Question::TYPE_TF) {
                $options = ['True', 'False'];
                $correct = array_values(array_map(
                    fn ($v) => ucfirst(strtolower($v)),
                    array_filter($correct, fn ($v) => in_array(strtolower($v), ['true', 'false'], true)),
                ));

                if (count($correct) !== 1) {
                    $errors[] = "Line {$line}: True/False needs exactly one correct answer, True or False.";
                    continue;
                }
            }

            if ($type === Question::TYPE_MC) {
                if (count($options) < 2) {
                    $errors[] = "Line {$line}: multiple choice needs at least two options separated by |.";
                    continue;
                }

                if ($correct === []) {
                    $errors[] = "Line {$line}: multiple choice needs a correct answer.";
                    continue;
                }

                $missing = array_diff($correct, $options);

                if ($missing !== []) {
                    $errors[] = "Line {$line}: correct answer \"" . implode(', ', $missing) . '" does not match any option exactly.';
                    continue;
                }
            }

            if ($type === Question::TYPE_SHORT) {
                $options = null;
            }

            $points = (int) ($row['points'] ?? 1);

            $rows[] = [
                'type'           => $type,
                'prompt'         => $row['prompt'],
                'options'        => $options,
                'correct_answer' => $correct !== [] ? $correct : null,
                'points'         => $points > 0 ? $points : 1,
                'explanation'    => ($row['explanation'] ?? '') !== '' ? $row['explanation'] : null,
            ];
        }

        fclose($h);

        return ['rows' => $rows, 'errors' => $errors];
    }

    protected static function split(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode('|', $value)),
            fn ($v) => $v !== '',
        ));
    }
}
