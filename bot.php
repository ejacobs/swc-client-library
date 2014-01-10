<?php

include('config.php');
include('Swc.php');

$client = new Swc(SWC_USERNAME, SWC_PASSWORD);

$client->connect();

while ($response = $client->play()) {
    if (is_array($response)) {

        // See README.md for a full list of responses
        $command = $response['Command'];

        if ($command == 'LobbyChat') {
            echo "{$response['Player']}: {$response['Text']}\n";
            if ($response['Text'] == 'are you a bot?') {
                $client->sendLobbyChat('Yes, I am a Bot!');
            }
        }

    }
}




?>