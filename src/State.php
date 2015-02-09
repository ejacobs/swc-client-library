<?php


class State {

	private $_client;

	private $_name;
	private $_location;
	private $_realName;
	private $_email;
	private $_status;

	private $_krill;
	private $_depositAddress;
	private $_chipsTotal;
	private $_chipsInPlay;
	private $_chipsAvailable;
	private $_lobbyChatLog = array();
	private $_ringGames = array();
	private $_tournaments = array();
	private $_players = array();

	public function __construct(Client $client) {
		$this->_client = $client;
	}

	/**
	 * Return the SwC client
	 *
	 * @return Client
	 */
	public function getClient() {
		return $this->_client;
	}

	public function update($command, $response) {
		switch ($command) {
			case 'login':
				$this->_name = $response['Player'];
				$this->_location = $response['Location'];
				$this->_realName = $response['RealName'];
				$this->_email = $response['Email'];
				$this->_status = $response['Status'];
				break;
			case 'lobbychat':
				$line = array($response['Player'], urldecode($response['Text']), time());
				$this->_lobbyChatLog[] = $line;
				$this->getClient()->emit('lobbychat', $line);
				break;
			case 'ringgamelobby':
				if (isset($response['Count'])) {
					$count = $response['Count'];
					for ($x=1; $x<=$count; $x++) {
						$id = $response["ID{$x}"];
						$ringTable = $this->_getOrCreateRingTable($id);
						$ringTable->setData(
							$response["Seats{$x}"],
							$response["StakesLo{$x}"],
							$response["StakesHi{$x}"],
							$response["BuyinMin{$x}"],
							$response["BuyinMax{$x}"],
							$response["Players{$x}"],
							$response["Waiting{$x}"]
						);
						$ringTable->setRequiresPassword($response["Password{$x}"]);
					}
				}
				break;
			case 'tournamentlobby':
				if (isset($response['Count'])) {
					$count = $response['Count'];
					for ($x=1; $x<=$count; $x++) {
						$id = sha1($response["ID{$x}"]);
						$tournament = $this->_getOrCreateTournament($id);
						$tournament->setData(
							$response["Game{$x}"],
							$response["Buyin{$x}"],
							$response["EntryFee{$x}"],
							$response["Rebuy{$x}"],
							$response["Shootout{$x}"],
							$response["ShootTo{$x}"],
							$response["TS{$x}"],
							$response["Reg{$x}"],
							$response["Max{$x}"],
							$response["Starts{$x}"],
							$response["StartMin{$x}"],
							$response["StartTime{$x}"],
							$response["Tables{$x}"],
							$response["Password{$x}"]
						);
					}
				}
				break;
			case 'logins':
				if (isset($response['Total'])) {
					$total = $response['Total'];
					for ($x=1; $x<=$total; $x++) {
						if (isset($response["LI{$x}"])) {
							$playerData = explode('|', $response["LI{$x}"]);
							list($playerId, $name, $location, $time) = $playerData;
							$player = $this->findOrCreatePlayer($playerId);
							$player->setData($player, $name, $location, $time);
							//echo 'Player ' . $player . "\n";
						}
					}
				}
				break;
			case 'balance':
				$this->_chipsAvailable = $response['Available'];
				$this->_chipsInPlay = $response['InPlay'];
				$this->_chipsTotal = $response['Total'];
				break;
			case 'chat':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->appendChatLog($response['Player'], $response['Text']);
				break;
			case 'history':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->appendChatLog('Dealer', $response['Hand']);
				$ringTable->appendChatLog('Dealer', $response['Text']);
				break;
			case 'tablemessage':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->appendChatLog('Dealer', $response['Text']);
				break;
			case 'tableinfo':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				for ($x = 1; $x < $response['Lines']; $x++) {
					$ringTable->appendChatLog('Dealer', $response["Line{$x}"]);
				}
				break;
			case 'opentable':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setIsOpen(true);
				$this->getClient()->emit('opentable', array($ringTable));
				break;
			case 'playerinfo':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				for ($seat = 1; $seat <= $response['Count']; $seat++) {
					$ringTable->setPlayerData($seat, $response["Player{$seat}"], $response["Chips{$seat}"], $response["Net{$seat}"]);
				}
				break;
			case 'tableheader':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setHeader($response['Text']);
				break;
			case 'dealer':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setDealer($response['Dealer']);
				break;
			case 'hotseat':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setHotSeat($response['Seat']);
				break;
			case 'timeleft':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setTimeLeft($response['Seat'], $response['Time']);
				break;
			case 'flop':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setCommunityCard(0, $this->getClient()->cardNumToText($response['Board1']));
				$ringTable->setCommunityCard(1, $this->getClient()->cardNumToText($response['Board2']));
				$ringTable->setCommunityCard(2, $this->getClient()->cardNumToText($response['Board3']));
				$this->getClient()->emit('board', array($ringTable, $ringTable->getCommunityCards()));
				break;
			case 'turn':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setCommunityCard(3, $this->getClient()->cardNumToText($response['Board4']));
				$this->getClient()->emit('board', array($ringTable, $ringTable->getCommunityCards()));
				break;
			case 'river':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setCommunityCard(4, $this->getClient()->cardNumToText($response['Board5']));
				$this->getClient()->emit('board', array($ringTable, $ringTable->getCommunityCards()));
				break;
			case 'total':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setTotal($response['Total']);
				break;
			case 'potrake':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->addPotRake($response['Value'], $response['Total']);
				break;
			case 'cards':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setPlayerCards(
					$response['Seat'],
					$this->getClient()->cardNumToText($response['Card1']),
					$this->getClient()->cardNumToText($response['Card2']),
					$this->getClient()->cardNumToText($response['Card3']),
					$this->getClient()->cardNumToText($response['Card4'])
				);
				break;
			case 'waiting':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->clearWaiting();
				for ($x = 1; $x <= $response['Count']; $x++) {
					$ringTable->addWaiting($x, $response["Wait{$x}"]);
				}
				break;
			case 'deal':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->clearWaiting();
				$ringTable->newHand();
				break;
			case 'potaward':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				for ($x = 1; $x <= 9; $x++) {
					if (isset($response["Seat{$x}"])) {
						$ringTable->potAward($x, $response["Seat{$x}"]);
					}
				}
				break;
			case 'actionchips':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$seat = $ringTable->findOrCreateSeat($response['Seat']);
				$ringTable->appendChatLog($seat->getPlayerName(), $response['Action1'] . ' ' . $response['Action2']);
				break;
			case 'bet':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$seat = $ringTable->findOrCreateSeat($response['Seat']);
				$seat->addBet($response['Bet']);
				break;
			case 'betcollection':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				foreach ($ringTable->getSeats() as $seat) {
					/* @var $seat State_Seat */
					$seat->collectBet();
				}
				break;
			case 'table':
				$ringTable = $this->_getOrCreateRingTable($response['Table']);
				$ringTable->setRequiresPassword($response['Password']);
				$ringTable->setSeatCount($response['Seats']);
				$ringTable->setDealer($response['Dealer']);
				$ringTable->setHotSeat($response['Turn']);

				for ($position = 1; $position <= $response['Total']; $position++) {
					if (isset($response["Player{$position}"])) {
						$ringTable->setPlayerData(
							$position,
							$response["Player{$position}"],
							$response["Chips{$position}"]
						);
						$ringTable->setPlayerCards(
							$position,
							$this->getClient()->cardNumToText($response["Card1{$position}"]),
							$this->getClient()->cardNumToText($response["Card2{$position}"]),
							$this->getClient()->cardNumToText($response["Card3{$position}"]),
							$this->getClient()->cardNumToText($response["Card4{$position}"])
						);
						$seat = $ringTable->findOrCreateSeat($position);
						$seat->addBet($response["Bet{$position}"]);
					}
					// Still have: Gender, Custom, Level, Avatar, AvatarCrc, Title, Time
				}

				break;

			default:
				print_r($response);
				echo $command . "\n";
				break;

		}
	}

