<?php

class State_Table_Ring extends State_Table {

	private $_seats;
	private $_stakesLo;
	private $_stakesHi;
	private $_buyinMin;
	private $_buyinMax;

	public function __toString() {
		return json_encode(array(
			'seats' => $this->_seats,
			'stakes_lo' => $this->_stakesLo,
			'stakes_hi' => $this->_stakesHi,
			'buyin_min' => $this->_buyinMin,
			'buyin_max' => $this->_buyinMax,
			'players' => $this->_players,
			'waiting' => $this->getWaitingCount(),
			'password' => $this->getRequiresPassword()
		));
	}

	public function setData($seats, $stakesLo, $stakesHi, $buyinMin, $buyinMax, $playerCount, $waitingCount) {
		$this->_seats = $seats;
		$this->_stakesLo = $stakesLo;
		$this->_stakesHi = $stakesHi;
		$this->_buyinMin = $buyinMin;
		$this->_buyinMax = $buyinMax;
		$this->setPlayerCount($playerCount);
		$this->setWaitingCount($waitingCount);
	}

	public function toJson() {

		$seatsJson = array();
		foreach ($this->getSeats() as $position => $seat) {
			$seatsJson[$position] = $seat->toJson();
		}

		return json_encode(array(
			'cards' => $this->getCommunityCards(),
			'seats' => $seatsJson,
			'stakes_lo' => $this->_stakesLo,
			'stakes_hi' => $this->_stakesHi,
			'buyin_min' => $this->_buyinMin,
			'buyin_max' => $this->_buyinMax,
			'players' => $this->getSeats(),
			'waiting' => $this->getWaitingCount(),
			'password' => $this->getRequiresPassword(),
			'chat' => $this->getChatLog()
		));
	}

	// Open a table so the server will start sending us the various table actions and state
	public function open() {
		$this->getClient()->sendOpenTable('R', $this->getTableId());
	}


}