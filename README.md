# dootong

A simple abstact dynamic pseudo-DTO ***model*** for PHP DDD.

* D-oo-TO-ng
* meaning: "headache" in Korean
* pronunciation: "do tongue"

## Quick Start

Every `Yuptogun\Dootong\Interfaces\Headache` implementation looks like the following.

```php
use Yuptogun\Dootong\Varieties\MySQL as Dootong;

class UserDTO extends Dootong
{
    /**
     * attributes to get
     * @required true
     */
    protected $fillable = [
        'email', 'username', 'id', 'bio', 'timezone',
    ];

    /**
     * required attributes to set
     * @required false
     */
    protected $required = [
        'email', 'username', 'pwd',
    ];

    /**
     * attributes to hide when getting them
     * @required false
     */
    protected $hidden = [
        'email', 'timezone',
    ];

    /**
     * attribute type casting definitions
     * @required false
     */
    protected $casts = [
        'username' => 'string',
        'email' => 'email',
        'pwd' => 'password', // password type fillable hidden by default
        'id' => 'increment',
    ];

    /**
     * set the timestamp column name if you want to enable "soft delete"
     * @required false
     * @default 'deleted_at'
     */
    protected $deletedAt = 'deleted_at';
}
```

### `get(): Headache[]`

Fetches *many* from the provided repository.

```php
$dto = new UserDTO;
$query = "SELECT * FROM users";

/** @var \PDO $pdo */
$statement = $pdo->query($query);
$users     = $dto->get($statement);

foreach ($users as $user) {
    if ($user->pwd) {
        throw new \Exception('this never happens: see fillable');
    }
    if (!is_int($user->id)) {
        throw new \Exception('this never happens: see castings');
    }
}
```

### `set(): Headache`

Registers *one* entity into the provided repository.

```php
$dto = new UserDTO;
$query =
"INSERT INTO users (username, email, pwd, bio)
VALUES (:username, :email, :pwd, :bio)";
$inputs = [
    'username' => 'Foo B.',
    'email'    => 'foo@bar.com',
    'pwd'      => '12345678',
    'bio'      => '',
];

/** @var \PDO $pdo */
$statement = $pdo->query($query);
$newUser   = $dto->set($statement, $inputs);

if ($newUser->pwd) {
    throw new \Exception('this never happens');
}
if (!$newUser->isPassword('12345678')) {
    throw new \Exception('this also never happens');
}

unset($inputs['password'], $inputs['bio']);
$inputs['email'] = 'bar.com';

try {
    $newUser = $dto->set($statement, $inputs);
    echo "this never prints";
} catch (\Exception $e) {
    echo "this always prints: see required";
    echo $e->getMessage(); // {"email":["invalid"],"password":["required"]}
}
```

## Expectedly Asked Questions

### Q. Why not just pure DTO with other ORMs or anything?

I don't buy the idea entirely.

* "A class with no methods" itself is a joke to me. Why not JSON then?
* The properties MUST have some attributes and characteristics; DTO in theory never resolves that requirements.