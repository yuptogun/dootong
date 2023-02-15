# dootong

Your painkiller for writing DTOs in PHP.

> "headache" is "두통" in Korean, which is Romanized into DooTOng.

## Quick Start

```php
class User extends Yuptogun\Dootong\Dootong
{
    protected $attributes = [
        'email', 'username', 'id', 'bio', 'timezone',
    ];
    protected $required = [
        'email', 'username', 'pwd',
    ];
    protected $hidden = [
        'email', 'timezone',
    ];
    protected $casts = [
        'id' => 'increment',
        'email' => 'email',
        'pwd' => 'password',
    ];
}

$user = User::sufferFrom(
    (new MySQL(new PDO('mysql:host=localhost;dbname=test', 'test', 'test')))
        ->diagnose("SELECT * FROM users WHERE email LIKE ?")
        ->prescribe("INSERT INTO users (email, username, pwd) VALUES (?, ?, ?)")
);

/** @var User[] $yahooUsers */
$yahooUsers = $user->get('@yahoo.com');
foreach ($yahooUsers as $u) {

    /** @var int $id type casted */
    $id = $u->id;

    /** @var null $timezone "hidden" */
    $timezone = $u->timezone;
}

/** @var User $newUser */
$newUser = $user->set('foo@bar.com', 'foo', 'bar');

/** @var null $newUserPassword "password" type basically hidden */
$newUserPassword = $newUser->pwd;

/**
 * "password" type works with comparison method.
 * if you have multiple password type attributes, specify it in the second argument.
 *
 * @var true $newUserPasswordCheck
 */
$newUserPasswordCheck = $newUser->isPassword('bar');
```

## Core Concepts

### One DTO, Any Repositories

A `Dootong` is an entity represented as a set of attributes and their handler methods.  
A `Headache` is a repository that can give/save entities.  
Any `Dootong` can "suffer from" any types of `Headache`, as long as they get along with each other.

```php
use MyApp\DTO\Order;

// note that we use same Order DTO with multiple types of Headache
$redis = new Yuptogun\Dootong\Types\Redis($config);
$mysql = new Yuptogun\Dootong\Types\MySQL($pdo);
$ordersQueued = Order::setRepository($redis)
    ->diagnose('LRANGE orders 0 10')
    ->get();
foreach ($ordersQueued as $order) {

    // each $order from Redis is compatible with one from DB, so it just works
    Order::sufferFrom($mysql)
        ->prescribe("INSERT INTO orders (user_id, product_id) VALUES (?, ?)")
        ->set($order->user_id, $order->product_id);
}
```

### For the real world problems

The real world queries are inevitably messy.  
This is the cause of your daily headache when you have to model them into ORMs.

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

Have `Dootong` instead. Define the cause of your headache once, get diagnosed and prescribed.  
And then everything starts working.

```php
class PaidUsersSince2021 extends MySQL
{
    protected $getter = THE_QUERY_ABOVE;
}

$paidUsersSince2021 = PaidUser::sufferFrom(new PaidUsersSince2021($pdo))->get([
    'email_domain' => 'google.com',
    'product_name' => 'painkiller 3000',
]);
```

## How to contribute

Everything can be improved, including this README.

### Unit test

```sh
docker-compose up -d --build
docker run --rm -it -v "$(pwd):/app" -w /app composer install --ignore-platform-reqs
docker run --rm -it -v "$(pwd):/app" -w /app yuptogun/dootong-test-php php ./vendor/bin/phpunit tests
```