<?php

class Swc {

    public $username;
    public $serverTime;
    public $depositAddress;
    public $location;
    public $title;
    public $krill;
    public $chipsTotal;
    public $chipsInPlay;
    public $chipsAvailable;
    public $sessionKey;
    public $version;

    private $_password;            // Player's password
    private $_buffer;              // Buffer holding server responses until an end character is received
    private $_packetCounter;       // Packet counter that is incremented and sent to the server on each request
    private $_ID;                  // Client ID sent from the server
    private $_pcid;                // A random hex identifier to prevent multiple logins
    private $_curlHandle;          // Curl handle to perform the encrypted SSL authentication
    private $_socket;              // Socket handle for sending and receiving commands

    // Initialize all state variables
    public function __construct($username, $password) {
        $this->username = $username;
        $this->_socket = null;
        $this->_buffer = '';
        $this->_packetCounter = 1;
        $this->_pcid = $this->_randomHex();
        $this->_registerdFunctions = array();
        $this->_password = $password;
    }

    // Exit and close connection
    public function __destroy() {
        $this->_liveOutput("[" . $this->username . "] Closing socket...", true);
        unset($this->_socket);
        $this->_liveOutput("[" . $this->username . "] Socket closed.", true);
    }

    // Send SSL Curl request with credentials and create socket connection for receiving commands
    public function connect() {
        if ($this->_socket) socket_close($this->_socket);
        $address = gethostbyname(SERVICE_URL);
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connectionSuccessful = socket_connect($this->_socket, $address, SERVICE_PORT);
        if ($connectionSuccessful) $this->_liveOutput("Connected to " . SERVICE_URL . " successfully");
        else $this->_liveOutput("[" . $this->username . "] Problem connecting to " . SERVICE_URL);
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, USER_AGENT_STR);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . SWC_CERT_FILENAME);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $this->_curlHandle = $curlHandle;
        $this->_getMeaningfulColors($curlHandle);
        if ($userData = $this->_getSession()) {
            $this->krill = $userData['krill'];
            $this->depositAddress = $userData['deposit_address'];
            $this->_sendPolicyRequest();
            $this->_sendSessionInitialization();
            $this->_sendLogin();
            $this->sendBalanceRequest();
            return true;
        }
        else return false;
    }

    // Our main loop which is called continuously after connection
    public function play() {
        if(!$this->_socket) return false;
        else return $this->_processInput();
    }

    // Request our chip and kirll balance
    public function sendBalanceRequest() {
        $this->_sendPacket('Response=Balance');
    }

    // Open a table so the server will start sending us the various table actions and state
    public function sendOpenTable($type, $tableName) {
        $this->_sendPacket('Response=GameSelected&Type=' . $type . '&Table=' . $tableName);
        $this->_sendPacket('Response=OpenTable&Seat=0&Type=' . $type . '&Table=' . $tableName);
    }

    // Close a table so the server stops sending us actions associated with it
    public function sendCloseTable($type, $tableName) {
        $this->_sendPacket('Response=CloseTable&Seat=0&Type=' . $type . '&Table=' . $tableName);
    }

    // Tell the server which game we selected so that it will send us information about it
    public function sendGameSelected($type, $tableName) {
        $this->_sendPacket('Response=GameSelected&Type=' . $type . '&Table=' . rawurlencode($tableName));
    }

    // Send chat to the lobby
    public function sendLobbyChat($text) {
        $this->_sendPacket('Response=LobbyChat&Text=' . urlencode($text));
    }

    // Register for a tourney
    public function sendTourneyRegistration($tableName) {
        $this->_sendPacket('Response=RegisterRequest&Table=' . rawurlencode($tableName));
        $this->_processInput();
        $this->_sendPacket('Response=Register&Seat=0&Type=T&Table=' . rawurlencode($tableName));
        $this->_processInput();
        return true;
    }

    // Tell the server which button we pressed
    public function sendDecision($type, $tableName, $decision) {
        $button = ucfirst(strtolower($decision['action']));
        $this->_sendPacket('Response=Button&Amount='. $decision['amount'] .'&Type='. $type .'&Button='. $button .'&Table=' . $tableName);
    }

    // Convert the numerical card to the 2 character text representation
    public function cardNumToText($cardNum) {
        $cardTable = array(
            1 => '2C', 2 => '2D', 3 => '2H', 4 => '2S', 5 => '3C', 6 => '3D', 7 => '3H', 8 => '3S',
            9 => '4C', 10 => '4D', 11 => '4H', 12 => '4S', 13 => '5C', 14 => '5D', 15 => '5H', 16 => '5S',
            17 => '6C', 18 => '6D', 19 => '6H', 20 => '6S', 21 => '7C', 22 => '7D', 23 => '7H', 24 => '7S',
            25 => '8C', 26 => '8D', 27 => '8H', 28 => '8S', 29 => '9C', 30 => '9D', 31 => '9H', 32 => '9S',
            33 => 'TC', 34 => 'TD', 35 => 'TH', 36 => 'TS', 37 => 'JC', 38 => 'JD', 39 => 'JH', 40 => 'JS',
            41 => 'QC', 42 => 'QD', 43 => 'QH', 44 => 'QS', 45 => 'KC', 46 => 'KD', 47 => 'KH', 48 => 'KS',
            49 => 'AC', 50 => 'AD', 51 => 'AH', 52 => 'AS', 53 => 'back'
        );
        if (isset($cardTable[$cardNum])) {
            return $cardTable[$cardNum];
        }
        else return false;
    }

    // Decrypt the encrypted cards sent by the server
    public function decryptCards($cards, $salt, $sessionKey) {
        $var1 = hash('sha256', $sessionKey . $salt);
        $var2 = intval(substr($var1, 0, 2), 16);
        $var3 = intval(substr($var1, 2, 2), 16);
        $var4 = intval(substr($var1, 4, 2), 16);
        $var5 = intval(substr($var1, 6, 2), 16);
        $var6 = intval($cards[0], 16) ^ $var2;
        $var7 = intval($cards[1], 16) ^ $var3;
        $var8 = intval($cards[2], 16) ^ $var4;
        $var9 = intval($cards[3], 16) ^ $var5;
        if ($var6 < 0 or $var6 > 53)  $var6 = 0;
        if ($var7 < 0 or $var7 > 53) $var7 = 0;
        if ($var8 < 0 or $var8 > 53) $var8 = 0;
        if ($var9 < 0 or $var9 > 53) $var9 = 0;

        $cardList = array();
        if ($var6 != 0)  $cardList[] = $this->cardNumToText($var6);
        if ($var7 != 0)  $cardList[] = $this->cardNumToText($var7);
        if ($var8 != 0)  $cardList[] = $this->cardNumToText($var8);
        if ($var9 != 0)  $cardList[] = $this->cardNumToText($var9);

        return $cardList;
    }



    
    
    // Convert a hex value to the string equivalent
    private function _hex2str($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

    // Generate a random hex string of a given length
    private function _randomHex($count = 8) {
        $ret = '';
        $chars = '0123456789ABCDEF';
        for ($i = 0; $i < $count; $i++) {
            $ret .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }
        return $ret;
    }

    // Output to console if LIVE_OUTPUT_ENABLED is true
    private function _liveOutput($text) {
        if (LIVE_OUTPUT_ENABLED) {
            echo "{$text}\n";
        }
    }

    // Send the command to intialize the session
    private function _sendSessionInitialization() {
        $this->_sendPacket('Response=Session&PC='. $this->_pcid .'&Version=' . $this->version);
        $response = $this->_recv();
        parse_str($response, $responseArray);
        $this->_ID = $responseArray['ID'];
    }

    // Send the command to login along with the session key
    private function _sendLogin() {
        $this->_sendPacket('Response=Login&SessionKey=' . $this->sessionKey . '&Player=' . $this->username);
    }

    // Sends the login credentials via an encrypted CURL request
    private function _getSession() {
        $fields = array(
            'UserName' => urlencode($this->username),
            'GoogleAuth' => '',
            'PassWord' => urlencode($this->_password),
            'MyAccount' => urlencode('My Account')
        );
        $response = $this->_curlPost(GET_SESSION_URL, $fields);
        $json = json_decode($response, true);
        $this->sessionKey = $json['MavensKey'];
        $this->version = $json['Version'];
        $this->serverTime = $json['ServerTime'];
        return array(
            'deposit_address' => $json['DepositAddress'],
            'krill' => $json['Krill'],
        );
    }

    // Process input
    private function _processInput() {
        $fullCommand = $this->_processBuffer();
        if (strpos($fullCommand, 'Command') !== false) {
            parse_str($fullCommand, $responseArr);
            if (isset($responseArr['Command'])) {
                $command = $responseArr['Command'];
                if ($command == 'Session') $this->_ID = $responseArr['ID'];
                else return $responseArr;
            }
        }
        return true;
    }

    // Used to buffer responses from the server. This is used because sometimes server responses are longer than a single packet
    private function _processBuffer() {
        if (strpos($this->_buffer, END_STR) !== false) {
            $lines = explode(END_STR, $this->_buffer);
            $ret = array_shift($lines);
            $this->_buffer = implode(END_STR, $lines);
            return $ret;
        }
        else {
            if ($response = $this->_recv(32768)) {
                $moddedResponse = preg_replace('/\0/', END_STR, $response);
                $this->_buffer .= $moddedResponse;
            }
            return false;
        }
    }

    // Mimic the request for the meaningful colors script from the official client
    private function _getMeaningfulColors() {
        $this->_curlGet(MEANINGFUL_COLORS_URL);
    }

    // Use the curl handle to post values to the authentication server
    private function _curlPost($url, $fields) {
        curl_setopt($this->_curlHandle,CURLOPT_POST, 1);
        curl_setopt($this->_curlHandle, CURLOPT_URL, $url);
        $fields_string = '';
        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');
        curl_setopt($this->_curlHandle,CURLOPT_POSTFIELDS, $fields_string);
        return curl_exec($this->_curlHandle);
    }

    // Use curl handle to GET a page
    private function _curlGet($url) {
        curl_setopt($this->_curlHandle, CURLOPT_POST, 0);
        curl_setopt($this->_curlHandle, CURLOPT_URL, $url);
        return curl_exec($this->_curlHandle);
    }

    // Send a packet and append ID and packet number
    private function _sendPacket($data) {
        if ($this->_ID) $data .= '&ID=' . $this->_ID;
        $data .= '&PNum=' . $this->_packetCounter;
        $data .= $this->_hex2str('00');
        $this->_sendRawPacket($data);
        $this->_packetCounter++;
    }

    // send a packet without appending anything
    private function _sendRawPacket($data) {
        socket_write($this->_socket, $data, strlen($data));
    }

    // Receive data on the socket
    private function _recv() {
        return socket_read($this->_socket, 32768);
    }

    // Mimic the policy request sent by the official client
    private function _sendPolicyRequest() {
        if ($this->_socket) {
            $this->_sendRawPacket("<policy-file-request/>" . $this->_hex2str('00'));;
            $this->_recv();
        }
        else die('no connection');
    }

}


?>