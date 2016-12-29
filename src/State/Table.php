<?php

class State_Table
{

    private $_client;
    private $_tableId;
    private $_chatLog = array();
    private $_seats = array();
    private $_header = '';
    private $_open = false;
    private $_communityCards = array();
    private $_total = null;
    private $_rakeTotal = 0;
    private $_waitingCount = 0;
    private $_playerCount = 0;
    private $_seatCount = 0;
    private $_waiting = array();
    private $_password;

    public function __construct($client, $tableId)
    {
        $this->_client = $client;
        $this->_tableId = $tableId;
    }

    public function getTableId()
    {
        return $this->_tableId;
    }

    public function __toString()
    {
        return $this->getTableId();
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

    /**
     * Return the seat at specified position
     *
     * @return State_Seat
     */
    public function findOrCreateSeat($seatNumber)
    {
        if (!isset($this->_seats[$seatNumber])) {
            $this->_seats[$seatNumber] = new State_Seat($this->getClient(), $this);
        }
        return $this->_seats[$seatNumber];
    }

    public function setPlayerData($seatNumber, $playerName, $chips, $net = 0)
    {
        $seat = $this->findOrCreateSeat($seatNumber);
        $seat->setData($playerName, $chips, $net);
        return $seat;
    }

    public function setDealer($seatNumber)
    {
        foreach ($this->getSeats() as $position => $seat) {
            if ($position == $seatNumber) $seat->setIsDealer(true);
            else $seat->setIsDealer(false);
        }
    }

    public function setHotSeat($seatNumber)
    {
        foreach ($this->getSeats() as $position => $seat) {
            if ($position == $seatNumber) $seat->setIsHotSeat(true);
            else $seat->setIsHotSeat(false);
        }
    }

    public function setTimeLeft($seatNumber, $timeLeft)
    {
        foreach ($this->getSeats() as $position => $seat) {
            if ($position == $seatNumber) $seat->setTimeLeft($timeLeft);
            else $seat->setTimeLeft(null);
        }
    }

    public function appendChatLog($username, $message)
    {
        $this->_chatLog[] = array($username, $message, time());
    }

    public function getSeats()
    {
        return $this->_seats;
    }

    public function setHeader($tableHeader)
    {
        $this->_header = $tableHeader;
    }

    public function getHeader()
    {
        return $this->_header;
    }

    public function setCommunityCard($index, $card)
    {
        $this->_communityCards[$index] = $card;
    }

    public function getCommunityCards()
    {
        return $this->_communityCards;
    }

    public function setTotal()
    {
        $this->_total = $this;
    }

    public function getTotal()
    {
        return $this->_total;
    }

    public function addPotRake($total, $value)
    {
        $this->_rakeTotal += $total;
    }

    public function getPotRakeTotal()
    {
        return $this->_rakeTotal;
    }

    public function setPlayerCards($seatPosition, $card1, $card2, $card3, $card4)
    {
        $seat = $this->findOrCreateSeat($seatPosition);
        $seat->setCards($card1, $card2, $card3, $card4);
    }


    public function addWaiting($position, $playerName)
    {
        $this->_waiting[$position] = $playerName;
    }

    public function newHand()
    {
        $this->_communityCards = array();
        foreach ($this->getSeats() as $seat) {
            $seat->setCards(null, null, null, null);
        }
    }

    public function potAward($position, $award)
    {
        $this->findOrCreateSeat($position)->addWonChips($award);
    }

    public function setRequiresPassword($requiresPassword)
    {
        $this->_password = $requiresPassword;
    }

    public function getRequiresPassword()
    {
        return $this->_password;
    }

    public function setWaitingCount($waitingCount)
    {
        $this->_waitingCount = $waitingCount;
    }

    public function getWaitingCount()
    {
        return $this->_waitingCount;
    }

    public function clearWaiting()
    {
        $this->_waiting = array();
    }

    public function getWaitingList()
    {
        return $this->_waiting;
    }

    public function setPlayerCount($playerCount)
    {
        $this->_playerCount = $playerCount;
    }

    public function getPlayerCount()
    {
        return $this->_playerCount;
    }

    public function getChatLog()
    {
        return $this->_chatLog;
    }

    public function getIsOpen()
    {
        return $this->_open;
    }

    public function setIsOpen($isOpen)
    {
        $this->_open = $isOpen;
    }

    public function setSeatCount($seatCount)
    {
        $this->_seatCount = $seatCount;
    }

    public function getSeatCount()
    {
        return $this->_seatCount;
    }

}