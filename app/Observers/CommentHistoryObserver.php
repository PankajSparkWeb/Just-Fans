<?php

// App/Observers/CommentHistoryObserver.php

namespace App\Observers;

use App\Model\History;
use App\Model\PostComment;

class CommentHistoryObserver
{
    public function created(PostComment $comment)
    {
        History::create([
            'user_id' => $comment->user_id,
            'post_id' => $comment->post_id,
            'comment_id' => $comment->id,
            'action' => 'comment',
        ]);
    }

    // You can also define other observer methods like updated, deleted, etc. based on your requirements.
}
