<?php


namespace daemionfox\Patreon;


use daemionfox\Traits\CacheTrait;
use daemionfox\Exceptions\PatreonCacheException;
use daemionfox\Patreon\API\Campaigns;
use daemionfox\Patreon\API\Posts;
use LSS\Array2XML;

class Feed
{
    use CacheTrait;

    protected $token;
    protected $cacheFile = 'patreonRSS.cache.json';
    protected $postLimit = 10;
    protected $showPrivatePosts = false;

    /**
     * PatreonFeed constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return false|string
     */
    public function rss()
    {
        try {
            $data = $this->getCache($this->cacheFile);
        } catch (PatreonCacheException $pce) {

            /**
             * @var $campaign Campaigns
             */
            $campaign = Campaigns::init($this->token);
            $dateString = date('r', time());
            $data = [
                    'channel' => [
                        'title' => $campaign->getName() . " Patreon Posts",
                        'description' => $campaign->getSummary(),
                        'link' => $campaign->getUrl(),
                        'pubDate' => $dateString,
                        'lastBuildDate' => $dateString,
                        'item' => $this->buildItems()
                    ],
                    '@attributes' => [
                        'version' => '2.0'
                    ]
            ];

            try {
                $this->saveCache($this->cacheFile,$data);
            } catch (PatreonCacheException $pe) {

            }
        }

        $xml = Array2XML::createXML('rss', $data);
        return $xml->saveXML();

    }

    /**
     * @return array
     */
    protected function buildItems(): array
    {
        /**
         * @var $postObj Posts
         */
        $postObj = Posts::init($this->token);
        $posts = $postObj->getPosts();
        $item = [];
        $punct = ['.', ',', '?', '!', ':', ';','�'];
        $cnt = 0;
        foreach ($posts as $p) {
            if ($this->postLimit > 0 && $cnt >= $this->postLimit) {
                continue;
            }
            if (!$this->showPrivatePosts && $p['is_public'] !== true) {
                continue;
            }
            $content = trim(substr(strip_tags($p['content']),0, 197));
            $lastChar = substr($content, -1, 1);
            if (!in_array($lastChar, $punct)) {
                $content .= "...";
            }
            $item[] = [
                'title' => $p['title'],
                'link' => $p['url'],
                'description' => $content,
                'pubDate' => date('r', $p['published_timestamp'])
            ];
            $cnt++;
        }
        return $item;
    }

    /**
     * @param int $postLimit
     * @return Feed
     */
    public function setPostLimit(int $postLimit): Feed
    {
        $this->postLimit = $postLimit;
        return $this;
    }


    /**
     * @param bool $showPrivatePosts
     * @return Feed
     */
    public function setShowPrivatePosts(bool $showPrivatePosts): Feed
    {
        $this->showPrivatePosts = $showPrivatePosts;
        return $this;
    }

}
