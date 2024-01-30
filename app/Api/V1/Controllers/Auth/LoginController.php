<?php

/*
 * LoginController.php
 * Copyright (c) 2021 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers\Auth;

use FireflyIII\Api\V1\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use FireflyIII\Api\V1\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use FireflyIII\Events\ActuallyLoggedIn;
use Illuminate\Support\Facades\Log;

/**
 * Class LoginController
 */
class LoginController extends Controller
{
    use ThrottlesLogins;
    use AuthenticatesUsers;
    private string $username;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->username = 'email';
    }

    /**
     * Handle a login request to the application.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        Log::channel('audit')->info(sprintf('User is trying to login using "%s"', $request->get($this->username())));
        app('log')->debug('User is trying to login.');

        app('log')->debug('Login data is present.');

        // Copied directly from AuthenticatesUsers, but with logging added:
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            Log::channel('audit')->warning(sprintf('Login for user "%s" was locked out.', $request->get($this->username())));
            app('log')->error(sprintf('Login for user "%s" was locked out.', $request->get($this->username())));
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }
        // Copied directly from AuthenticatesUsers, but with logging added:
        if ($this->attemptLogin($request)) {
            Log::channel('audit')->info(sprintf('User "%s" has been logged in.', $request->get($this->username())));

            // if you just logged in, it can't be that you have a valid 2FA cookie.

            // send a custom login event because laravel will also fire a login event if a "remember me"-cookie
            // restores the event.
            event(new ActuallyLoggedIn($this->guard()->user()));

            return response()->json(["message"=> (string)trans('User has been logged in'),"token"=> $this->guard()->user()->createToken('Token Name')->accessToken], 200);
        }
        app('log')->warning('Login attempt failed.');

        // Copied directly from AuthenticatesUsers, but with logging added:
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        Log::channel('audit')->warning(sprintf('Login failed. Attempt for user "%s" failed.', $request->get($this->username())));

        // @noinspection PhpUnreachableStatementInspection
        return response()->json(["message"=> 'Login attempt failed :('], 400);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

}
