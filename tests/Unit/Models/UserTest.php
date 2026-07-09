<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Core\Database;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
        Database::getConnection();
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
    }

    public function testSaveInsertsNewUser(): void
    {
        $u           = new User();
        $u->name     = 'Elvina';
        $u->email    = 'elvina@test.com';
        $u->password = 'hash';
        $u->save();

        $this->assertNotNull($u->id);
        $this->assertGreaterThan(0, $u->id);
    }

    public function testSaveUpdatesExistingUser(): void
    {
        $u           = new User();
        $u->name     = 'Elvina';
        $u->email    = 'e@test.com';
        $u->password = 'hash';
        $u->save();

        $u->name = 'Updated';
        $u->save();

        $found = User::find($u->id);
        $this->assertSame('Updated', $found->name);
    }

    public function testFindByEmail(): void
    {
        $u           = new User();
        $u->name     = 'Test';
        $u->email    = 'find@test.com';
        $u->password = 'hash';
        $u->save();

        $found = User::findByEmail('find@test.com');
        $this->assertInstanceOf(User::class, $found);
        $this->assertSame('Test', $found->name);
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $this->assertNull(User::findByEmail('nobody@test.com'));
    }

    public function testFind(): void
    {
        $u           = new User();
        $u->name     = 'Find';
        $u->email    = 'findid@test.com';
        $u->password = 'hash';
        $u->save();

        $found = User::find($u->id);
        $this->assertSame($u->id, $found->id);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $this->assertNull(User::find(99999));
    }

    public function testDelete(): void
    {
        $u           = new User();
        $u->name     = 'Del';
        $u->email    = 'del@test.com';
        $u->password = 'hash';
        $u->save();
        $id = $u->id;

        $u->delete();
        $this->assertNull(User::find($id));
    }

    public function testDeleteReturnsFalseWhenNoId(): void
    {
        $u = new User();
        $this->assertFalse($u->delete());
    }

    public function testAll(): void
    {
        $u           = new User();
        $u->name     = 'A';
        $u->email    = 'a@test.com';
        $u->password = 'h';
        $u->save();

        $all = User::all();
        $this->assertNotEmpty($all);
        $this->assertInstanceOf(User::class, $all[0]);
    }

    public function testFromRow(): void
    {
        $u = User::fromRow(['id' => 1, 'name' => 'X', 'email' => 'x@x.com', 'password' => 'p']);
        $this->assertSame(1, $u->id);
        $this->assertSame('X', $u->name);
    }
}
