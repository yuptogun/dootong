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
     * @testdox you can "diagnose" your `Headache`
     */
    public function testDootongGetEasyMode(): void
    {
        // arrange
        $dootong = User::sufferFrom(
            (new MySQL($this->getPDO()))
                ->diagnose("SELECT * FROM users")
        );

        // act
        $users = $dootong->get();

        // assert
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertIsInt($user->id);
            $this->assertNull($user->password);
        }
    }

    public function testDootongSetEasyMode(): void
    {
        // arrange
        $dootong = User::sufferFrom(
            (new MySQL($this->getPDO()))
                ->diagnose("SELECT * FROM users WHERE id = :id")
                ->prescribe("INSERT INTO users (`name`, email, `password`) VALUES (:name, :email, :password)")
        );

        // act
        $newUserID = $dootong->set([
            'name'     => 'foo',
            'email'    => 'foo@bar.com',
            'password' => 'bar',
        ]);
        $newUser = $dootong->get([
            'id' => $newUserID
        ])->current();

        // assert
        $this->assertInstanceOf(User::class, $newUser);
        $this->assertSame('foo', $newUser->name);
        $this->assertSame('foo@bar.com', $newUser->email);
        $this->assertTrue($newUser->isPassword('bar'));
    }

//     /**
//      * @testdox you never get Dootong if Headache Variety is undetermined
//      */
//     public function testDootongWithoutVariety(): void
//     {
//         $this->expectException(Exception::class);

//         $wrongDootong = new User;
//         $wrongDootong->setGetCause("SELECT * FROM users");
//         $wrongDootong->get();
//     }

//     /**
//      * @testdox every Dootong should have (i.e. suffer from) Variety
//      */
//     public function testDootongWithVariety(): void
//     {
//         $correctDootong1 = new User(new MySQL($this->getPDO()));
//         $correctDootong2 = User::sufferFrom(new MySQL($this->getPDO()));

//         $this->assertInstanceOf(User::class, $correctDootong1);
//         $this->assertInstanceOf(User::class, $correctDootong2);
//     }

//     /**
//      * @testdox every Dootong can hold key-value pairs and cast the values
//      */
//     public function testCastings(): void
//     {
//         foreach ($this->getAllUsers() as $user) {
//             /** @var User $user */
//             $this->assertIsInt($user->id);
//             $this->assertNotEmpty($user->email);
//             $this->assertIsObject($user->created_at);
//             $this->assertIsString($user->created_at->format('Y-m-d H:i:s'));
//             $this->assertObjectNotHasAttribute('password', $user);
//             $this->assertNull($user->deleted_at);
//         }
//     }

//     /**
//      * @testdox if Dootong can be soft deleted then regarding information should be available
//      */
//     public function testSoftDelete(): void
//     {
//         foreach ($this->getAllUsers(true) as $user) {
//             /** @var Dootong $user */
//             if ($user->isSoftDeleted()) {
//                 $this->assertIsString($user->deleted_at->format('Y-m-d H:i:s'));
//             } else {
//                 $this->assertNull($user->deleted_at);
//             }
//         }
//     }

//     /**
//      * @testdox if Dootong has defined required attributes, none of them should be excluded when set()
//      */
//     public function testSetWithoutRequiredAttributes(): void
//     {
//         $this->expectException(InvalidArgumentException::class);

//         $cause = "INSERT INTO users (`name`, created_at) VALUES (:username, :created_at)";
//         $dootong = User::sufferFrom(new MySQL($this->getPDO()));
//         $dootong->set([
//             'username' => 'foo',
//             'password' => 'bar',
//             'created_at' => date('Y-m-d H:i:s'),
//         ], $cause);
//     }

//     /**
//      * @return User[]
//      */
//     private function getAllUsers(bool $withTrashed = false): array
//     {
//         $dootong = User::sufferFrom(new MySQL($this->getPDO()));
//         $cause = "SELECT * FROM users";
//         $dootong->setGetCause($cause);
//         return $withTrashed
//             ? $dootong->withTrashed()->get()
//             : $dootong->get();
//     }
}
