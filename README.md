# dootong

A simple abstact dynamic pseudo-DTO ***model*** for PHP DDD.

* D-oo-TO-ng
* meaning: "headache" in Korean
* pronunciation: "do tongue"

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

### `suffer(): Headache`

Set a variety (i.e. type) of the headache.

You define your `Dootong` once and get/set data from a variety of repositories.

```php
use Yuptogun\Dootong\Varieties\MySQL;
use Yuptogun\Dootong\Varieties\Redis; // tbd

$userFromMySQL = User::suffer(new MySQL($pdo));
$userFromRedis = User::suffer(new Redis($redis));

$DBUsers = $userFromMySQL->get('SELECT * FROM users');
$RedisUsers = $userFromRedis->get('user:*');
if ($DBUsers !== $RedisUsers) {
    echo "this can and should happen";
}
```

### `get(): Headache[]`

Fetches *many* from the provided repository.

```php
/** @var \PDO $pdo */
$dto = User::suffer(new MySQL($pdo));
$users = $dto->get("SELECT * FROM users");

foreach ($users as $user) {
    if ($user->pwd) {
        throw new \Exception('this never happens: see fillable');
    }
    if (!is_int($user->id)) {
        throw new \Exception('this never happens: see castings');
    }
}
```

### `set(): int`

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
$newUserID = $dto->set($query, $inputs);
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

## Core Concepts

### Comparison to conventional ORMs

Suppose you have to deal with a dataset based on a query like the following.

```sql
SELECT
    a.a_id,
    max(a.a_name) AS a_name,
    ifnull(max(bc.bc_name), '') AS bc_name
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
WHERE a.email LIKE '%@test.com'
AND bc.bc_name LIKE '%subscription%'
GROUP BY a.a_id, bc.bc_id;
```

In Theoretical ORM usages you start by analyzing the given query and converting into a lot of entities and their relationships.

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
        $q->whereHas('webPurchase', function ($r) { /* ... */ })
        ->orWhereHas('appPurchase', function ($r) { /* ... */ });
    })->where(/*  ... */)->get(); // ERROR! still a lot to do
```

This is because that's the way the ORMs work with data. It premises that tables and columns have inherent and essential relationships. As a result, the more the way of fetching data is complicated, the more the constraints, the less you can do with them.

`Dootong` is a trade-off to that. It never assumes any relationships, indexing rules or whatsoever. It just `get()` and `set()` the data according to its variety and cause.

```php
// take this joke for example
class UserModel extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'users';
}
$userArray = UserModel::whereDoesntHave('subs')->get();

// $userArray alone works 100%, but you can still use Dootong
class User extends \Yuptogun\Dootong\Dootong {
    protected $fillable = ['name', 'age'];
}
$users = User::suffer(new StaticArray($userArray));
```

ORMs are great, but when all you want to do is entity-level jobs like validating input values, filtering soft-deleted rows and/or casting attribute types, doing it with ORMs could be an overkill.

After all, sooner or later, we all should deal with heavy queries and/or raw data anyway. It is the real problem that would cause a headache, which `Dootong` is to resolve.

```php
// as long as this MySQL query works, all you need is a MySQL Dootong ...
$query = "SELECT a.a_id ... GROUP BY a.a_id, bc.bc_id";

// ... that lets you focus on final selects
class UserPurchase extends \Yuptogun\Dootong\Dootong {
    $fillable = ['a_id', 'a_name', 'bc_name'];
    $casts = ['a_id' => 'increment'];
}
$userPurchases = UserPurchase::suffer(new MySQL($pdo))->get($query);
```

## Expectedly Asked Questions

### Q. But this is not technically a Data Transfer Object is it?

No it isn't. after all I don't buy that idea entirely.

* "A class with no methods" itself is a joke to me. Why not JSON then?
* The properties MUST have some attributes and characteristics; DTO in theory never resolves that requirements.

### Q. What about update/delete?

I have a number of unsolved problems to properly implement it.

* Transactions?
* (Can it) return updated Dootongs only or all update-requested ones?
* Isn't it too much for it? (e.g. no `Dootong` should care what tables are used)

If you have a soulution, please make a pull request and let us see.

### Q. Do you have the "variety" adapter of `[insert your favorite repository here]`?

I suppose not. If you can write one for yourself, please consider a contribution.

### Q. "`Dootong\Variety\Foo` type `Headache` caused by `$cause`"? You serious? No plan to rename the package name or namespaces?

Unless you come up with better DTO jokes.