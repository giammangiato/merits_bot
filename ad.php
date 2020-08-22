<?php

require 'config.php';
define('API_KEY',   $TOKEN);
define('DEBUG',     false);  
require 'utils.php';
require 'bitcointalk.php';

$db   = explode("\n", file_get_contents('bot_data/NICK-db.txt'));
$users = array();
foreach($db as $line) {
  if(trim($line)!=='') {
    $d = explode(':',trim($line));
    $users[strtolower($d[0])] = $d[1];
  }
}

foreach($users as $nick => $uid) {
  $msg = "Hello dear $nick, I just want to warn you that with the command /poke you can poke users. Do not abuse it.";
  sm($uid, $msg, 'Html');
  echo $nick." ".$uid."\n";
}
