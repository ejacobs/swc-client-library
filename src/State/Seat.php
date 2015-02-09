<?php

class State_Seat  {

	private $_isDealer = false;
	private $_isHotSeat = false;
	private $_player;
	private $_chips;
	private $_net;
	private $_timeLeft = null;
	private $_cards = array();
	private $_wonChips = 0;
	private $_spentChips = 0;
	private $_currentBet = 0;

	private $_client;
	private $_table;


	public function __construct($client, $table) {
		$this->_client = $client;
		$this->_table = $table;
	}

	/**
	 * Return the table the seat belongs to
	 *
	 * @return State_Table
	 */
	public function getTable() {
		return $this->_table;
	}

	/**
	 * Return the SwC client
	 *
	 * @return Client
	 */
	public function getClient() {
		return $this->_client;
	}

	public function setData($playerName, $chips, $net) {
		$player = $this->getClient()->getState()->findOrCreatePlayer($playerName);
		$this->_player = $player;
		$this->_chips = $chips;
		$this->_net = $net;
	}

	public function toJson() {
		if ($player = $this->getPlayer()) $playerName = $player->getName();
		else $playerName = null;
		return json_encode(array(
			'player' => $playerName,
			'chips' => $this->_chips,
			'net' => $this->_net,
			'is_dealer' => $this->getIsDealer()
		));
	}

	public function setIsDealer($isDealer) {
		$this->_isDealer = $isDealer;
	}

	public function getIsDealer() {
		return $this->_isDealer;
	}

	public function setIsHotSeat($isHotSeat) {
		$this->_isHotSeat = $isHotSeat;
	}

	public function getIsHotSeat() {
		return $this->_isHotSeat;
	}

	public function setTimeLeft($time) {
		$this->_timeLeft = $time;
	}

	public function getTimeLeft() {
		return $this->_timeLeft;
	}

	public function setCards($card1, $card2, $card3, $card4) {
		$this->_cards = array($card1, $card2, $card3, $card4);
	}

	public function getCards() {
		return $this->_cards;
	}

	public function getWonChips() {
		return $this->_wonChips;
	}

	public function addWonChips($award) {
		$this->_wonChips += $award;
	}

	/**
	 * Return the player seated at this seat
	 *
	 * @return State_Player
	 */
	public function getPlayer() {
		return $this->_player;
	}

	public function getPlayerName() {
		if ($player = $this->getPlayer()) {
			return $player->getName();
		}
		else return null;
	}

	public function getCurrentBet() {
		return $this->_currentBet;
	}

	public function collectBet() {
		$this->_spentChips += $this->_currentBet;
		$this->_currentBet = 0;
	}

	public function addBet($amount) {
		$this->_currentBet += $amount;
	}




}