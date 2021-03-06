<?php

namespace App\Widgets;

use App\semas\BchApi;
use Arrilot\Widgets\AbstractWidget;
use Jenssegers\Date\Date;
use MongoDB;


class WitnessSupportVotes extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $voteFor = [];
        $voteForHistory = [];
        $forWitness = [];
        $forWitnessHistory = [];

        //account_witness_vote
        $collection = BchApi::getMongoDbCollection($this->config['account']);
        $data = $collection->find(['op' => 'account_witness_vote'], ['sort' => ['timestamp' => 1]]);
        if ($data) {
            //dump($data->toArray());

            foreach ($data as $datum) {
                //dump(collect($datum));
                $arr['witness'] = $datum['op'][1]['witness'];
                $arr['account'] = $datum['op'][1]['account'];
                $arr['approve'] = $datum['op'][1]['approve'];
                $arr['timestamp'] = $datum['timestamp'];
                $arr['date'] = Date::parse($datum['timestamp'])->format('Y F d H:i:s');
                if ($arr['approve']==true){
                    $arr['status'] = 'Approve';
                }
                if ($arr['approve']==false){
                    $arr['status'] = 'Disapprove';
                }

                if ($datum['op'][1]['account'] == $this->config['account']) { // account votes for other witnesses

                    $voteFor[$arr['witness']] = $arr;
                    $voteForHistory[] = $arr;
                    if ($datum['op'][1]['approve'] == false) {
                        unset($voteFor[$arr['witness']]);
                    }
                }
                else { // votes for witness
                    $forWitness[$arr['account']] = $arr;
                    $forWitnessHistory[] = $arr;
                    if ($datum['op'][1]['approve'] == false) {
                        unset($forWitness[$arr['account']]);
                    }
                }

            }
        }
        $voteFor = collect($voteFor)->sortByDesc('timestamp');
        $voteForHistory = collect($voteForHistory)->sortByDesc('timestamp');
        $forWitness = collect($forWitness)->sortByDesc('timestamp');
        $forWitnessHistory = collect($forWitnessHistory)->sortByDesc('timestamp');
        //dump($voteFor, $forWitness);
        //dump($forWitnessHistory[''])
        return view(getenv('BCH_API') . '.widgets.witness_support_votes', [
            'config' => $this->config,
            'voteFor' => $voteFor->toArray(),
            'forWitness' => $forWitness->toArray(),
            'account' => $this->config['account'],
            'forWitnessHistory' => $forWitnessHistory->toArray(),
            'voteForHistory' => $voteForHistory->toArray()
        ]);
    }
}
