<?php

include('vendor/autoload.php');
include('config.php');

$loop = React\EventLoop\Factory::create();
$client = new Client('<your_username>', '<your_password>', $loop);
$client->connect();


$client->on('lobbychat', function($player, $text, $time) use ($client) {
	echo "{$time} {$player}: {$text}\n";
});

$client->on('board', function(State_Table $table, $cards) use ($client) {
	echo $table->getTableId() . "\n";
	print_r($cards);
});


$loop->addPeriodicTimer(5, function() use ($client) {
	$client->debug();
});


// Start the web interface
$web = new Web($loop, $client);

$loop->run();
