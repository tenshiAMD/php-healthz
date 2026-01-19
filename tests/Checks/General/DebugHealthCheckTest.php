<?php
namespace Gentux\Healthz\Checks\General;

use Gentux\Healthz\HealthCheck;
use PHPUnit\Framework\Attributes\Test;

class DebugHealthCheckTest extends \TestCase
{
    protected DebugHealthCheck $debug;

    public function setUp(): void
    {
        parent::setUp();
        $this->debug = new DebugHealthCheck();
    }

    #[Test]
    public function instance_of_health_check()
    {
        $this->assertInstanceOf(HealthCheck::class, $this->debug);
    }

    #[Test]
    public function run_sets_the_description_to_off()
    {
        putenv('APP_DEBUG=false');

        $this->debug->run();
        $this->assertSame('off', $this->debug->status());
    }

    #[Test]
    public function run_throws_warning_exception_if_debug_is_on()
    {
        $this->expectException(\Gentux\Healthz\Exceptions\HealthWarningException::class);
        $this->debug = new DebugHealthCheck('DEBUG_CUSTOM');
        putenv('DEBUG_CUSTOM=true');

        $this->debug->run();
        $this->assertSame('on', $this->debug->status());
    }
}
