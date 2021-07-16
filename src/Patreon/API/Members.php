<?php


namespace daemionfox\Patreon\API;


use daemionfox\Exceptions\PatreonCacheException;

class Members extends APIAbstract implements PatreonAPIInterface
{

    protected $cacheFile = 'patreonMembers.cache.json';
    protected static $instance;
    protected $campaignID;
    protected $tiers = [];
    protected $members = [];
    protected $includeInactive = false;

    /**
     * @return PatreonAPIInterface
     */
    public function get(): PatreonAPIInterface
    {

        try {
            $data = $this->getCache($this->cacheFile);
            $this->campaignID = $data['campaignID'];
            $this->tiers = $data['tiers'];
            $this->members = $data['members'];
        } catch (PatreonCacheException $pce) {
            /**
             * @var $campaign Campaigns
             */
            $campaign = Campaigns::init($this->accessToken);
            $this->campaignID = $campaign->getCampaignID();
            $this->tiers = $campaign->getTiers();
            $this->members = $this->fetchMembers();
            if (empty($this->members)) {
                $this->members = [];
            }
            try {
                $data = [
                    'campaignID' => $this->campaignID,
                    'tiers' => $this->tiers,
                    'members' => $this->members
                ];
                $this->saveCache($this->cacheFile, $data);
            } catch (PatreonCacheException $pe) {

            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignID(): string
    {
        return $this->campaignID;
    }

    /**
     * @return array
     */
    public function getTiers(): array
    {
        return $this->tiers;
    }

    /**
     * @return array
     */
    public function getMembers(): array
    {
        return $this->members;
    }


    /**
     * @return array
     */
    protected function fetchMembers()
    {

//        $memberList = $this->patreon->fetch_page_of_members_from_campaign($this->campaignID, 1);
        $suffix = "campaigns/{$this->campaignID}/members?page%5Bcount%5D=20";
        $memberList = $this->getAPIData($suffix);

        $lastDay = $month_start = strtotime('first day of this month', time()) - 86400;
        foreach($memberList['data'] as $m) {
            $suffix = "members/{$m['id']}?fields%5Bmember%5D=full_name,is_follower,last_charge_date&include=currently_entitled_tiers";
            $member = $this->getAPIData($suffix);

            $tier = [];
            foreach ($member['data']['relationships']['currently_entitled_tiers']['data'] as $t) {
                if (empty($tier) || $tier['amount'] < $this->tiers[$t['id']]['amount']) {
                    $tier = $this->tiers[$t['id']];
                }
            }

            $isActive = !empty($tier);

            if (!$isActive) {
                continue;
            }

            $members[$member['data']['id']] = [
                'id' => $member['data']['id'],
                'name' => $member['data']['attributes']['full_name'],
                'isActive' => $isActive,
                'tier' => !empty($tier['title']) ? $tier['title'] : null
            ]
            ;
        }
        return $members;
    }



    /**
     * @param $token
     * @return PatreonAPIInterface
     */
    public static function init($token):PatreonAPIInterface
    {
        if (empty(self::$instance)) {
            self::$instance = new self($token);
        }
        return self::$instance;
    }



}