<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MediaFile;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaFilePolicy
{
    use HandlesAuthorization;

    public function view(User $user, MediaFile $file)
    {
        return $user->id === $file->owner_id ||
            $file->shares()->where('shared_with_id', $user->id)->exists();
    }

    public function update(User $user, MediaFile $file)
    {

        # Prevent updates if file is marked as read-only
        if ($file->read_only) {
            return false;
        }
        
        return $user->id === $file->owner_id ||
            $file->shares()
            ->where('shared_with_id', $user->id)
            ->where('permission_type', 'edit')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function delete(User $user, MediaFile $file)
    {
        return $user->id === $file->owner_id;
    }

    public function share(User $user, MediaFile $file)
    {
        return $user->id === $file->owner_id;
    }
}
