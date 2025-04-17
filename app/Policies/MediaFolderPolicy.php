<?php

namespace App\Policies;

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaFolderPolicy
{
    use HandlesAuthorization;
  
    public function view(User $user, MediaFolder $folder)
    {
        return $user->id === $folder->owner_id;
    }

    public function update(User $user, MediaFolder $folder)
    {
        return $user->id === $folder->owner_id;
    }

    public function delete(User $user, MediaFolder $folder)
    {
        return $user->id === $folder->owner_id;
    }
}
