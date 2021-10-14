<?php
declare(strict_types=1);

namespace Tests\MySQL;

use Exception;
use Tests\MySQL as MySQLTest;
use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Varieties\MySQL;

class User extends Dootong
{
    protected $fillable = [
        'id', 'email', 'name', 'password', 'created_at',
    ];
    protected $casts = [
        'id' => 'increment',
        'password' => 'password',
        'created_at' => 'datetime',
    ];
    protected $deletedAt = 'deleted_at';
}

final class BasicTest extends MySQLTest
{
    public function testWrongDootong(): void
    {
        $this->expectException(Exception::class);
        $wrongDootong = new User;
        $wrongDootong->get("SELECT * FROM users");
    }

    public function testCorrectDootong(): void
    {
        $correctDootong1 = new User(new MySQL($this->getPDO()));
        $correctDootong2 = User::suffer(new MySQL($this->getPDO()));

        $this->assertInstanceOf(User::class, $correctDootong1);
        $this->assertInstanceOf(User::class, $correctDootong2);
    }

    public function testCanGetUsers(): void
    {
        foreach ($this->getAllUsers() as $user) {
            /** @var User $user */
            $this->assertIsInt($user->id);
            $this->assertNotEmpty($user->email);
            $this->assertIsObject($user->created_at);
            $this->assertIsString($user->created_at->format('Y-m-d H:i:s'));
            $this->assertObjectNotHasAttribute('password', $user);
            $this->assertNull($user->deleted_at);
        }
    }

    public function testCanGetAllUsers(): void
    {
        foreach ($this->getAllUsers(true) as $user) {
            /** @var Dootong $user */
            if ($user->isSoftDeleted()) {
                $this->assertIsString($user->deleted_at->format('Y-m-d H:i:s'));
            } else {
                $this->assertNull($user->deleted_at);
            }
        }
    }

    /**
     * @return User[]
     */
    private function getAllUsers(bool $withTrashed = false): array
    {
        $dootong = User::suffer(new MySQL($this->getPDO()));
        $cause = "SELECT * FROM users";
        return $withTrashed
            ? $dootong->withTrashed()->get($cause)
            : $dootong->get($cause);
    }
}
