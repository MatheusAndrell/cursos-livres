<?php

namespace App\Policies;

use TeamTeaTime\Forum\Models\Post;
use TeamTeaTime\Forum\Policies\PostPolicy as Base;

class PostPolicy extends Base
{
    public function edit($user, Post $post): bool
    {
        return $user->id === $post->author_id || $user->isAdmin();
    }

    public function delete($user, Post $post): bool
    {
        return $user->id === $post->author_id || $user->isAdmin();
    }

    public function restore($user, Post $post): bool
    {
        return $user->id === $post->author_id || $user->isAdmin();
    }
}
