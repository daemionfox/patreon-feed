<?php


namespace daemionfox\Patreon\API;

use daemionfox\Exceptions\PatreonCacheException;

class Posts extends APIAbstract implements PatreonAPIInterface
{
    protected $cacheFile = 'patreonPosts.cache.json';
    protected static $instance;
    protected $campaignID;
    protected $posts = [];
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


    public function get():PatreonAPIInterface
    {
        try {
            $data = $this->getCache($this->cacheFile);
            $this->campaignID = $data['campaignID'];
            $this->posts = $data['posts'];
        } catch (PatreonCacheException $pce) {
            /**
             * @var $campaigns Campaigns
             */
            $campaigns = Campaigns::init($this->accessToken);
            $this->campaignID = $campaigns->getCampaignID();
            $this->posts = $this->fetchPosts();



            try {
                $data = [
                    'campaignID' => $this->campaignID,
                    'posts' => $this->posts
                ];
                $this->saveCache($this->cacheFile, $data);
            } catch (PatreonCacheException $pe) {

            }
        }


        return $this;
    }

    protected function fetchPosts()
    {
        $suffix = "campaigns/{$this->campaignID}/posts?fields%5Bpost%5D=" . join(",", $this->fields);
        $posts = $this->getAPIData($suffix);
        $data = [];
        foreach ($posts['data'] as $post) {
            $published = $post['attributes']['published_at'];
            $temp = array(
                "id" => $post['id'],
                "title" => $post['attributes']['title'],
                "content" => $post['attributes']['content'],
                "url" => self::$patreonURL . $post['attributes']['url'],
                "published_at" => $published,
                "published_timestamp" => strtotime($published),
                "published_date" => date("Y-m-d", strtotime($published)),
                "is_public" =>  $post['attributes']['is_public'],
            );
            $data[$post['id']] = $temp;
        }
        usort($data, function($a, $b) {
            return $a['published_timestamp'] <= $b['published_timestamp'];
        });

        return $data;
    }

    /**
     * @return mixed
     */
    public function getCampaignID()
    {
        return $this->campaignID;
    }

    /**
     * @return array
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string[] $fields
     * @return Posts
     */
    public function setFields(array $fields): Posts
    {
        $this->fields = $fields;
        return $this;
    }



    public static function init($token):PatreonAPIInterface
    {
        if (empty(self::$instance)) {
            self::$instance = new self($token);
        }
        return self::$instance;
    }
}