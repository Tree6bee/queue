# queue
A job queue based on beanstalkd(and other queue system), easy to dispatch job and handle job.

### Keywords

`queue`, `job`, `easy handle`, `job with timeout`, `delayed job`, `retry_after`, `given connection`, `given queue`, `given tube`, `memory limit`,`reload`

### Installation

`composer require "tree6bee/queue:~2.0"`

### Overview

* [Create Queue](#create-queue)
* [Create Job](#create-job)
* [Dispatch Job](#dispatch-job)
* [Process Job](#process-job)
* [Recommend](#recommend)
* [Example](#example)
* [Todo](#todo)

### Create Queue

```
use Tree6bee\Queue\Drivers\Beanstalkd;
use Tree6bee\Queue\Queue;

$queue = new Queue(new Beanstalkd($host, $port));
```

### Create Job

```
<?php

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
    public $words;

    public function __construct(array $words)
    {
        $this->words = $words;
    }

    public function handle()
    {
        var_export($this->words);

        var_dump($this->retry_after, $this->tries);

        // throw new \Exception('handle job with error...lol ^_^');
    }
}
```

specifying job queue by defining `$queue` , specifying Max Job Attempts by defining `$tries` , specifying timeout Values by defining `$retry_after` .

### Dispatch Job

```
$queue->push(new ExampleJob(['i', 'love', 'china']));
```

of cause, you can dispatch job later (push a delayed job) :

```
$queue->later(60, new ExampleJob(['i', 'love', 'china']));
```

### Process Job

```
$worker = new Worker($queue);

$worker->daemon();
```

> Note: `$worker->daemon()` is blocking.

by default, the worker will will listen  the tube named `default`, you can specifying worker queue (beanstalkd tube) like :

```
$queueTube = 'sendEmail';
$worker = new Worker($queue, $queueTube);

$worker->daemon();
```

you can specifying worker with sleep time while there is no job, and memoryLimit, like :

```
$sleep = 60;
$memoryLimit = 128;

$queueTube = 'sendEmail';
$worker = new Worker($queue, $queueTube);

$worker->daemon($sleep, $memoryLimit);
```

***Notice***, if you want reload queue worker, you should implement `queueShouldRestart`method on class `Tree6bee\Queue\Worker`.

### Recommend

it's highly recommended that extends `Tree6bee\Queue\Worker`, and you should implements these methods: `logProcessError`, `handleWithObj`, `queueShouldRestart`.

#### Other

the queue package can work on other queue system, you should implements these interface `Tree6bee\Queue\Drivers\Jobs\JobInterface`, `Tree6bee\Queue\Drivers\QueueInterface`.

### Example

* [https://github.com/Tree6bee/queue/blob/master/tests/QueueTest.php](https://github.com/Tree6bee/queue/blob/master/tests/QueueTest.php)
* [https://github.com/Tree6bee/ctx_base/blob/master/tests/Service/Ctx/test_queue.php](https://github.com/Tree6bee/ctx_base/blob/master/tests/Service/Ctx/test_queue.php)

### Link

* [beanstalkd protocol](https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt) 
* [beanstalkd协议](https://github.com/kr/beanstalkd/blob/master/doc/protocol.zh-CN.md)

### Todo

* add some useful command, like clearn queue job or kick buried job, etc.


