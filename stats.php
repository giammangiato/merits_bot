<?php

require 'config.php';
define('API_KEY',   $TOKEN);
define('DEBUG',     false);  
require 'utils.php';
require 'bitcointalk.php';

$USERS = new UidDB();
$count = count($USERS->__getList());

$sql = 'select count(*) as count from users';
echo "[STASTICS]\n";
echo "[+] Users |> ".($count)." \n";
