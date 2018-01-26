<?php

namespace Tree6bee\Queue;

use Tree6bee\Queue\Drivers\Jobs\JobInterface;

abstract class Job
{
    /**
     * @var string job queue name
     */
    public $queue = 'default';

    /**
     * The "time to run" for all pushed jobs.
     *
     * @var int 允许 worker 执行的最大秒数,超时 job 将会被 release 到 ready 状态.
     */
    public $retry_after = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int 最大尝试次数
     */
    public $tries = 1;

    abstract public function handle();



    /** 以下为 队列 job 的操作方法 */

    /**
     * The underlying queue job instance.
     *
     * @var JobInterface
     */
    protected $job;

    /**
     * Set the base queue job instance.
     *
     * @param  JobInterface $job
     * @return $this
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->job->attempts();
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->job->delete();
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->job->release($delay);
    }

    /**
     * Bury the job in the queue.
     */
    public function bury()
    {
        $this->job->bury();
    }
}
