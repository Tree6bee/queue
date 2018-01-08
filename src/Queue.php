<?php

namespace Tree6bee\Queue;

use Tree6bee\Queue\Drivers\QueueInterface;

class Queue
{
    const TYPE = 'tree6bee';

    /**
     * @var QueueInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $defaultQueue = 'default';

    public function __construct(QueueInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  Job  $job
     * @param  string  $queue
     *
     * @return mixed
     */
    public function push(Job $job, $queue = '')
    {
        return $this->driver->push(
            $this->getQueue($queue),
            $this->createPayload($job),
            $job->retry_after
        );
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int  $delay
     * @param  Job  $job
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, Job $job, $queue = '')
    {
        return $this->driver->later(
            $delay,
            $this->getQueue($queue),
            $this->createPayload($job),
            $job->retry_after
        );
    }

    /**
     * @param string $queue
     *
     * @return null|\Tree6bee\Queue\Drivers\Jobs\Job
     */
    public function pop($queue = '')
    {
        return $this->driver->pop($this->getQueue($queue));
    }

    /**
     * release job
     *
     * @param $job
     */
    public function release($job)
    {
        return $this->driver->release($job);
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return $queue ? $queue : $this->defaultQueue;
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  Job $job
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function createPayload(Job $job)
    {
        $payload = json_encode([
            'type'  => self::TYPE, //只有 type 为 tree6bee 才会自动处理，其他的不处理
            'job'   => serialize(clone $job),
        ]);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }

        return $payload;
    }
}
