<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a comment on a task.
     */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:65535',
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            $comment->load('user');
            return response()->json([
                'success' => true,
                'comment' => $comment,
            ]);
        }

        return back()->with('success', 'Comment added.');
    }
}
