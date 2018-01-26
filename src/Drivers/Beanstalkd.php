<?php

namespace Tree6bee\Queue\Drivers;

use Pheanstalk\Pheanstalk;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;
use Tree6bee\Queue\Drivers\Jobs\BeanstalkdJob;

class Beanstalkd implements QueueInterface
{
    /**
     * @var Pheanstalk
     */
    protected $pheanstalk;

    public function __construct($host, $port = PheanstalkInterface::DEFAULT_PORT)
    {
        $this->pheanstalk = new Pheanstalk($host, $port);
    }

    /**
     * 立即分发到队列
     *
     * @param string $queue
     * @param string $payload
     * @param int $ttr
     *
     * @return int
     */
    public function push($queue, $payload, $ttr = PheanstalkInterface::DEFAULT_TTR)
    {
        return $this->pushRaw($queue, $payload, $ttr);
    }

    /**
     * 延时分发到队列
     *
     * @param $delay
     * @param $queue
     * @param $payload
     * @param int $ttr
     *
     * @return int
     */
    public function later($delay, $queue, $payload, $ttr = PheanstalkInterface::DEFAULT_TTR)
    {
        return $this->pushRaw($queue, $payload, $ttr, $delay);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $queue 队列
     * @param string $payload job内容
     * @param int $priority 优先级，可以为0-2^32（4,294,967,295），值越小优先级越高，默认为1024。
     * @param int $delay 延迟ready的秒数，在这段时间job为delayed状态。
     * @param int $ttr retry_after 允许worker执行的最大秒数，
     *  如果worker在这段时间不能delete，release，bury job，那么job超时，服务器将release此job，此job的状态迁移为ready。
     *  最小为1秒，如果客户端指定为0将会被重置为1。
     *
     * @return int
     */
    protected function pushRaw(
        $queue,
        $payload,
        $ttr = PheanstalkInterface::DEFAULT_TTR,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY
    ) {
        if (empty($ttr)) {
            $ttr = PheanstalkInterface::DEFAULT_TTR;
        }

        return $this->pheanstalk
            ->useTube($queue)
            ->put($payload, $priority, $delay, $ttr);
    }

    /**
     * @param $queue
     *
     * @return null|\Tree6bee\Queue\Drivers\Jobs\JobInterface
     */
    public function pop($queue)
    {
        $job = $this->pheanstalk->watchOnly($queue)->reserve(0);

        if ($job instanceof PheanstalkJob) {
            return new BeanstalkdJob($this->pheanstalk, $job, $queue);
        }

        return null;
    }
}
