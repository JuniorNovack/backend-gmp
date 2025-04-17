<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Template $template)
    {
        return $user->id === $template->created_by;
    }

    public function update(User $user, Template $template)
    {
        return $user->id === $template->created_by;
    }

    public function delete(User $user, Template $template)
    {
        return $user->id === $template->created_by;
    }
    
}
