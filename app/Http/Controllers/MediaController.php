<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\Images;

class MediaController
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'      => ['required', 'in:image,video'],
            'file'      => ['nullable', 'required_if:type,image', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'video_url' => ['nullable', 'required_if:type,video', 'url', 'max:500'],
            'caption'   => ['nullable', 'string', 'max:255'],
            'taken_on'  => ['nullable', 'date'],
        ]);

        $user = Auth::user();

        $item = new MediaItem([
            'user_id'  => $user->getKey(),
            'type'     => $data['type'],
            'caption'  => $data['caption'] ?? null,
            'taken_on' => $data['taken_on'] ?? null,
        ]);

        if ($data['type'] === MediaItem::TYPE_VIDEO) {
            $item->video_url = $data['video_url'];

            if (! $item->embedUrl()) {
                return back()->withErrors([
                    'video_url' => 'That link is not a recognised YouTube or Vimeo URL.',
                ])->withInput();
            }
        } else {
            $dir = 'gallery/' . $user->getKey();
            Storage::disk('public')->makeDirectory($dir);

            $base   = (string) Str::ulid();
            $source = $request->file('file')->getRealPath();

            // Downscale the original. A raw phone photo is ~5 MB and 4000px
            // wide; nothing on the site displays it above 1600.
            $fullOk = Images::scaleDown($source, Storage::disk('public')->path($dir . '/' . $base . '.jpg'), 1600);
            $thumbOk = Images::square($source, Storage::disk('public')->path($dir . '/' . $base . '_t.jpg'), 600);

            if (! $fullOk || ! $thumbOk) {
                return back()->withErrors([
                    'file' => 'That image could not be processed. Try a JPG, PNG or WebP.',
                ])->withInput();
            }

            $item->file_path  = $dir . '/' . $base . '.jpg';
            $item->thumb_path = $dir . '/' . $base . '_t.jpg';
        }

        $item->save();

        return back()->with('status', 'Added to your gallery.');
    }

    public function destroy(MediaItem $media): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $media->user_id === $user->getKey() || $user->hasAnyRole(Roles::PANEL),
            403,
        );

        $media->delete();

        return back()->with('status', 'Removed.');
    }
}
