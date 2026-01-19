<?php
namespace Gentux\Healthz\Checks\Laravel;

use Exception;
use Illuminate\Queue\SqsQueue;
use Gentux\Healthz\HealthCheck;
use Illuminate\Queue\SyncQueue;
use Illuminate\Queue\QueueManager;
use Gentux\Healthz\Exceptions\HealthWarningException;
use Gentux\Healthz\Exceptions\HealthFailureException;

/**
 * This will check the connection to a queue provider.
 *
 * @package Gentux\Healthz
 */
class QueueHealthCheck extends HealthCheck
{

    /** @var string */
    protected $title = 'Queue Service';

    /** @var string|null Laravel queue name to use */
    protected $name;

    /** @var QueueManager */
    protected $queue;

    /** @var string */
    protected $description = 'Check the queue connection.';

    /**
     * @param QueueManager|null $queue Will use Laravel's container to make an instance if null
     *
     * @throws HealthFailureException if unable to resolve queue manager
     */
    public function __construct(?QueueManager $queue = null)
    {
        $this->queue = $queue;

        if (!$this->queue) {
            try { $this->queue = app('queue'); } catch (Exception $e) {
                throw new HealthFailureException('Cannot create instance of Laravels queue manager.');
            }
        }
    }

    /**
     * Check database connection
     *
     * @return void
     *
     * @throws HealthFailureException
     * @throws HealthWarningException
     */
    public function run(): void
    {
        $name = $this->name();
        $queue = $this->queue->connection($name);

        if ($queue instanceof SqsQueue) {
            $this->runSqsCheck($queue);
        } elseif ($queue instanceof SyncQueue) {
            $this->runSyncCheck($queue);
        } else {
            throw new HealthWarningException('Only SQS and Sync queue drivers supported at this time.');
        }
    }

    /**
     * Run the health check against an sqs queue
     *
     * @param SqsQueue $queue
     *
     * @return void
     */
    protected function runSqsCheck(SqsQueue $queue): void
    {
        $url = $queue->getQueue(null);
        $queue->getSqs()->getQueueAttributes(['QueueUrl' => $url]);

        $this->setStatus('connected to SQS');
    }

    /**
     * Nothing to really check with a sync queue, will
     * just set the status.
     *
     * @param SyncQueue $queue
     *
     * @return void
     */
    protected function runSyncCheck(SyncQueue $queue): void
    {
        $this->setStatus('connected to Sync queue');
    }

    /**
     * Get the queue name
     *
     * @return null|string
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Set the queue name
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * If no description property is defined, use the queue name instead.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->name() ?: $this->description;
    }
}
