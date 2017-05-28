<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/28/17
 * Time: 11:55 AM
 */

namespace daemionfox\Patreon\Components;


abstract class AbstractComponent
{

    /**
     * @param array $data
     * @return $this
     */
    public function load(array $data)
    {
        foreach ($data as $key => $value)
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $class = get_class($this);
        $vars  = get_class_vars($class);

        $output = array();
        foreach ($vars as $k => $v) {
            if (!empty($this->$k)) {
                $output[$k] = $this->$k;
            }
        }
        return $output;
    }

    /**
     * @throws \Exception
     * @return array
     */
    abstract function toRSS();

}
