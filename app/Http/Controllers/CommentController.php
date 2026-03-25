<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Comment added.');
    }

public function destroy(Comment $comment)
    {
        // Kontrolli, kas kasutaja on administraator või kommentaari autor
        if (Auth::id() !== $comment->user_id && !Auth::user()->is_admin) {
            abort(403, 'Sul pole õigust seda kommentaari kustutada.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Kommentaar kustutatud.');
    }
    }