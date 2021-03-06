<?php

namespace App\Rankings\Parsers\Leaderboards;

use App\Leaderboard;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class LeaderboardParser
{
    /**
     * Saved Rank for Teams
     *
     * @var
     */
    private static $rank_saved;

    /**
     * Query information from Battlenet
     *
     * @var
     */
    public $self;
    
    public $purgatory = [];

    /**
     * Parsed leaderboard
     *
     * @var
     */
    public $leaderboard = [];
    private $needs_record;
    private $test = [];

    public function __construct()
    {
        $this->self = new stdClass;
    }

    /**
     * Parse the rankings
     *
     * @param array $data
     * @param int|null $limit
     * @return array
     */
    public function parse(array $data) : array
    {
        foreach ($data as $array) {
            if ($array instanceof \GuzzleHttp\Exception\RequestException) {
                continue;
            }

            $this->getRankings(
                json_decode($array->getBody()->getContents())
            );
        }

        return $this->leaderboard;
    }

    /**
     * Parse the rankings from the response
     *
     * @param array $leaderboard
     * @param int $limit
     */
    public function getRankings(stdClass $leaderboard)
    {
        $this->getSelf($leaderboard);

        $i = 0;
        foreach ($leaderboard->row as $record) {
            $this->getLeaderboardData($record);

            $i++;

            switch($this->self->players) {
                case 1:
                    if ($i == 100) {
                        break 2;
                    }
                break;
                case 2:
                    if ($i == 100) {
                        break 2;
                    }
                break;
                case 3:
                    if ($i == 100) {
                        break 2;
                    }
                break;
                case 4:
                    if ($i == 100) {
                        break 2;
                    }
                break;
            }
        }

        $this->findOrphans();
    }

    /**
     * I'm going to fix this don't worry
     */
    private function findOrphans()
    {
        foreach ($this->leaderboard as $record) {
            $record['players'] = array_map(function ($i) {
                if (!isset($i->battlenet_hero_id)) {
                    if (array_key_exists($i->battle_tag, $this->purgatory)) {
                        $i->battlenet_hero_id = $this->purgatory[$i->battle_tag]->battlenet_hero_id;
                    }
                }

                return $i;
            }, $record['players']);
        }
    }

    /**
     * Get the self attributes of the response
     *
     * @param stdClass $leaderboard
     * @return mixed
     */
    public function getSelf(stdClass $leaderboard)
    {
        $this->self->hardcore = $leaderboard->hardcore ?? false;
        $this->self->greater_rift = $leaderboard->greater_rift ?? false;

        if (isset($leaderboard->season)) {
            $this->self->season = true;
            $this->self->period = $leaderboard->season;
        } else {
            $this->self->season = false;
            $this->self->period = $leaderboard->era;
        }

        $this->self->players = isset($leaderboard->greater_rift_solo_class)
            ? 1
            : $leaderboard->greater_rift_team_size;

        $explode = explode('/', $leaderboard->_links->self->href);
        $this->self->region = strtoupper(substr($explode[2], 0, 2));
    }

    /**
     * 
     * @param  [type] $record [description]
     * @return [type]         [description]
     */
    public function getLeaderboardData($record)
    {
        $players = $this->getPlayersData($record->player);
        $data = array_merge(
            (array) $this->self,
            (array) $this->parseJson($record->data)
        );


        if (!isset($data['rank'])) {
            if (empty($players[0])) {
                return;
            }

            foreach ($players as $player) {
                if (!isset($player->battle_tag)
                    || !isset($player->battlenet_hero_id)
                ) {
                    continue;
                }

                $this->purgatory[$player->battle_tag] = $player;
            }
        } else {
            $this->leaderboard[] = compact('players', 'data');
        }
    }

    /**
     * @param $players
     */
    public function getPlayersData(array $players) : array
    {
        $bnet_players = [];

        foreach ($players as $player) {
            $data = $this->getPlayerData($player->data);

            $bnet_players[] = $data;
        }

        return $bnet_players;
    }

    /**
     * Retrieve player data from response
     *
     * @param array $players
     */
    public function getPlayerData(array $player) : stdClass
    {
        $profile = $this->parseJson($player);

        if (empty($profile->battle_tag)
            && empty($profile->hero_battle_tag)
        ) {
            $profile->battle_tag = '';
        } else {
            $profile->battle_tag = $profile->battle_tag ?? $profile->hero_battle_tag;
            unset($profile->hero_battle_tag);
        }

        $profile->class = str_replace(' ', '-', $profile->class);

        $profile->gender = $profile->gender == 'm'
            ? 1
            : 0;

        $profile = array_merge(
            (array) $profile,
            (array) $this->self
        );

        return (object) $profile;
    }

    /**
     * @param $json
     * @return stdClass
     */
    public function parseJson($json) : stdClass
    {
        $ladder_data = new stdClass;

        foreach ($json as $data) {
            list($attr1, $attr2) = array_keys((array)$data);

            $key = snake_case($data->$attr1);

            if (substr($key, 0, 5) == 'hero_') {
                $key = $key !== 'hero_id'
                    ? str_replace('hero_', '', $key)
                    : 'battlenet_hero_id';
            }

            $ladder_data->$key = $data->$attr2;
        }

        return $ladder_data;
    }
}