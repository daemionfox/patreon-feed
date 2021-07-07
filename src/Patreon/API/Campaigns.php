<?php


namespace daemionfox\Patreon\API;

use daemionfox\Exceptions\PatreonCacheException;
use daemionfox\Exceptions\PatreonObjectException;

class Campaigns extends APIAbstract implements PatreonAPIInterface
{
    protected $cacheFile = 'patreonCampaign.cache.json';
    protected static $instance;
    protected $campaignID;
    protected $url;
    protected $name;
    protected $summary;
    protected $goals = [];
    protected $tiers = [];

    public function get():PatreonAPIInterface
    {
        try {
            $data = $this->getCache($this->cacheFile);
            $this->campaignID = $data['campaignID'];
            $this->url = $data['url'];
            $this->name = $data['name'];
            $this->summary = $data['summary'];
            $this->tiers = $data['tiers'];
            $this->goals = $data['goals'];

        } catch (PatreonCacheException $pce) {

            $campaigns = $this->patreon->fetch_campaigns();
            $this->campaignID = $campaigns['data'][0]['id'];


            $suffix = "campaigns/{$this->campaignID}?include=goals,tiers&" .
                "fields%5Bcampaign%5D=creation_name,is_nsfw,summary,url,vanity&" .
                "fields%5Btier%5D=amount_cents,title,patron_count,discord_role_ids&" .
                "fields%5Bgoal%5D=amount_cents,completed_percentage,title,reached_at"
            ;
            $campaign = $this->getAPIData($suffix);

            $this->url = self::$patreonURL . "/" . $campaign['data']['attributes']['url'];
            $this->name = $campaign['data']['attributes']['creation_name'];
            $this->summary = $campaign['data']['attributes']['summary'];

            foreach ($campaign['included'] as $i) {
                switch (strtolower($i['type'])) {
                    case "tier":
                        $tierID = $i['id'];
                        $this->tiers[$tierID] = [
                            'id' => $tierID,
                            'title' => $i['attributes']['title'],
                            'amount' => $i['attributes']['amount_cents'],
                            'discord' => $i['attributes']['discord_role_ids'],
                            'patrons' => $i['attributes']['patron_count']
                        ];
                        break;
                    case "goal":
                        $goalID = $i['id'];
                        $this->goals[$goalID] = [
                            'id' => $goalID,
                            'amount' => $i['attributes']['amount_cents'],
                            'completed' => $i['attributes']['completed_percentage'],
                            'reached' => date('Y-m-d', strtotime($i['attributes']['reached_at'])),
                            'title' => $i['attributes']['title']
                        ];
                        break;
                }
            }
            try {
                $data = [
                    'campaignID' => $this->campaignID,
                    'url' => $this->url,
                    'name' => $this->name,
                    'summary' => $this->summary,
                    'tiers' => $this->tiers,
                    'goals' => $this->goals
                ];
                $this->saveCache($this->cacheFile, $data);
            } catch (PatreonCacheException $pce) {
                // Do nothing
            }

        }
        return $this;
    }

    public function getTiers()
    {
        return $this->tiers;
    }

    public function getGoals()
    {
        return $this->goals;
    }

    public function getCampaignID()
    {
        return $this->campaignID;
    }

    public function getTier($tierID)
    {
        if (isset($this->tiers[$tierID])) {
            return $this->tiers[$tierID];
        }
        throw new PatreonObjectException("No such tier exists");

    }

    public function getGoal($goalID)
    {
        if (isset($this->goals[$goalID])) {
            return $this->goals[$goalID];
        }
        throw new PatreonObjectException("No such goal exists");

    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    public static function init($token):PatreonAPIInterface
    {
        if (empty(self::$instance)) {
            self::$instance = new self($token);
        }
        return self::$instance;
    }



}