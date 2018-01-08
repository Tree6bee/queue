<?php

namespace Tree6bee\Queue\Drivers\Jobs;

use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Job as PheanstalkJob;

class BeanstalkdJob implements Job
{
    /**
     * @var PheanstalkInterface
     */
    protected $pheanstalk;

    /**
     * @var PheanstalkJob
     */
    protected $job;

    /**
     * @var string
     */
    protected $queue;

    public function __construct(PheanstalkInterface $pheanstalk, PheanstalkJob $job, $queue)
    {
        $this->pheanstalk = $pheanstalk;
        $this->job = $job;
        $this->queue = $queue;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->getData();
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->pheanstalk->delete($this->job);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = PheanstalkInterface::DEFAULT_DELAY)
    {
        $this->pheanstalk->release($this->job, PheanstalkInterface::DEFAULT_PRIORITY, $delay);
    }

    /**
     * Bury the job in the queue.
     *
     * @return void
     */
    public function bury()
    {
        $this->pheanstalk->bury($this->job);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        $stats = $this->pheanstalk->statsJob($this->job);

        return (int) $stats->reserves;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->getId();
    }

    /**
     * Get the underlying Pheanstalk instance.
     *
     * @return PheanstalkInterface
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * Get the underlying Pheanstalk job.
     *
     * @return \Pheanstalk\Job
     */
    public function getPheanstalkJob()
    {
        return $this->job;
    }
}
