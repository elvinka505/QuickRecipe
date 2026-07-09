<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserFromRowTest extends TestCase
{
    public function testFromRowBuildsUserObject(): void
    {
        $user = User::fromRow([
            'id' => 3,
            'name' => 'Elvina',
            'email' => 'elvina@example.com',
            'password' => 'hashed-password',
        ]);

        $this->assertSame(3, $user->id);
        $this->assertSame('Elvina', $user->name);
        $this->assertSame('elvina@example.com', $user->email);
        $this->assertSame('hashed-password', $user->password);
    }
}
