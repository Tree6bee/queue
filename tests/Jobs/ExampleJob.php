<?php

namespace Tests\Tree6bee\Queue\Jobs;

use Tree6bee\Queue\Job;

class ExampleJob extends Job
{
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

        throw new \Exception('handle job...lol ^_^');
    }
}
