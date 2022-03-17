# dootong

A simple abstact dynamic pseudo-DTO ***model*** for PHP DDD.

> Dootong is romanization of 두통, a Korean word meaning "headache".

## Quick Start

Every `Yuptogun\Dootong\Interfaces\Headache` implementation looks like the following.

```php
class User extends Yuptogun\Dootong\Dootong
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
        'pwd' => 'password', // "password" type fillable hidden by default
        'id' => 'increment', // "increment" type is automatically integer
    ];

    /**
     * set the timestamp column name if you want to enable "soft delete"
     * @required false
     */
    protected $deletedAt = 'deleted_at';
}
```

And it can do the following:

### `suffer(Variety $variety): Headache`

Set a variety (i.e. type) of the headache.

You define your `Dootong` once and get/set data from a variety of repositories.

```php
use Yuptogun\Dootong\Varieties\MySQL;
use Yuptogun\Dootong\Varieties\Redis; // tbd

$userFromMySQL = User::suffer(new MySQL($pdo));
$userFromRedis = User::suffer(new Redis($redis));

$DBUsers = $userFromMySQL->setHeadacheGettingCause('SELECT * FROM users')->get();
$RedisUsers = $userFromRedis->setHeadacheGettingCause('user:*')->get();
$RedisUserIDs = array_column($RedisUsers, 'id');
foreach ($DBUsers as $DBUser) {
    if (!in_array($DBUsers->id, $RedisUserIDs)) {
        $userFromRedis->set($DBUser, 'user');
    }
}
```

### `get(?array $attrs = null, $cause = null): Headache[]`

Fetches *many* entities from the given repository and query/command (i.e. "cause" of Headache)

```php
/** @var \PDO $pdo */
$dto = User::suffer(new MySQL($pdo));
$users = $dto->get(
    ['email_domain' => 'yahoo.com'],
    "SELECT * FROM users WHERE email LIKE concat('%@', :email_domain)"
);

foreach ($users as $user) {
    if ($user->pwd) {
        throw new \Exception('this never happens: see fillable');
    }
    if (!is_int($user->id)) {
        throw new \Exception('this never happens: see castings');
    }
    if (stripos($user->email, 'yahoo.com') === FALSE) {
        throw new \Exception('this never happens: see attrs and cause');
    }
}
```

### `setHeadacheGettingCause($cause): Headache`

Defines how the entities should be fetched.

### `set(array $attrs, $cause = null): int`

Registers *one* entity into the provided repository.

```php
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
$dto = User::suffer(new MySQL($pdo));
$newUserID = $dto->set($inputs, $query);
$newUser   = $dto->get("SELECT * FROM users WHERE id = :newUserID", compact('newUserID'))[0];
if ($newUser->pwd) {
    throw new \Exception('this never happens');
}
if (!$newUser->isPassword('12345678')) {
    throw new \Exception('this also never happens');
}

unset($inputs['password'], $inputs['bio']);
$inputs['email'] = 'bar.com';

try {
    $newUserID = $dto->set($query, $inputs);
    echo "this never prints";
} catch (\Exception $e) {
    echo "this always prints: see required";
    echo $e->getMessage(); // {"email":["invalid"],"password":["required"]}
}
```

### `setHeadacheSettingCause($cause): Headache`

Defines how the entities should be registered.

## Core Concepts

### Nothing Else Than Getter and Setter

Every `Dootong` is only a holder of attribues with extra methods to set/get them.

Its children or dependents will do the funs with the attributes; `Dootong` itself shall not.

Any ideas/commits/issues against this fundamental will be rejected.

### Real World Problems First

The primary purpose of this library is to solve the real world problems with the "messy" data storages and their structures, which are probably your daily headache.

```sql
SELECT
    a.a_id AS `user_id`,
    max(a.a_name) AS `user_name`,
    ifnull(max(bc.bc_name), '') AS `purchase_name`
FROM a
LEFT JOIN (
    SELECT b.a_id, b.b_name AS bc_name, concat(b.b_name, b.b_id) AS bc_id
    FROM b WHERE b.a_id = a.a_id
    AND b.created_at >= '2021-01-01 00:00:00' AND b.cancelled_at IS NULL
    UNION ALL
    SELECT c.a_id, c.c_name AS bc_name, concat(c.c_name, c.c_id) AS bc_id
    FROM c WHERE c.a_id = a.a_id
    AND c.created_at >= '2021-01-01 00:00:00' AND c.cancelled_at IS NULL
) bc ON bc.a_id = a.a_id
WHERE a.email LIKE concat('%@', :email_domain)
AND bc.bc_name LIKE concat('%', :product_name)
GROUP BY a.a_id, bc.bc_id;
```

To resolve this kind of problems, the attempts with ORMs can be easily defeated. Look at the query above for example; can you define all relationships so the Object-*Relationship*-Models would happily do the rest?

```php
class User extends Model {
    protected $table = 'a';
    public function webPurchase() {
        return $this->hasMany(WebPurchase::class, 'a_id', 'a_id');
    }
    public function appPurchase() {
        return $this->hasMany(AppPurchase::class, 'a_id', 'a_id');
    }
    // bla bla
}
class WebPurchase extends Model {
    protected $table = 'b';
    public function user() {
        return $this->belongsTo(User::class, 'a_id', 'a_id');
    }
    // yaddy yadda
}
$userPurchases = User::where(function ($q) {
        $q->whereHas('webPurchase', function ($r) { /* waka waka */ })
        ->orWhereHas('appPurchase', function ($r) { /* ahda kohda */ });
    })->where(/*  ... */)->get(); // ERROR! still a lot to do
```

Instead, try replacing your *headache* with *`Dootong`*. (Get it?) Just bring your queries/relationships/constraints that are working for whatever reason, give that "cause" to your `Dootong` and BOOM! Jobs done.

```php
class PayingUserSince2021 extends \Yuptogun\Dootong\Dootong {
    protected $fillable = ['user_id', 'user_name', 'purchase_name'];
}
$subscribers = PayingUserSince2021::suffer(new MySQL($pdo))
    ->setHeadacheGettingCause($theQuery)
    ->get([
        'email_domain' => 'test.com',
        'product_name' => 'subscription'
    ]);
```

## Expectedly Asked Questions

### Q. But this is not technically a Data Transfer Object is it?

No it isn't. after all I don't buy that idea entirely.

* "A class with no methods" itself is a joke to me. Why not JSON then?
* The properties MUST have some attributes and characteristics; DTO in theory never resolves that requirements.

### Q. Do you have the "variety" adapter of `[insert your favorite repository here]`?

I suppose not. If you can write one for yourself, please consider a contribution.

### Q. "`Dootong\Variety\Foo` type `Headache` caused by `$cause`"? You serious? No plan to rename the package name or namespaces?

Unless you come up with better DTO jokes.