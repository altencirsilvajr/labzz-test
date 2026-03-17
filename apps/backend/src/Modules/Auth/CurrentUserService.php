<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Infrastructure\Security\AuthContext;
use App\Modules\Users\UsersRepository;

final class CurrentUserService
{
    public function __construct(private readonly UsersRepository $users)
    {
    }

    /** @return array<string, mixed> */
    public function resolve(AuthContext $auth): array
    {
        $user = $this->users->findByAuth0Subject($auth->subject);
        if ($user !== null) {
            return $user;
        }

        $email = $auth->email;
        if ($email === '') {
            $normalizedSub = preg_replace('/[^a-zA-Z0-9._-]/', '_', $auth->subject) ?? 'user';
            $suffix = substr(hash('sha256', $auth->subject), 0, 10);
            $email = sprintf('%s.%s@auth0.local', $normalizedSub, $suffix);
        }

        return $this->users->create([
            'auth0_sub' => $auth->subject,
            'email' => $email,
            'display_name' => explode('@', $email)[0],
            'locale' => 'pt-BR',
        ]);
    }
}
