<?php

namespace Tree6bee\Queue\Drivers\Jobs;

use Pheanstalk\PheanstalkInterface;

interface Job
{
    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody();

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = PheanstalkInterface::DEFAULT_DELAY);

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete();

    /**
     * Bury the job in the queue.
     *
     * @return void
     */
    public function bury();

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts();

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId();
}
