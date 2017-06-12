<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/28/17
 * Time: 11:52 AM
 */

namespace daemionfox\Patreon\Components;


class Post extends AbstractComponent
{
    protected $post_type;
    protected $title;
    protected $content;
    protected $url;
    protected $published_at;
    protected $comment_count;
    protected $like_count;
    protected $post_file;
    protected $image;
    protected $thumbnail_url;
    protected $embed;
    protected $is_paid;
    protected $pledge_url;
    protected $patreon_url;
    protected $patron_count;
    protected $upgrade_url;

    protected $published_at_calc;
    protected $pledge_level_calc;

    /**
     * @return array
     */
    public function toRSS()
    {
        $itemMap = array(
            'description'   => 'content',
            'link'          => 'url',
            'guid'          => 'url',
            'pubdate'       => 'published_at_calc',
            'pledge_level'  => 'pledge_level_calc',
        );

        $class = get_class($this);
        $vars  = get_class_vars($class);

        $output = array();
        $seen = array();
        foreach ($itemMap as $key => $val)
        {
            if (property_exists($this, $val) && !empty($this->$val)) {
                $output[$key] = $this->$val;
                $seen[$val] = 1;
            }
        }

        foreach ($vars as $k=>$v) {
            if (property_exists($this, $k) && !empty($this->$k) && !isset($seen[$k])) {
                $output[$k] = $this->$k;
                $seen[$k] = 1;
            }
        }
        return $output;
    }

    /**
     * @return mixed
     */
    public function getPostType()
    {
        return $this->post_type;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getPublishedAt()
    {
        return $this->published_at;
    }

    /**
     * @return mixed
     */
    public function getCommentCount()
    {
        return $this->comment_count;
    }

    /**
     * @return mixed
     */
    public function getLikeCount()
    {
        return $this->like_count;
    }

    /**
     * @return mixed
     */
    public function getPostFile()
    {
        return $this->post_file;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        $image = $this->image;
        $url = isset($image['url']) ? urldecode($image['url']) : null;
        return $url;
    }

    /**
     * @return mixed
     */
    public function getImageLargeUrl()
    {
        $image = $this->image;
        $url = isset($image['large_url']) ? urldecode($image['large_url']) : null;
        return $url;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl()
    {
        $image = $this->image;
        $url = isset($image['thumb_url']) ? urldecode($image['thumb_url']) : null;
        return $url;
    }

    /**
     * @return mixed
     */
    public function getEmbed()
    {
        return $this->embed;
    }

    /**
     * @return mixed
     */
    public function getIsPaid()
    {
        return $this->is_paid;
    }

    /**
     * @return mixed
     */
    public function getPledgeUrl()
    {
        return $this->pledge_url;
    }

    /**
     * @return mixed
     */
    public function getPatreonUrl()
    {
        return $this->patreon_url;
    }

    /**
     * @return mixed
     */
    public function getPatronCount()
    {
        return $this->patron_count;
    }

    /**
     * @return mixed
     */
    public function getUpgradeUrl()
    {
        return $this->upgrade_url;
    }

    /**
     * @return mixed
     */
    public function getPublishedAtCalc()
    {
        return $this->published_at_calc;
    }

    /**
     * @return mixed
     */
    public function getPledgeLevelCalc()
    {
        return $this->pledge_level_calc;
    }




}
