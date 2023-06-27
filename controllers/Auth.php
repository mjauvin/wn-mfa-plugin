<?php namespace StudioAzura\MFA\Controllers;

use Backend;
use BackendAuth;
use BackendMenu;
use Backend\Classes\Controller;
use Config;
use Flash;
use Input;
use Lang;
use Redirect;
use Session;
use System\Classes\UpdateManager;

use Winter\Storm\Auth\AuthenticationException;
use Winter\Storm\Exception\ValidationException;


class Auth extends \Backend\Controllers\Auth
{
    protected $publicActions = ['signin','authcode'];

    protected $backendUrl = null;

    public function __construct()
    {
        parent::__construct();

        $this->layout = 'auth';
        $this->backendUrl = Config::get('cms.backendUrl', 'backend');
    }


    public function signin()
    {
        try {
            $username = Input::get('login');
            $password = Input::get('password');

            if ($user = BackendAuth::findUserByLogin($username)) {
                $credentials = [
                    'login' => $username,
                    'password' => $password,
                ];

                if (BackendAuth::validate($credentials)) {
                    if (!$user->mfaEnabled()) {
                        BackendAuth::authenticate($credentials, true);
                        return Backend::redirectIntended($this->backendUrl);
                    }
                    Session::put('credentials', $credentials);
                    Flash::success('User credentials accepted');
                    return Redirect::to($this->actionUrl('authcode'));
                } else {
                    throw new AuthenticationException('Invalid credentials');
                }
            } else {
                throw new AuthenticationException('User not found');
            }

        } catch (AuthenticationException $ex) {
            Flash::error($ex->getMessage());
            return Backend::redirect($this->backendUrl);
        }
    }

    public function authcode()
    {
        $this->bodyClass = 'signin';

        // Clear Cache and any previous data to fix invalid security token issue
        $this->setResponseHeader('Cache-Control', 'no-cache, no-store, must-revalidate');

        try {
            if (post('postback')) {
                return $this->authcode_onSubmit();
            }

            $this->bodyClass .= ' preload';
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    public function authcode_onSubmit()
    {
        $authcode = Input::get('authcode');
        $credentials = Session::get('credentials');
        if ($credentials) {
            if ($user = BackendAuth::findUserByLogin($credentials['login'])) {
                if ($user->validateAuthCode($authcode)) {
                    Session::forget('credentials');
                    BackendAuth::authenticate($credentials, true);
                    return Backend::redirectIntended($this->backendUrl);
                } else {
                    Flash::error('Invalid Authorization code');
                }
            }
        } else {
            Flash::error('Missing user credentials');
            return Backend::redirect($this->backendUrl . '/auth/signin');
        }
    }
}
