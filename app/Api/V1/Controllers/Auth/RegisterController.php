<?php

/*
 * RegisterController.php
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
use FireflyIII\Events\RegisteredUser;
use FireflyIII\Exceptions\FireflyException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use FireflyIII\Support\Http\Controllers\CreateStuff;
use Illuminate\Foundation\Auth\RegistersUsers;
use FireflyIII\User;
use FireflyIII\Api\V1\Requests\Auth\RegisterRequest;
use FireflyIII\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Class RegisterController
 */
class RegisterController extends Controller
{
    use RegistersUsers;
    use CreateStuff;
    /**
     * Handle a registration request for the application.
     *
     * @return Application|Redirector|RedirectResponse
     *
     * @throws FireflyException
     * @throws ValidationException
     */
    public function register(RegisterRequest $request)
    {
        $allowRegistration = $this->allowedToRegister();
        $inviteCode        = (string)$request->get('invite_code');
        $repository        = app(UserRepositoryInterface::class);
        $validCode         = $repository->validateInviteCode($inviteCode);

        if (false === $allowRegistration && false === $validCode) {
            return response()->json(["message"=> 'Registration is currently not available :('], 204);
        }

        $user              = $this->createUser($request->all());
        // app('log')->info(sprintf('Registered new user %s', $user->email));
        event(new RegisteredUser($user));

        $this->guard()->login($user);

        if ($validCode) {
            $repository->redeemCode($inviteCode);
        }
        return response()->json(["message"=> (string)trans('firefly.registered'), "token"=> $user->createToken('Token Name')->accessToken], 200);
    }

    /**
     * @throws FireflyException
     */
    protected function allowedToRegister(): bool
    {
        // is allowed to register?
        $allowRegistration = true;
        try {
            $singleUserMode = app('fireflyconfig')->get('single_user_mode', config('firefly.configuration.single_user_mode'))->data;
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            $singleUserMode = true;
        }
        $userCount         = User::count();
        $guard             = config('auth.defaults.guard');
        if (true === $singleUserMode && $userCount > 0 && 'web' === $guard) {
            $allowRegistration = false;
        }
        if ('web' !== $guard) {
            $allowRegistration = false;
        }

        return $allowRegistration;
    }
}
