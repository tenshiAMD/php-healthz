<?php
namespace Gentux\Healthz\Checks\General;

use Gentux\Healthz\HealthCheck;
use PHPUnit\Framework\Attributes\Test;

class EnvHealthCheckTest extends \TestCase
{
    protected EnvHealthCheck $env;

    public function setUp(): void
    {
        parent::setUp();
        $this->env = new EnvHealthCheck('CUSTOM_ENV');
    }

    #[Test]
    public function instance_of_health_check()
    {
        $this->assertInstanceOf(HealthCheck::class, $this->env);
    }

    #[Test]
    public function sets_the_status_to_the_current_environment()
    {
        putenv('CUSTOM_ENV=staging');
        $this->env->run();
        $this->assertSame('staging', $this->env->status());
    }

    #[Test]
    public function unknown_environment_emits_a_warning()
    {
        $this->expectException(\Gentux\Healthz\Exceptions\HealthWarningException::class);
        putenv('CUSTOM_ENV=');
        $this->env->run();
    }
}
