<?php

namespace App\Console\Commands;

use App\Jobs\UpdateLeaderboard;
use App\Rankings\API\DiabloAPI;
use App\Rankings\Parsers\Leaderboards\LeaderboardParser;
use App\Rankings\Services\Leaderboards\LeaderboardService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;

class UpdateLeaderboards extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:update {team_class} {mode} {period} {type}';
    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Update ladder records';
    /**
     * Leaderboard service
     * @var object
     */
    private $service;
    /**
     * Leaderboard API
     * @var object
     */
    private $api;
    /**
     * Leaderboard Parser
     * @var object
     */
    private $parser;

    /**
     * Create a new command instance.
     *
     * @param LeaderboardParser $parser
     * @param DiabloAPI $api
     * @param LeaderboardService $service
     */
    public function __construct(
        LeaderboardParser $parser,
        LeaderboardService $service,
        DiabloAPI $api
    )
    {
        parent::__construct();

        $this->parser = $parser;
        $this->api = $api;
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Updating leaderboards...');
        $t = microtime(true);

        $request = $this->api->{$this->argument('team_class')}(
            $this->argument('mode'),
            $this->argument('period'),
            $this->argument('type')
        );

        $rankings = $this->parser->parse(
            $request
        );

        $bar = $this->output->createProgressBar(count($rankings));
        foreach ($rankings as $record) {
            if (empty($record['data']['battle_tag'])) {
                continue;
            }

            $job = (new UpdateLeaderboard($record))->onQueue('leaderboards');
            $this->dispatch($job);

            $bar->advance();
        }

        $bar->finish();

        $this->info(PHP_EOL . 'Leaderboard updated in ' . (microtime(true) - $t) . ' seconds');
    }
}
