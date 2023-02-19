<?php declare(strict_types=1);

namespace Tests\MySQL;

use RuntimeException;
use Tests\MySQL as MySQLTest;
use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Headaches\MySQL;

class User extends Dootong
{
    protected function getAttributes(): array
    {
        return ['id', 'email', 'name', 'password', 'created_at'];
    }

    protected function getRequiredAttributes(): array
    {
        return ['email', 'password'];
    }

    protected function getHiddenAttributes(): array
    {
        return [];
    }

    protected function getAttributeCastings(): array
    {
        return [
            'id'         => 'increment',
            'password'   => 'password',
            'created_at' => 'datetime',
        ];
    }

    protected function getDeletedAtName(): ?string
    {
        return 'deleted_at';
    }
}

final class BasicTest extends MySQLTest
{
    public static function teardownAfterClass(): void
    {
        self::$pdo->exec("DELETE FROM users WHERE email LIKE '%@basic.test'");

        parent::teardownAfterClass();
    }

    /**
     * @testdox `Dootong` fails to `get()` when no diagnose is given
     */
    public function testDootongGetWithoutHeadache(): void
    {
        // arrange
        $dto = new User;

        // assert
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Please diagnose() this Headache!');

        // act
        $dto->get();
    }

    /**
     * @testdox `Dootong` fails to `set()` when no prescription is given
     */
    public function testDootongSetWithoutHeadache(): void
    {
        // arrange
        $headache = new MySQL($this->getPDO());
        $headache->diagnose("SELECT * FROM users");
        $dootong = User::sufferFrom($headache);

        // assert
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Please prescribe() this Headache!');

        // act
        $dootong->set(['foo', 'bar']);
    }

    /**
     * @testdox you can `diagnose()` your `Headache`
     */
    public function testDootongGetEasyMode(): void
    {
        // arrange
        $dootong = User::sufferFrom(
            (new MySQL($this->getPDO()))
                ->diagnose("SELECT * FROM users WHERE email LIKE '%@dootong.test'")
        );

        // act
        $users = $dootong->get();

        // assert
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertIsInt($user->id);
            $this->assertNull($user->password);
            $this->assertTrue($user->isPassword($user->name));
        }
    }

    /**
     * @testdox you can `prescribe()` your `Headache`
     */
    public function testDootongSetEasyMode(): void
    {
        // arrange
        $name = 'foo';
        $email = 'bar@basic.test';
        $password = 'dee';
        $dootong = User::sufferFrom(
            (new MySQL($this->getPDO()))
                ->diagnose("SELECT * FROM users WHERE id = :id")
                ->prescribe("INSERT INTO users (`name`, email, `password`) VALUES (:name, :email, :password)")
        );

        // act
        $newUserID = $dootong->set([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);
        $newUser = $dootong->get([
            'id' => $newUserID
        ])->current();

        // assert
        $this->assertInstanceOf(User::class, $newUser);
        $this->assertSame($name, $newUser->name);
        $this->assertSame($email, $newUser->email);
        $this->assertTrue($newUser->isPassword($password));
    }
}
