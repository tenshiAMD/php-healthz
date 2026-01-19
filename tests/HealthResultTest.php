<?php
namespace Gentux\Healthz;

use Mockery;
use PHPUnit\Framework\Attributes\Test;

class HealthResultTest extends \TestCase
{
    /** @var HealthResult*/
    protected $resultSuccess;

    /** @var HealthResult*/
    protected $resultWarning;

    /** @var HealthResult*/
    protected $resultFailure;

    public function setUp(): void
    {
        parent::setUp();
        $check = Mockery::mock(HealthCheck::class);
        $check->shouldReceive('title')->andReturn('Title');
        $check->shouldReceive('description')->andReturn('Description');
        $check->shouldReceive('status')->andReturn('Status');

        $this->resultSuccess = new HealthResult(HealthResult::RESULT_SUCCESS, $check);
        $this->resultWarning = new HealthResult(HealthResult::RESULT_WARNING, $check);
        $this->resultFailure = new HealthResult(HealthResult::RESULT_FAILURE, $check);
    }

    #[Test]
    public function result_helpers()
    {
        $this->assertTrue($this->resultSuccess->passed());
        $this->assertFalse($this->resultSuccess->warned());
        $this->assertFalse($this->resultSuccess->failed());

        $this->assertTrue($this->resultWarning->warned());
        $this->assertFalse($this->resultWarning->passed());
        $this->assertFalse($this->resultWarning->failed());

        $this->assertTrue($this->resultFailure->failed());
        $this->assertFalse($this->resultFailure->passed());
        $this->assertFalse($this->resultFailure->warned());
    }

    #[Test]
    public function information_about_health_check()
    {
        $this->assertSame('Title', $this->resultSuccess->title());
        $this->assertSame('Description', $this->resultSuccess->description());
        $this->assertSame('Status', $this->resultSuccess->status());
        $this->assertSame(HealthResult::RESULT_SUCCESS, $this->resultSuccess->result());
    }
}
