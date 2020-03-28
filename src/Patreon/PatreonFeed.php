<?php


namespace daemionfox\Patreon;


use daemionfox\Exceptions\PatreonCacheException;
use LSS\Array2XML;
use Patreon\API;

class PatreonFeed
{
    protected $patreon;
    protected $accessKey;

    protected static $showPrivatePosts = false;
    protected static $alllowCache = false;
    protected static $cacheDir;
    protected static $cacheTimeout = 14400; // 4 hours
    protected static $cacheFile = 'patreon-rss.cache';
    protected static $patreonURL = "https://patreon.com";

    protected $fields = array(
        "title",
        "content",
        "is_paid",
        "is_public",
        "published_at",
        "url",
        "embed_data",
        "embed_url",
        "app_id",
        "app_status"
    );

    public function __construct($accessKey)
    {
        $this->accessKey = $accessKey;
        $this->patreon = new API($accessKey);
    }

    public function rss()
    {
        $data = $this->getData();

        $rssData = array(
            'rss' => array(
                'channel' => $this->getRSSChannelInfo($data),
                'items' => array('item' => $this->getRSSItems($data)),
                '@attributes' => array(
                    'version' => '2.0'
                )
            )
        );

        $xml = Array2XML::createXML('rss', $rssData);
        return  $xml->saveXML();
    }

    protected function getRSSItems($data)
    {
        $output = array();
        foreach ($data['posts'] as $p) {
            $output[] = array(
                'title' => $p['title'],
                'description' => self::$showPrivatePosts ? $p['content'] : ($p['is_public'] ? $p['content'] : "See this post at Patreon"),
                'link' => $p['url'],
                'guid' => $p['url'],
                'pubDate' => $p['published_at']
            );
        }

        return $output;
    }

    protected function getRSSChannelInfo($data)
    {
        $output = array(
            'title' => $data['meta']['name'] . " Patreon",
            'description' => null,
            'link' => $data['meta']['url']
        );

        return $output;
    }

    public function getData()
    {
        try {
            $data = $this->getCache();
        } catch (PatreonCacheException $pe) {
            $data = array();
            $campaigns = $this->getCampaigns();
            $user = $this->patreon->fetch_user();
            $lastPublished = "01-01-1970T00:00:00";
            $data['meta'] = array(
                'name' => isset($user['data']['attributes']['full_name']) ? $user['data']['attributes']['full_name'] : null,
                'url' => isset($user['data']['attributes']['url']) ? $user['data']['attributes']['url'] : null,
                'email' => isset($user['data']['attributes']['email']) ? $user['data']['attributes']['email'] : null,
            );

            foreach ($campaigns as $c) {
                $posts = $this->getPosts($c);
                foreach ($posts['data'] as $p) {
                    $temp = array(
                        "title" => $p['attributes']['title'],
                        "content" => $p['attributes']['content'],
                        "url" => self::$patreonURL . $p['attributes']['url'],
                        "published_at" => $p['attributes']['published_at'],
                        "is_public" => $p['attributes']['is_public'],
                    );
                    $data['posts'][] = $temp;
                    if (strtotime($p['attributes']['published_at']) > strtotime($lastPublished)){
                        $lastPublished = $p['attributes']['published_at'];
                    }
                }
                usort($data['posts'], function($a, $b){ return strtotime($a['published_at']) <= strtotime($b['published_at']);});
                $data['meta']['published'] = $lastPublished;
            }
            try {
                $this->saveCache($data);
            } catch (PatreonCacheException $pe) {}
        }
        return $data;
    }

    /**
     * @return mixed
     * @throws PatreonCacheException
     */
    protected function getCache()
    {
        if (self::$alllowCache === true) {
            if (empty(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir not set");
            }
            if (!is_dir(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir does not exist");
            }

            $cachePath = self::$cacheDir . "/" . self::$cacheFile;
            if (!file_exists($cachePath)) {
                throw new PatreonCacheException("Cache file does not exist");
            }

            $data = json_decode(file_get_contents($cachePath), true);
            if (empty($data)) {
                throw new PatreonCacheException("No data found");
            }

            return $data;
        }
        throw new PatreonCacheException("Cache is not active");
    }

    /**
     * @param $data
     * @throws PatreonCacheException
     */
    protected function saveCache($data)
    {
        if (self::$alllowCache === true) {
            if (empty(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir not set");
            }
            if (!is_dir(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir does not exist");
            }

            $cachePath = self::$cacheDir . "/" . self::$cacheFile;
            file_put_contents($cachePath, json_encode($data, JSON_PRETTY_PRINT));
        }
        throw new PatreonCacheException("Cache is not active");
    }

    protected function getCampaignData($campaign)
    {
        $data = $this->patreon->fetch_campaign_details($campaign);
        return $data;
    }

    protected function getCampaigns()
    {
        $campaigns = $this->patreon->fetch_campaigns();
        $ids = array();
        foreach ($campaigns['data'] as $c) {
            if (isset($c['id'])) {
                $ids[] = $c['id'];
            }
        }
        return $ids;
    }

    protected function getPosts($campaign)
    {
        $posts = $this->patreon->get_data("campaigns/{$campaign}/posts?fields%5Bpost%5D=" . join(",", $this->fields));
        return $posts;
    }

    public static function setShowPrivatePosts(bool $showPrivatePosts)
    {
        self::$showPrivatePosts = $showPrivatePosts;
    }

}
