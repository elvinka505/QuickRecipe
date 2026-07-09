<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\User;
use Tests\Support\InMemoryDatabaseTestCase;

class UserIntegrationTest extends InMemoryDatabaseTestCase
{
    public function testUserCanBeSavedAndFoundByEmail(): void
    {
        $user = new User();
        $user->name = 'Elvina';
        $user->email = 'elvina@example.com';
        $user->password = 'hashed';

        $saved = $user->save();
        $found = User::findByEmail('elvina@example.com');

        $this->assertTrue($saved);
        $this->assertNotNull($user->id);
        $this->assertNotNull($found);
        $this->assertSame('Elvina', $found->name);
        $this->assertSame('elvina@example.com', $found->email);
    }

    public function testFindReturnsSavedUserById(): void
    {
        $user = new User();
        $user->name = 'Anna';
        $user->email = 'anna@example.com';
        $user->password = 'secret';
        $user->save();

        $found = User::find((int) $user->id);

        $this->assertNotNull($found);
        $this->assertSame('Anna', $found->name);
        $this->assertSame('anna@example.com', $found->email);
    }

    public function testAllReturnsSavedUsers(): void
    {
        $first = new User();
        $first->name = 'User 1';
        $first->email = 'user1@example.com';
        $first->password = 'pass1';
        $first->save();

        $second = new User();
        $second->name = 'User 2';
        $second->email = 'user2@example.com';
        $second->password = 'pass2';
        $second->save();

        $users = User::all();

        $this->assertCount(2, $users);
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    public function testUpdateChangesSavedUser(): void
    {
        $user = new User();
        $user->name = 'Old Name';
        $user->email = 'update@example.com';
        $user->password = 'oldpass';
        $user->save();

        $user->name = 'New Name';
        $user->password = 'newpass';

        $this->assertTrue($user->save());

        $updated = User::find((int) $user->id);

        $this->assertNotNull($updated);
        $this->assertSame('New Name', $updated->name);
        $this->assertSame('newpass', $updated->password);
    }

    public function testDeleteRemovesUser(): void
    {
        $user = new User();
        $user->name = 'Delete Me';
        $user->email = 'delete@example.com';
        $user->password = 'pass';
        $user->save();

        $id = (int) $user->id;

        $this->assertTrue($user->delete());
        $this->assertNull(User::find($id));
    }

    public function testDeleteReturnsFalseWhenUserHasNoId(): void
    {
        $user = new User();

        $this->assertFalse($user->delete());
    }
}
