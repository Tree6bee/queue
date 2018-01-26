<?php

namespace Tests\Tree6bee\Queue\Jobs;

use Tree6bee\Queue\Job;

class ExampleJob extends Job
{
    /**
     * @var string job queue name (beanstalkd tube)
     */
    public $queue = 'default';

    /**
     * The "time to run" for all pushed jobs. (beanstalkd ttr, timeout)
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

    /**
     * @var array
     */
    public $idArr;

    public function __construct(array $idArr)
    {
        $this->idArr = $idArr;
    }

    public function handle()
    {
        var_export($this->idArr);

        var_dump($this->retry_after, $this->tries);

        // throw new \Exception('handle job...lol ^_^');
    }
}
