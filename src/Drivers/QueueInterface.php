<?php

namespace Tree6bee\Queue\Drivers;

interface QueueInterface
{
    /**
     * 立即分发到队列
     * Push a new job onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  int     $ttr Time To Run: retry_after 允许worker执行的最大秒数
     *
     * @return int
     */
    public function push($queue, $payload, $ttr);

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
    public function later($delay, $queue, $payload, $ttr);

    /**
     * @param $queue
     *
     * @return null|\Tree6bee\Queue\Drivers\Jobs\JobInterface
     */
    public function pop($queue);
}
