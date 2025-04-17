<?php

namespace App\Providers;

use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\MediaFile;
use App\Models\MediaFolder;
use App\Models\Template;
use App\Policies\MediaFilePolicy;
use App\Policies\MediaFolderPolicy;
use App\Policies\TemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MediaFolder::class => MediaFolderPolicy::class,
        MediaFile::class => MediaFilePolicy::class,
        Template::class => TemplatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Sanctum::authenticateAccessTokensUsing(
            static function (PersonalAccessToken $accessToken, bool $is_valid) {
                return $accessToken->expired_at ? $is_valid && !$accessToken->expired_at->isPast() : $is_valid;
            }
        );
    }
}
