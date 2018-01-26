<?php

namespace Tree6bee\Queue;

use Tree6bee\Queue\Drivers\Jobs\JobInterface;

class Worker
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var string 需要处理的 queue
     */
    protected $queueTube;

    /**
     * Create a new queue worker.
     *
     * @param Queue $queue
     * @param string $queueTube
     */
    public function __construct(Queue $queue, $queueTube = '')
    {
        $this->queue = $queue;
        $this->queueTube = $queueTube;
    }

    /**
     * Listen to the given queue in a loop.
     *
     * @param int $sleep 没有新的有效任务产生时的休眠时间 (单位: 秒)
     * @param int $memoryLimit worker 内存限制 (单位: mb)
     */
    public function daemon($sleep = 60, $memoryLimit = 128)
    {
        $memoryLimit = $memoryLimit * 1024 * 1024;
        $startTime = time();
        while (true) {
            $ret = $this->runNextJob();

            if (! $ret) { //没有获取到job
                sleep($sleep);
            }

            ($this->memoryExceeded($memoryLimit) || $this->queueShouldRestart($startTime)) &&
                $this->stop();
        }
    }

    public function runNextJob()
    {
        $job = $this->queue->pop($this->queueTube);

        if ($job) {
            $payload = json_decode($job->getRawBody(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \InvalidArgumentException('Unable to create payload: '.json_last_error_msg());
            }

            if (isset($payload['type']) && $payload['type'] === Queue::TYPE) {
                $obj = unserialize($payload['job']);

                if ($obj instanceof Job) {
                    $this->process($job, $obj);

                    return true;
                }
            }

            throw new \Exception('不能识别的 job 类型, jobId: ' . $job->getJobId());
        } else {
            return false;
        }
    }

    /**
     * !!! 对 队列的 bury 增加监控，防止失败未处理的 job 过多
     * @param JobInterface $job 队列里的每个 job 对象
     * @param Job $obj job 中反序列化后具体的实例对象 应用开发者定义的 job
     */
    protected function process(JobInterface $job, Job $obj)
    {
        try {
            $this->handleWithObj($job, $obj->setJob($job));

            $job->delete();
        } catch (\Exception $e) {
            if ($job->attempts() < $obj->tries) { //小于最大尝试次数 release
                $job->release();
            } else { //超过了放到失败中
                $job->bury();

                $this->logProcessError($e);
            }
        }
    }

    /**
     * 记录 job 执行失败的日志
     * !!! 此处根据具体的框架应用进行重载来记录符合业务的日志格式
     * !!! you can override this method to log the job processing error, etc...
     * @param \Exception $e
     *
     * @return void
     */
    protected function logProcessError(\Exception $e)
    {
        echo (string) $e;

        return ;
    }

    /**
     * !!! 此处根据具体的框架应用进行重载，方便注入框架的服务对象等
     * !!! you can override this method, so that the application job can init with more obj, etc...
     *
     * @param JobInterface $job 队列里的每个 job 对象 the base queue job instance.
     * @param Job $obj job 中反序列化后具体的实例对象 应用开发者定义的 application job
     */
    protected function handleWithObj(JobInterface $job, Job $obj)
    {
        //override like: $obj->setApp($app);
        $obj->handle();
    }

    /**
     * Determine if the memory limit has been exceeded.
     * @param  int   $memoryLimit
     *
     * @return bool
     */
    protected function memoryExceeded($memoryLimit)
    {
        return memory_get_usage() >= $memoryLimit;
    }

    /**
     * !!! override 采用cache存储 重启命令执行时间 和 worker $startTime 比较
     * @param $startTime
     * @return bool
     */
    protected function queueShouldRestart($startTime)
    {
        return false;
    }

    protected function stop()
    {
        throw new \Exception('队列 worker 内存超过设定阈值, queue: ' . $this->queueTube);
    }
}
