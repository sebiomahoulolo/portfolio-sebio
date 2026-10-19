<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ProfileSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $tag = $request->filled('tag') ? (string) $request->string('tag') : null;

        $posts = Post::published()
            ->when($tag, fn ($q) => $q->whereJsonContains('tags', $tag))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $tags = Post::published()->pluck('tags')->flatten()->filter()->countBy()->sortDesc()->keys()->take(12);

        return view('site.blog.index', [
            'profile' => ProfileSetting::current(),
            'posts' => $posts,
            'tags' => $tags,
            'activeTag' => $tag,
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $post = $this->findLocalized(Post::published(), $slug, 'blog.show');
        if ($post instanceof RedirectResponse) {
            return $post;
        }

        $viewed = $request->session()->get('viewed_posts', []);
        if (! in_array($post->id, $viewed, true)) {
            $post->increment('views_count');
            $request->session()->put('viewed_posts', [...$viewed, $post->id]);
        }

        $related = Post::published()
            ->whereKeyNot($post->id)
            ->when($post->tags, fn ($q) => $q->where(function ($q) use ($post) {
                foreach ($post->tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            }))
            ->latest('published_at')
            ->limit(2)
            ->get();

        return view('site.blog.show', [
            'profile' => ProfileSetting::current(),
            'post' => $post,
            'related' => $related,
        ]);
    }
}
