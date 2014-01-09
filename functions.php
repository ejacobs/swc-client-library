<?php


    function cardNumToText($cardNum) {
        $cardTable = array(
            1 => '2C',
            2 => '2D',
            3 => '2H',
            4 => '2S',
            5 => '3C',
            6 => '3D',
            7 => '3H',
            8 => '3S',
            9 => '4C',
            10 => '4D',
            11 => '4H',
            12 => '4S',
            13 => '5C',
            14 => '5D',
            15 => '5H',
            16 => '5S',
            17 => '6C',
            18 => '6D',
            19 => '6H',
            20 => '6S',
            21 => '7C',
            22 => '7D',
            23 => '7H',
            24 => '7S',
            25 => '8C',
            26 => '8D',
            27 => '8H',
            28 => '8S',
            29 => '9C',
            30 => '9D',
            31 => '9H',
            32 => '9S',
            33 => 'TC',
            34 => 'TD',
            35 => 'TH',
            36 => 'TS',
            37 => 'JC',
            38 => 'JD',
            39 => 'JH',
            40 => 'JS',
            41 => 'QC',
            42 => 'QD',
            43 => 'QH',
            44 => 'QS',
            45 => 'KC',
            46 => 'KD',
            47 => 'KH',
            48 => 'KS',
            49 => 'AC',
            50 => 'AD',
            51 => 'AH',
            52 => 'AS',
            53 => 'back'
        );
        if (isset($cardTable[$cardNum])) {
            return $cardTable[$cardNum];
        }
        else return false;
    }


    function decryptCards($cards, $salt, $sessionKey) {
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
        if ($var6 != 0)  $cardList[] = cardNumToText($var6);
        if ($var7 != 0)  $cardList[] = cardNumToText($var7);
        if ($var8 != 0)  $cardList[] = cardNumToText($var8);
        if ($var9 != 0)  $cardList[] = cardNumToText($var9);

        return $cardList;
    }

    function hex2str($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

    function randomHex($count = 8) {
        $ret = '';
        $chars = '0123456789ABCDEF';
        for ($i = 0; $i < $count; $i++) {
            $ret .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }
        return $ret;
    }

    function liveOutput($text) {
        if (LIVE_OUTPUT_ENABLED) {
            echo "{$text}\n";
        }
    }

?>