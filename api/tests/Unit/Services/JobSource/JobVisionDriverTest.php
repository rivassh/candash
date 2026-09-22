<?php

namespace Tests\Unit\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\Services\JobSource\JobSourceDriverFactory;
use App\Services\JobSource\JobVisionDriver;
use Tests\TestCase;

class JobVisionDriverTest extends TestCase
{
    public function testDriverImplementsJobSourceInterface(): void
    {
        $driver = new JobVisionDriver();
        $this->assertInstanceOf(JobSourceInterface::class, $driver);
    }

    public function testDriverReturnsCorrectName(): void
    {
        $driver = new JobVisionDriver();
        $this->assertEquals('jobvision', $driver->driverName());
    }

    public function testFactoryCreatesJobVisionDriver(): void
    {
        $driver = JobSourceDriverFactory::make('jobvision');
        $this->assertInstanceOf(JobVisionDriver::class, $driver);
        $this->assertEquals('jobvision', $driver->driverName());
    }

    public function testFactoryHandlesUnknownProvider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("not defined in config/job_sources.php");
        
        JobSourceDriverFactory::make('invalid_provider');
    }

    public function testFactoryCreatesMockDriver(): void
    {
        $driver = JobSourceDriverFactory::make('mock');
        $this->assertEquals('mock', $driver->driverName());
    }

    public function testFactoryCreatesExternalDriver(): void
    {
        $driver = JobSourceDriverFactory::make('external');
        $this->assertEquals('external', $driver->driverName());
    }
}