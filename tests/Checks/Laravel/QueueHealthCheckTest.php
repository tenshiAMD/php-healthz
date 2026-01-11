<?php
namespace Gentux\Healthz\Checks\Laravel;

use Gentux\Healthz\Checks\Laravel\QueueHealthCheck;
use Mockery;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\SqsQueue;
use Illuminate\Queue\SyncQueue;
use Illuminate\Queue\RedisQueue;
use Gentux\Healthz\HealthCheck;
use Illuminate\Queue\QueueManager;
use PHPUnit\Framework\Attributes\Test;

class QueueHealthCheckTest extends \TestCase
{
    /** @var Mockery\Mock | QueueManager */
    protected $manager;

    /** @var QueueHealthCheck */
    protected $queue;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = Mockery::mock(QueueManager::class);
        $this->queue = new QueueHealthCheck($this->manager);
    }

    #[Test]
    public function instance_of_health_check()
    {
        $this->assertInstanceOf(HealthCheck::class, $this->queue);
    }

    #[Test]
    public function sets_queue_name()
    {
        $this->assertNull($this->queue->name());

        $this->queue->setName('custom');
        $this->assertSame('custom', $this->queue->name());
    }

    #[Test]
    public function if_no_connection_is_set_use_the_description()
    {
        $description = $this->queue->description();
        $this->assertSame('Check the queue connection.', $description);

        $this->queue->setName('sqs');
        $description = $this->queue->description();
        $this->assertSame('sqs', $description);
    }

    #[Test]
    public function checks_connection_status_of_sqs_queue()
    {
        $this->queue->setName('custom');

        # laravel sqs queue service
        $sqsQueue = Mockery::mock(SqsQueue::class);
        $this->manager->shouldReceive('connection')->with('custom')->once()->andReturn($sqsQueue);

        # need url of queue to check attributes on SQS
        $sqsQueue->shouldReceive('getQueue')->andReturn('some-queue-url.com');;

        # sqs service to check connection
        $sqs = Mockery::mock(SqsClient::class);
        $sqsQueue->shouldReceive('getSqs')->andReturn($sqs);

        # make sure a call is made using sqs client to get queue attributes
        $sqs->shouldReceive('getQueueAttributes')->with(['QueueUrl' => 'some-queue-url.com'])->once();

        $this->queue->run();
        $status = $this->queue->status();
        $this->assertSame('connected to SQS', $status);
    }

    #[Test]
    public function checks_status_of_sync_queue()
    {
        $sync = Mockery::mock(SyncQueue::class);
        $this->manager->shouldReceive('connection')->andReturn($sync);

        $this->queue->run();

        $status = $this->queue->status();
        $this->assertSame('connected to Sync queue', $status);
    }

    #[Test]
    public function throws_warning_if_queue_driver_is_not_supported()
    {
        $this->expectException(\Gentux\Healthz\Exceptions\HealthWarningException::class);
        $redis = Mockery::mock(RedisQueue::class);
        $this->manager->shouldReceive('connection')->andReturn($redis);

        $this->queue->run();
    }
}
