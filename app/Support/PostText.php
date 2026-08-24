<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Renders member-written text into a tiny, safe subset of HTML.
 *
 * Security model: the input is escaped FIRST, so any tags a member types are
 * inert text before we do anything else. Every tag in the output is one this
 * class wrote. There is no path from user input to executable markup.
 */
final class PostText
{
    public static function render(?string $raw): HtmlString
    {
        $text = trim((string) $raw);

        if ($text === '') {
            return new HtmlString('');
        }

        // 1. Everything becomes inert text.
        $text = e($text);

        // 2. Pull explicit [label](url) links out into placeholders so the
        //    bare-URL autolinker below cannot mangle them.
        $links = [];

        $text = preg_replace_callback(
            '~\[([^\]\n]{1,200})\]\((https?://[^\s)]{1,500})\)~i',
            function (array $m) use (&$links) {
                $token = '@@LINK' . count($links) . '@@';
                $links[$token] = self::anchor($m[2], $m[1]);

                return $token;
            },
            $text,
        ) ?? $text;

        // 3. Autolink bare URLs.
        $text = preg_replace_callback(
            '~(?<!["\'=>])\b(https?://[^\s<]{4,500})~i',
            fn (array $m) => self::anchor($m[1], self::shorten($m[1])),
            $text,
        ) ?? $text;

        // 4. Inline formatting.
        $text = preg_replace('~`([^`\n]{1,300})`~', '<code class="rounded bg-black/[0.07] px-1 py-0.5 text-[0.9em]">$1</code>', $text) ?? $text;
        $text = preg_replace('~\*\*(?=\S)(.+?)(?<=\S)\*\*~s', '<strong>$1</strong>', $text) ?? $text;
        $text = preg_replace('/~~(?=\S)(.+?)(?<=\S)~~/s', '<s>$1</s>', $text) ?? $text;
        $text = preg_replace('~(?<![\*\w])\*(?=\S)([^\*\n]+?)(?<=\S)\*(?![\*\w])~', '<em>$1</em>', $text) ?? $text;
        $text = preg_replace('~(?<![_\w])_(?=\S)([^_\n]+?)(?<=\S)_(?![_\w])~', '<em>$1</em>', $text) ?? $text;

        // 5. Simple bullet lines.
        $text = preg_replace('~^[\-\*]\s+(.+)$~m', '<span class="block pl-4 -indent-3">&bull;&nbsp;$1</span>', $text) ?? $text;

        // 6. Restore links, then line breaks.
        $text = strtr($text, $links);
        $text = nl2br($text, false);

        return new HtmlString($text);
    }

    protected static function anchor(string $url, string $label): string
    {
        // $url arrived already escaped; the regex only matched http/https.
        return '<a href="' . $url . '" target="_blank" rel="nofollow noopener ugc" class="text-brand underline hover:text-brand-red">'
            . $label . '</a>';
    }

    protected static function shorten(string $url): string
    {
        $clean = preg_replace('~^https?://(www\.)?~i', '', $url) ?? $url;

        return mb_strlen($clean) > 55 ? mb_substr($clean, 0, 52) . '...' : $clean;
    }
}