	public function setDepositAddress($depositAddress) {
		$this->_depositAddress = $depositAddress;
	}

	public function getDepositAddress() {
		return $this->_depositAddress;
	}

	public function setKrill($krill) {
		$this->_krill = $krill;
	}

	public function getKrill() {
		return $this->_krill;
	}

	public function getChipsTotal() {
		return $this->_chipsTotal;
	}

	public function getChipsInPlay() {
		return $this->_chipsInPlay;
	}

	public function getChipsAvailable() {
		return $this->_chipsAvailable;
	}

	public function getLobbyChat() {
		return $this->_lobbyChatLog;
	}

	public function getRingGames() {
		return $this->_ringGames;
	}

	private function _getOrCreateRingTable($tableId) {
		$encoded = sha1($tableId);
		if ($ringTable = $this->getRingTable($encoded)) return $ringTable;
		return $this->_ringGames[$encoded] = new State_Table_Ring($this->getClient(), $tableId);
	}

	public function getRingTable($tableId) {
		if (isset($this->_ringGames[$tableId])) return $this->_ringGames[$tableId];
		return null;
	}

	private function _getOrCreateTournament($tableId) {
		$encoded = sha1($tableId);
		if ($tournament = $this->getTournament($encoded)) return $tournament;
		else return $this->_tournaments[$encoded] = new State_Table_Tournament($this->getClient(), $tableId);
	}

	public function getTournament($tableId) {
		if (isset($this->_tournaments[$tableId])) return $this->_tournaments[$tableId];
		return null;
	}

	public function findOrCreatePlayer($playerId) {
		if ($player = $this->getPlayer($playerId)) return $player;
		else return $this->_players[$playerId] = new State_Player($this->getClient(), $playerId);
	}

	public function getPlayer($playerId) {
		if (isset($this->_players[$playerId])) return $this->_players[$playerId];
		return null;
	}


}