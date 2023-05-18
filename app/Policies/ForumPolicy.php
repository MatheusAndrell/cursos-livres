<?php

namespace App\Policies;

use TeamTeaTime\Forum\Policies\ForumPolicy as Base;

class ForumPolicy extends Base
{
    public function createCategories($user): bool
    {
        return $user->isAdmin();
    }

    public function manageCategories($user): bool
    {
        return $this->moveCategories($user) ||
            $this->renameCategories($user);
    }

    public function moveCategories($user): bool
    {
        return $user->isAdmin();
    }

    public function renameCategories($user): bool
    {
        return $user->isAdmin();
    }

    public function markThreadsAsRead($user): bool
    {
        return $user->isAdmin();
    }

    public function viewTrashedThreads($user): bool
    {
        return $user->isAdmin();
    }

    public function viewTrashedPosts($user): bool
    {
        return $user->isAdmin();
    }
}
