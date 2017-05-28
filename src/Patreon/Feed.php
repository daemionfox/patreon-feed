<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/27/17
 * Time: 5:07 PM
 */

namespace daemionfox\Patreon;


use LSS\Array2XML;

class Feed extends PatreonRSS
{
    const PATREON_BASE = "https://www.patreon.com";
    const PATREON_POST = "posts";

    protected $creator;

    protected $creatorID;

    protected $posts;

    public function __construct($creator)
    {
        $this->creator = $creator;
        $this->creatorID = $this->findCreatorID($creator);
        parent::__construct($this->creatorID);
    }

    public function findCreatorID($user)
    {
        $url = self::PATREON_BASE . "/{$user}/" . self::PATREON_POST;
        $string = file_get_contents($url);
        $pattern = '/\s*"creator_id":\s*([0-9]+)\s*/';
        if (false !== preg_match($pattern, $string, $matches)) {
            return $matches[1];
        }
        throw new \Exception("Could not find the creator id");
    }

    public function rss()
    {
        $posts = $this->getPosts();

        $array = array(
            'channel' => array(
                $posts
            )
        );


        $xml = Array2XML::createXML('rss', $array);
        return $xml->saveXML();
    }

    public function getPosts()
    {
        $this->posts = $this->getData();

        $itemMap = array(
            'title' => 'title',
            'description' => 'content',
            'link' => 'url',
            'guid' => 'url',
            'pubdate' => 'published_at_calc',
            'pledgelevel' => 'pledge_level_calc'
        );

        $output = array(
            'item' => array()
        );

        if (empty($this->posts['posts'])) {
            throw new \Exception("No posts found");
        }

        foreach ($this->posts['posts'] as $post) {
            $item = array();
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

            foreach ($itemMap as $mapKey => $postKey) {
                if (!empty($post[$postKey])) {
                    $item[$mapKey] = (string)$post[$postKey];
                }
            }
            $output['item'][] = $item;
        }
        return $output;
    }



}
