<?php
namespace Gentux\Healthz\Checks\Laravel;

use Gentux\Healthz\Checks\Laravel\DatabaseHealthCheck;
use Mockery;
use Gentux\Healthz\HealthCheck;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use PDO;
use PHPUnit\Framework\Attributes\Test;

class DatabaseHealthCheckTest extends \TestCase
{
    /** @var Mockery\Mock | DatabaseManager */
    protected $manager;

    /** @var DatabaseHealthCheck */
    protected $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = Mockery::mock(DatabaseManager::class);
        $this->db = new DatabaseHealthCheck($this->manager);
    }

    #[Test]
    public function instance_of_health_check()
    {
        $this->assertInstanceOf(HealthCheck::class, $this->db);
    }

    #[Test]
    public function sets_connection_name()
    {
        $this->assertNull($this->db->connection());

        $this->db->setConnection('custom');
        $this->assertSame('custom', $this->db->connection());
    }

    #[Test]
    public function if_no_connection_is_set_use_the_description()
    {
        $description = $this->db->description();
        $this->assertSame('Check the database connection.', $description); # if connection is also null

        $this->db->setConnection('mysql');
        $description = $this->db->description();
        $this->assertSame('mysql', $description);
    }

    #[Test]
    public function uses_the_connection_name_set_to_resolve_a_laravel_db_connection()
    {
        $this->db->setConnection('custom');

        $conn = Mockery::mock(Connection::class)->makePartial();
        $this->manager->shouldReceive('connection')->with('custom')->once()
            ->andReturn($conn);

        $pdo = Mockery::mock(PDO::class);
        $conn->shouldReceive('getPdo')->once()
            ->andReturn($pdo);

        $this->db->run();
        $status = $this->db->status();
        $this->assertSame('connected', $status);
    }

    #[Test]
    public function throws_health_failure_when_laravel_runs_into_trouble()
    {
        $this->expectException(\Gentux\Healthz\Exceptions\HealthFailureException::class);
        $this->manager->shouldReceive('connection')->andThrow(new \Exception());
        $this->db->run();
    }
}
