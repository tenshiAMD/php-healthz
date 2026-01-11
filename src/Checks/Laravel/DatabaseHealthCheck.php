<?php
namespace Gentux\Healthz\Checks\Laravel;

use Exception;
use Gentux\Healthz\HealthCheck;
use Illuminate\Database\DatabaseManager;
use Gentux\Healthz\Exceptions\HealthFailureException;

/**
 * This will check the connection to a database using Laravel db services.
 *
 * @package Gentux\Healthz
 */
class DatabaseHealthCheck extends HealthCheck
{

    /** @var string */
    protected $title = 'Database';

    /** @var string|null Laravel database connection name to use */
    protected $connection;

    /** @var DatabaseManager */
    protected $db;

    /** @var string */
    protected $description = 'Check the database connection.';

    /**
     * @param DatabaseManager|null $db Will use Laravel's container to make an instance if null
     *
     * @throws HealthFailureException if unable to resolve database manager
     */
    public function __construct(?DatabaseManager $db = null)
    {
        $this->db = $db;

        if (!$this->db) {
            try { $this->db = app('db'); } catch (Exception $e) {
                throw new HealthFailureException('Cannot create instance of Laravel database manager.');
            }
        }
    }

    /**
     * Check database connection
     *
     * @return void
     *
     * @throws HealthFailureException
     */
    public function run(): void
    {
        try {
            $name = $this->connection();
            $this->db->connection($name)->getPdo();
        } catch (Exception $e) {
            throw new HealthFailureException($e->getMessage());
        }

        $this->setStatus('connected');
    }

    /**
     * Get the connection name
     *
     * @return null|string
     */
    public function connection(): ?string
    {
        return $this->connection;
    }

    /**
     * Set the connection name
     *
     * @param string $connection
     *
     * @return void
     */
    public function setConnection($connection): void
    {
        $this->connection = $connection;
    }

    /**
     * If no description property is defined, use the connection
     * name instead ('default' if connection is also null).
     *
     * @return string
     */
    public function description(): string
    {
        return $this->connection() ?: $this->description;;
    }
}
