<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/28/17
 * Time: 11:54 AM
 */

namespace daemionfox\Patreon\Components;


class Campaign extends AbstractComponent
{

    protected $created_at;
    protected $creation_count;
    protected $creation_name;
    protected $display_patron_goals;
    protected $earnings_visibility;
    protected $image_small_url;
    protected $image_url;
    protected $is_charged_immediately;
    protected $is_monthly;
    protected $is_nsfw;
    protected $is_plural;
    protected $main_video_embed;
    protected $main_video_url;
    protected $one_liner;
    protected $patron_count;
    protected $pay_per_name;
    protected $pledge_sum;
    protected $pledge_url;
    protected $published_at;
    protected $summary;
    protected $id;

    public function toRSS()
    {
        throw new \Exception("Not available");
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed
     */
    public function getCreationCount()
    {
        return $this->creation_count;
    }

    /**
     * @return mixed
     */
    public function getCreationName()
    {
        return $this->creation_name;
    }

    /**
     * @return mixed
     */
    public function getDisplayPatronGoals()
    {
        return $this->display_patron_goals;
    }

    /**
     * @return mixed
     */
    public function getEarningsVisibility()
    {
        return $this->earnings_visibility;
    }

    /**
     * @return mixed
     */
    public function getImageSmallUrl()
    {
        return $this->image_small_url;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * @return mixed
     */
    public function getIsChargedImmediately()
    {
        return $this->is_charged_immediately;
    }

    /**
     * @return mixed
     */
    public function getIsMonthly()
    {
        return $this->is_monthly;
    }

    /**
     * @return mixed
     */
    public function getIsNsfw()
    {
        return $this->is_nsfw;
    }

    /**
     * @return mixed
     */
    public function getIsPlural()
    {
        return $this->is_plural;
    }

    /**
     * @return mixed
     */
    public function getMainVideoEmbed()
    {
        return $this->main_video_embed;
    }

    /**
     * @return mixed
     */
    public function getMainVideoUrl()
    {
        return $this->main_video_url;
    }

    /**
     * @return mixed
     */
    public function getOneLiner()
    {
        return $this->one_liner;
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
    public function getPayPerName()
    {
        return $this->pay_per_name;
    }

    /**
     * @return mixed
     */
    public function getPledgeSum()
    {
        return $this->pledge_sum;
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
    public function getPublishedAt()
    {
        return $this->published_at;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}
