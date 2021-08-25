# dootong

A simple abstact dynamic pseudo-DTO ***model*** for PHP DDD.

* D-oo-TO-ng
* meaning: "headache" in Korean
* pronunciation: "do tongue"

## Quick Start

Every `Yuptogun\Dootong\Interfaces\Headache` implementation looks like the following.

```php
use Yuptogun\Dootong\Varieties\MySQL as Dootong;

class UserModel extends Dootong
{
    // required
    private $fillable = [
        'email', 'username', 'id', 'bio', 'timezone',
    ];

    // optional
    private $required = [
        'email', 'username', 'pwd',
    ];

    // optional
    private $hidden = [
        'email', 'timezone',
    ];

    // optional
    private $casts = [
        'username' => 'string',
        'email' => 'email',
        'pwd' => 'password', // password type fillable hidden by default
        'id' => 'increment',
    ];
}
```

### `get()`

Fetches from the provided repository.

```php
$userModel = new UserModel;

/** @var \PDO $pdo */
$statement = $pdo->query("SELECT * FROM users");

$users = UserModel::get($statement);
foreach ($users as $user) {
    if ($user->pwd) {
        throw new \Exception('this never happens: see fillable');
    }
    if (!is_int($user->id)) {
        throw new \Exception('this never happens: see castings');
    }
}
```

### `set()`

Registers the entity into the provided repository.

```php
$inputs = [
    'username' => 'Foo B.',
    'email' => 'foo@bar.com',
    'pwd' => '12345678',
    'bio' => '',
];

/** @var \PDO $pdo */
$statement = $pdo->query("INSERT INTO users (username, email, pwd, bio) VALUES (:username, :email, :pwd, :bio)");

$newUser = UserModel::set($statement, $inputs);
if ($newUser->pwd) {
    throw new \Exception('this never happens');
}
if (!$newUser->isPassword('12345678')) {
    throw new \Exception('this also never happens');
}

unset($inputs['password'], $inputs['bio']);
$inputs['email'] = 'bar.com';

try {
    $newUser = UserModel::set($statement, $inputs);
    echo "this never prints";
} catch (\Exception $e) {
    echo "this always prints: see required";
    echo $e->getMessage(); // {"email":["invalid"],"password":["required"]}
}
```

## Expectedly Asked Questions

### Q. But this is not a DTO is it?

No it is not a DTO, but not a non-DTO.

* It is a model.
* Its methods maintains/validates its properties, keeping its number to the least.

### Q. Why not just pure DTO with other ORMs or anything?

I don't buy the idea entirely.

* "A class with no methods" itself is a joke to me. Why not JSON then?
* The properties MUST have some attributes and characteristics; DTO in theory never resolves that requirements.

### Q. Why `PDOStatement`?

Because it is a PDO Dootong I suppose?

* I don't know what queries you would like to execute to get that data objects. So be it on your own! Will just map up into `Dootong[]` or `Dootong` instance(s).