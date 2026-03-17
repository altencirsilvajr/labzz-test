<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final readonly class AuthContext
{
    /** @param list<string> $roles */
    public function __construct(public string $subject, public string $email, public array $roles)
    {
    }
}
