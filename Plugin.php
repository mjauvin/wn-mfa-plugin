<?php namespace StudioAzura\MFA;

use Backend;
use Backend\Models\UserRole;
use Event;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $elevated = true;

    public function pluginDetails(): array
    {
        return [
            'name'        => 'studioazura.mfa::lang.plugin.name',
            'description' => 'studioazura.mfa::lang.plugin.description',
            'author'      => 'StudioAzura',
            'icon'        => 'icon-leaf'
        ];
    }

    public function boot(): void
    {
        \Backend\Models\User::extend(function () {
            $this->addDynamicMethod('validateAuthCode', function ($authCode) {
                return $authCode === 'test123';
            });
            $this->addDynamicMethod('mfaEnabled', function () {
                return true;
            });
        }, true);

        Event::listen('backend.auth.extendSigninView', function ($controller) {
            $controller->addJs('$/studioazura/mfa/assets/js/override-auth.js');
        });
    }
}
