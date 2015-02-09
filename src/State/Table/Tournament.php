<?php

class State_Table_Tournament extends State_Table {

	private $_game;
	private $_buyin;
	private $_entryFee;
	private $_rebuy;
	private $_shootout;
	private $_shootTo;
	private $_TS;
	private $_reg;
	private $_max;
	private $_starts;
	private $_startMin;
	private $_startTime;
	private $_tables;
	private $_password;

	public function __toString() {
		return $this->getTableId() . ", Rebuy: " . $this->_rebuy;
	}

	public function setData($game, $buyin, $entryFee, $rebuy, $shootout, $shootTo, $TS, $reg, $max, $starts, $startMin, $startsTime, $tables, $password) {
		$this->_game = $game;
		$this->_buyin = $buyin;
		$this->_entryFee = $entryFee;
		$this->_rebuy = $rebuy;
		$this->_shootout = $shootout;
		$this->_shootTo = $shootTo;
		$this->_TS = $TS;
		$this->_reg = $reg;
		$this->_max = $max;
		$this->_starts = $starts;
		$this->_startMin = $startMin;
		$this->_startTime = $startsTime;
		$this->_tables = $tables;
		$this->_password = $password;
	}

}