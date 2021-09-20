<?php
declare(strict_types=1);

namespace Tests\MySQL;

use Tests\MySQL as MySQLTest;
use Yuptogun\Dootong\Varieties\MySQL as Dootong;

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
        $model = new User;
        $source = $this->getPDO()->query("SELECT * FROM users");
        return $withTrashed
            ? $model->withTrashed()->get($source)
            : $model->get($source);
    }
}
