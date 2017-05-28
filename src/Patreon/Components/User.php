<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/28/17
 * Time: 11:53 AM
 */

namespace daemionfox\Patreon\Components;


class User extends AbstractComponent
{

    protected $full_name;
    protected $image_url;
    protected $url;
    protected $id;

    /**
     * @throws \Exception
     */
    public function toRSS()
    {
        throw new \Exception("Not available");
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->full_name;
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}
