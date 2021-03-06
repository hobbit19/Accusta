<?php

namespace App\Jobs;

use App\semas\AdminNotify;
use App\semas\GolosApi;
use App\semas\SteemitApi;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetHistoryAccountUpdateInCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $acc;
    private $api;
    private $processed;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($acc, $processed, $api = 'golos')
    {
        $this->acc = $acc;
        $this->api = $api;
        $this->processed = $processed;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        dump('Start getting ' . $this->acc, $this->api);
        try {
            if ($this->api == 'golos') //GolosApi::getHistoryAccountFullInCache($this->acc);
            {
                GolosApi::getHistoryAccountUpdateInDBDesc($this->acc, $this->processed);
            }
            if ($this->api == 'steemit') //SteemitApi::getHistoryAccountFullInCache($this->acc);
            {
                SteemitApi::getHistoryAccountUpdateInDBDesc($this->acc, $this->processed);
            }
        } catch (Exception $e) {
            dump($e->getTraceAsString());
            AdminNotify::send('Jobs failed: ' . print_r($e->getTraceAsString(), true));

            if ($this->api == 'golos') {
                GolosApi::disconnect();
            }
            if ($this->api == 'steemit') {
                SteemitApi::disconnect();
            }
        }
        dump('-------done--');

    }
}
