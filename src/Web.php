<?php

use Evenement\EventEmitter;

class Web extends EventEmitter {

	public function __construct($loop, $client) {
		$app = function ($request, $response) use ($client) {


			$parts = explode('/', trim($request->getPath(), '/'));
			switch ($parts[0]) {
				case 'lobbychat':
					$output = json_encode($client->getState()->getLobbyChat());
					break;
				case 'ringgames':

					if (count($parts) > 1) {
						if (($parts[1] == 'join') && (count($parts) > 2)) {
							$response->writeHead(200, array('Content-Type' => 'application/json'));
							if ($ringTable = $client->getState()->getRingTable($parts[2])) {
								$ringTable->open();
								$output = json_encode('joining');
							}
							else $output = $parts[1];
						}
						if (($parts[1] == 'view') && (count($parts) > 2)) {
							$response->writeHead(200, array('Content-Type' => 'application/json'));
							if ($ringTable = $client->getState()->getRingTable($parts[2])) {
								$output = $ringTable->toJson();
							}
						}
					}
					else {
						$response->writeHead(200, array('Content-Type' => 'text/html'));
						$output = "<table border=\"1\">\n";
						foreach ($client->getState()->getRingGames() as $ringId => $ringGame) {
							$output .= '<tr>';
							$output .= '<td>' . urldecode($ringGame->getTableId()) . "</td><td>" . $ringGame->getPlayerCount() . "</td>";
							$output .= '<td>' . "<a href=\"/ringgames/view/". sha1($ringGame->getTableId()) ."\">View</a></td>";
							$output .= '<td>' . "<a href=\"/ringgames/join/". sha1($ringGame->getTableId()) ."\">Join</a></td>";
							$output .= "<td>\n";
							if ($ringGame->getIsOpen()) $output .= "<strong>Open</strong>\t";
							$output .= "</td>\n";
							$output .= "</tr>\n";
						}
						$output .= "</table>\n";
					}
					break;
				default:
					$response->writeHead(404, array('Content-Type' => 'text/html'));
					$output = 'Not found';
					break;
			}

			$response->end($output);
		};

		$socket = new React\Socket\Server($loop);
		$http = new React\Http\Server($socket, $loop);
		$http->on('request', $app);
		echo "Server running at http://127.0.0.1:1337\n";
		$socket->listen(1337, '0.0.0.0');
	}

}
