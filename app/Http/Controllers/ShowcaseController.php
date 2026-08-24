<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostPollVote;
use App\Support\Images;
use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShowcaseController
{
    protected function canModerate(): bool
    {
        return Auth::user()?->hasAnyRole(Roles::CAN_PROMOTE) ?? false;
    }

    public function index(Request $request): View
    {
        $uid      = Auth::id();
        $category = $request->string('category')->toString() ?: null;

        $posts = Post::query()
            ->with([
                'user',
                'images',
                'pollOptions.votes',
                'comments.user',
            ])
            ->withCount(['likes', 'comments'])
            ->when($uid, fn ($query) => $query
                ->withExists(['likes as liked' => fn ($query) => $query->where('user_id', $uid)])
                ->with(['pollVotes' => fn ($query) => $query->where('user_id', $uid)]))
            ->when($category && isset(Post::CATEGORIES[$category]),
                fn ($query) => $query->where('category', $category))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('showcase.index', [
            'posts'       => $posts,
            'category'    => $category,
            'canModerate' => $this->canModerate(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body'           => ['nullable', 'string', 'max:5000'],
            'category'       => ['nullable', 'string', 'in:' . implode(',', array_keys(Post::CATEGORIES))],
            'feeling'        => ['nullable', 'string', 'in:' . implode(',', array_keys(Post::FEELINGS))],
            'images'         => ['nullable', 'array', 'max:4'],
            'images.*'       => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'poll_options'   => ['nullable', 'array', 'max:4'],
            'poll_options.*' => ['nullable', 'string', 'max:120'],
        ]);

        $options = collect($data['poll_options'] ?? [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();

        if ($options->count() === 1) {
            return back()->withErrors(['poll_options' => 'A poll needs at least two options.'])->withInput();
        }

        $hasImages = $request->hasFile('images');

        if (blank($data['body'] ?? null) && ! $hasImages && $options->isEmpty()) {
            return back()->withErrors(['body' => 'Write something, add a photo, or start a poll.'])->withInput();
        }

        $user = Auth::user();

        DB::transaction(function () use ($request, $data, $options, $user, $hasImages) {
            $post = Post::create([
                'user_id'  => $user->getKey(),
                'body'     => $data['body'] ?? null,
                'category' => $data['category'] ?? null,
                'feeling'  => $data['feeling'] ?? null,
            ]);

            if ($hasImages) {
                $dir = 'showcase/' . $post->getKey();
                Storage::disk('public')->makeDirectory($dir);

                foreach (array_slice($request->file('images'), 0, 4) as $i => $file) {
                    $base = (string) Str::ulid();
                    $full = $dir . '/' . $base . '.jpg';
                    $thmb = $dir . '/' . $base . '_t.jpg';

                    $ok = Images::scaleDown($file->getRealPath(), Storage::disk('public')->path($full), 1400)
                        && Images::square($file->getRealPath(), Storage::disk('public')->path($thmb), 600);

                    if ($ok) {
                        $post->images()->create([
                            'file_path'  => $full,
                            'thumb_path' => $thmb,
                            'sort_order' => $i,
                        ]);
                    }
                }
            }

            foreach ($options as $i => $label) {
                $post->pollOptions()->create(['label' => $label, 'sort_order' => $i]);
            }
        });

        return back()->with('status', 'Posted.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        abort_unless($post->user_id === Auth::id() || $this->canModerate(), 403);

        $post->delete();

        return back()->with('status', 'Post removed.');
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        return back()->with('status', 'Comment added.');
    }

    public function destroyComment(PostComment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === Auth::id() || $this->canModerate(), 403);

        $comment->delete();

        return back()->with('status', 'Comment removed.');
    }

    public function like(Post $post): RedirectResponse
    {
        $existing = PostLike::query()
            ->where('post_id', $post->getKey())
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            PostLike::create(['post_id' => $post->getKey(), 'user_id' => Auth::id()]);
        }

        return back();
    }

    public function vote(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        abort_unless($post->pollOptions()->whereKey($data['option_id'])->exists(), 422);

        // One vote per person per poll. Changing your mind replaces it.
        PostPollVote::updateOrCreate(
            ['post_id' => $post->getKey(), 'user_id' => Auth::id()],
            ['post_poll_option_id' => $data['option_id']],
        );

        return back();
    }
}
