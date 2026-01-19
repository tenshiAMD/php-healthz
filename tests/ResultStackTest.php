<?php
namespace Gentux\Healthz;

use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ResultStackTest extends \TestCase
{
    /** @var ResultStack */
    protected $stack;

    /** @var HealthResult */
    protected $checkPassed;

    /** @var HealthResult */
    protected $checkFailed;

    /** @var HealthResult */
    protected $checkWarned;

    public function setUp(): void
    {
        $this->checkPassed = Mockery::mock(HealthResult::class);
        $this->checkPassed->shouldReceive('failed')->andReturn(false);

        $this->checkFailed = Mockery::mock(HealthResult::class);
        $this->checkFailed->shouldReceive('failed')->andReturn(true);

        $this->checkWarned = Mockery::mock(HealthResult::class);
        $this->checkWarned->shouldReceive('warned')->andReturn(true);
        $this->checkWarned->shouldReceive('failed')->andReturn(false);

        $this->stack = new ResultStack();
        parent::setUp();
    }

    #[Test]
    public function push_and_check_for_failures()
    {
        $this->stack->push($this->checkPassed);
        $this->assertSame([$this->checkPassed], $this->stack->all());
        $this->assertFalse($this->stack->hasFailures());

        $this->stack->merge([$this->checkFailed]);
        $this->assertSame([$this->checkPassed, $this->checkFailed], $this->stack->all());
        $this->assertTrue($this->stack->hasFailures());
    }

    #[Test]
    public function push_and_check_for_warnings()
    {
        $this->stack->replace([$this->checkWarned]);
        $this->assertFalse($this->stack->hasFailures());
        $this->assertTrue($this->stack->hasWarnings());
    }
}
