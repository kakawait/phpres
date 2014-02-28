<?php

use \Mockery as m;

class ExecutorTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Mockery\MockInterface
     */
    private $communicator;

    /**
     * @var \Mockery\MockInterface
     */
    private $stringifier;

    /**
     * @var \Jumper\Executor
     */
    private $executor;

    public function setUp()
    {
        $this->communicator = m::mock('\Jumper\Communicator');
        $this->stringifier = m::mock('\Jumper\Stringifier');
        $this->executor = new \Jumper\Executor($this->communicator, $this->stringifier);
    }

    /**
     * @test
     */
    public function connectionShouldBeExpectedIfConnectionIsInactive()
    {
        $this->communicator->shouldReceive('isConnected')->withNoArgs()->andReturn(false)->once()->ordered();
        $this->communicator->shouldReceive('connect')->withNoArgs()->once()->ordered();
        $this->communicator->shouldReceive('run')->with(m::type('string'))->once()->ordered();

        $this->stringifier->shouldReceive('getSerializeFunctionName')->withNoArgs()->once()->ordered();
        $this->stringifier->shouldReceive('toObject')->with(m::any())->once()->ordered();

        $this->executor->run(function() {});
    }

    /**
     * @test
     */
    public function connectionShouldBeNotExpectedIfConnectionIsActive()
    {
        $this->communicator->shouldReceive('isConnected')->withNoArgs()->andReturn(true)->once()->ordered();
        $this->communicator->shouldReceive('run')->with(m::type('string'))->once()->ordered();

        $this->stringifier->shouldReceive('getSerializeFunctionName')->withNoArgs()->once()->ordered();
        $this->stringifier->shouldReceive('toObject')->with(m::any())->once()->ordered();

        $this->executor->run(function() {});
    }

    /**
     * @test
     * @expectedException \Jumper\Exception\ExecutorException
     */
    public function remotePhpErrorShouldThrowExecutorException()
    {
        $this->communicator->shouldReceive('isConnected')->withNoArgs()->andReturn(true)->once()->ordered();
        $this->communicator->shouldReceive('run')->with(m::type('string'))->andThrow('\RuntimeException')->once()
                           ->ordered();

        $this->stringifier->shouldReceive('getSerializeFunctionName')->withNoArgs()->once()->ordered();

        $this->executor->run(function() {});
    }

    public function tearDown()
    {
        m::close();
    }
} 