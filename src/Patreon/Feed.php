<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/27/17
 * Time: 5:07 PM
 */

namespace daemionfox\Patreon;


use daemionfox\Patreon\Components\Campaign;
use daemionfox\Patreon\Components\Post;
use daemionfox\Patreon\Components\User;
use LSS\Array2XML;

/**
 * Class Feed
 * @package daemionfox\Patreon
 */
class Feed extends PatreonRSS
{
    /**
     * Path to Patreon
     */
    const PATREON_BASE = "https://www.patreon.com";
    /**
     * URL portion of Patreon's posts
     */
    const PATREON_POST = "posts";

    /**
     * @var string
     */
    protected $creator;

    /**
     * @var string
     */
    protected $creatorID;

    /**
     * @var array
     */
    protected $posts;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Campaign
     */
    protected $campaign;

    /**
     * @var bool
     */
    protected static $useCache = false;

    /**
     * @var string
     */
    protected static $cachePath = '/tmp';

    /**
     * @var string
     */
    protected $cacheFile = 'patreon.rss';

    /**
     * @var int
     */
    protected $cacheExpires = 900;

    /**
     * @var array
     */
    protected $optionalFields = array(
        'comment_count',
        'like_count',
        'post_file',
        'image',
        'thumbnail_url',
        'embed',
        'is_paid',
        'pledge_url',
        'patreon_url',
        'patron_count',
        'upgrade_url',
    );

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Feed constructor.
     * @param null $creator
     */
    public function __construct($creator = null)
    {
        if ($creator !== null) {
            $this->setCreator($creator);
        }
    }

    /**
     * @param $field
     * @return $this
     * @throws \Exception
     */
    public function addField($field)
    {
        if (!in_array(strtolower($field), $this->optionalFields)) {
            throw new \Exception("Field '{$field}' is not supported");
        }

        if (!in_array(strtolower($field), $this->fields['post'])) {
            $this->fields['post'][] = $field;
        }

        return $this;
    }

    /**
     * @param $user
     * @return string
     * @throws \Exception
     */
    public function findCreatorID($user)
    {
        $url = self::PATREON_BASE . "/{$user}/" . self::PATREON_POST;
        $string = @file_get_contents($url);
        if(empty($string)) {
            throw new \Exception("Could not find the creator id");
        }
        $pattern = '/\s*"creator_id":\s*([0-9]+)\s*/';
        if ($string !== false && false !== preg_match($pattern, $string, $matches)) {
            if (isset($matches[1])) {
                return $matches[1];
            }
        }
        throw new \Exception("Could not find the creator id");
    }

    /**
     * @return string
     */
    public function rss()
    {
        $posts = $this->getPosts();
        $array = array(
            'channel' => array(
                'items' => array()
            )
        );

        /**
         * @var $p Post
         */
        foreach ($posts as $p) {
            $array['channel']['items'][] = $p->toRSS();
        }

        $xml = Array2XML::createXML('rss', $array);
        return $xml->saveXML();
    }

    /**
     * @return array
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param string $creator
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->cacheFile = $creator . ".patreon.rss";
        try {
            $this->loadCache();
        } catch (\Exception $e) {
            $this->creator = $creator;
            $this->creatorID = $this->findCreatorID($creator);
            parent::__construct($this->creatorID);
            $this->getPatreon();
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function getPatreon()
    {
        try {
            $this->loadCache();
        } catch( \Exception $e) { }

        $posts = $this->getData();

        if (!empty($posts['posts'])) {
            foreach ($posts['posts'] as $post) {
                if (!empty($post['published_at'])) {
                    $post['published_at_calc'] = date('r', strtotime($post['published_at']));
                }

                if (empty($post['min_cents_pledged_to_view']) || intval($post['min_cents_pledged_to_view']) === 0) {
                    $post['pledge_level_calc'] = 'public';
                } elseif (intval($post['min_cents_pledged_to_view']) === 1) {
                    $post['pledge_level_calc'] = 'patreon';
                } else {
                    $post['pledge_level_calc'] = sprintf("%0.2f", $post['min_cents_pledged_to_view']/100);
                }

                $postObj = new Post();
                $postObj->load($post);

                $this->posts[] = $postObj;
            }
        }

        if (!empty($posts['user'])) {
            $this->user = new User();
            $this->user->load($posts['user']);
        }

        if (!empty($posts['campaign'])) {
            $this->campaign = new Campaign();
            $this->campaign->load($posts['campaign']);
        }

        $this->saveCache();

        return $this;
    }

    protected function toArray()
    {
        $array = array(
            'creator' => $this->creator,
            'creatorID' => $this->creatorID,
            'user' => $this->user->toArray(),
            'campaign' => $this->campaign->toArray()
        );

        /**
         * @var $p Post
         */
        foreach ($this->posts as $p) {
            $array['posts'][] = $p->toArray();
        }
        return $array;
    }

    protected function loadCache()
    {
        $now = time();
        if (!self::$useCache) {
            throw new \Exception("Cache is not active");
        } elseif (!file_exists(self::$cachePath . "/{$this->cacheFile}")) {
            throw new \Exception("Cache file does not exist");
        } elseif (($now - filemtime(self::$cachePath . "/{$this->cacheFile}")) > $this->cacheExpires) {
            throw new \Exception("Cache has expired");
        }
        $this->user = new User();
        $this->campaign = new Campaign();
        $this->posts = array();

        $input = json_decode(file_get_contents(self::$cachePath . "/{$this->cacheFile}"), true);
        $this->creator = $input['creator'];
        $this->creatorID = $input['creatorID'];
        $this->user->load($input['user']);
        $this->campaign->load($input['campaign']);

        foreach ($input['posts'] as $p) {
            $post = new Post();
            $post->load($p);
            $this->posts[] = $post;
        }

        return $this;
    }

    protected function saveCache()
    {
        if (!self::$useCache) {
            return $this;
        }
        $output = $this->toArray();
        file_put_contents(self::$cachePath . "/{$this->cacheFile}", json_encode($output, JSON_PRETTY_PRINT));
        return $this;

    }

    /**
     * @param bool $useCache
     * @return $this
     */
    static public function setUseCache($useCache)
    {
        self::$useCache = $useCache;
    }

    /**
     * @param string $cachePath
     * @return $this
     */
    public static function setCachePath($cachePath)
    {
        self::$cachePath = $cachePath;
    }

    /**
     * @param int $cacheExpires
     * @return $this
     */
    public function setCacheExpires($cacheExpires)
    {
        $this->cacheExpires = $cacheExpires;
        return $this;
    }


}
