<?php

class State_Player
{

    private $_client;
    private $_name;
    private $_realName;
    private $_location;
    private $_time;

    public function __construct($client, $name)
    {
        $this->_client = $client;
        $this->_name = $name;
    }

    /**
     * Return the SwC client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    public function __toString()
    {
        return $this->_name . " " . $this->_location;
    }

    public function setData($realName, $location, $time)
    {
        $this->_realName = $realName;
        $this->_location = $location;
        $this->_time = $time;
    }

    public function getName()
    {
        return $this->_name;
    }

}