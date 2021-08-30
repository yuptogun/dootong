<?php
namespace Tests\MySQL;

use PDO;
use PHPUnit\Framework\TestCase;
use Yuptogun\Dootong\Varieties\MySQL as Dootong;

class Bizunit extends Dootong
{
    protected $fillable = [
        'bizunit_sno', 'bizunit_id', 'bizunit_name',
    ];
    protected $casts = [
        'bizunit_sno' => 'increment',
    ];
}

final class BasicTest extends TestCase
{
    public function testGet()
    {
        $pdo = new PDO('mysql:host=3.34.53.113;port=33307;charset=utf8', 'admin', 'Tldzmgkgk12!');
        $source = $pdo->query(
            "SELECT *
            FROM synctree_studio.bizunit b
            ORDER BY b.bizunit_sno DESC
            LIMIT 1"
        );

        $model = new Bizunit;
        $bizunit = $model->get($source);
        foreach ($bizunit as $b) {
            $this->assertIsInt($b->bizunit_sno);
        }
    }
}
