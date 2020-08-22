<?php

require 'config.php';
define('API_KEY',   $TOKEN);
define('DEBUG',     false);  
require 'utils.php';
define('C2', 'CHATIDLOG');

$data   = file_get_contents("php://input");
$update = json_decode($data, true);

if(DEBUG) {
  logThis(999, json_encode($update));
}

$new      = $update['message']['new_chat_members'];
$left     = $update['message']['left_chat_member'];
$msg      = $update['message']['text'];
$chat_id  = $update['message']['from']['id'];

$btcadd = 'bc1q92n28m7eve8ncdq9n2ltu8vmrfuzfuy6kltjf5';
$donate   = "Donation BTC `$btcadd` Thank you\n\n";

$USER = new User($chat_id, new UidDB(), new NickDB());

if($msg == "/start" || $msg == "/help"){
  $help = "/uid <number> | set your bitcointalk uid uid\n/myuid | your setted bitcointalk uid\n/nick <nick> | set nick for mention notifier\n".
  "/poke <nick> | poke user of btctalk";
  sm($chat_id, $donate.$help, 'Markdown', true);
}

if(preg_match('/^\/uid(\s)+([0-9])+/i', $msg)) {   
  $uid = trim(str_replace('/uid','',strtolower($msg)));

  if($USER->__setUid($uid)) {
    $msg = "UID `".$uid."` => `Registered`";
  } else {
    $msg = "Change from => `".$USER->__getBUid()."` to `".$uid."`";
  }
  sm($chat_id, $donate.$msg, 'Markdown', true);
  sm(C2, "User $chat_id UID ".$uid, 'Markdown', true);
}

if(preg_match('/^\/nick(\s)+/i', $msg)) {   
  $nick = trim(str_replace('/nick','',strtolower($msg)));

  if($USER->__setNick($nick)) {
    $msg = "NICK `".$nick."` => `Registered`";
  } else {
    $msg = "Change from => `".$USER->__getBNick()."` to `".$nick."`";
  }
  sm($chat_id, $donate.$msg, 'Markdown', true);
  sm(C2, "User $chat_id NICK ".$nick, 'Markdown', true);
}

if($msg == "/myuid"){

  $msg = "Your Btctalk UID => `".$USER->__getBUid()."`\n".
  "Your BtckTalk NICK => `".$USER->__getBNick()."`";
  sm($chat_id, $donate.$msg, 'Markdown', true);
  sm(C2, "User $chat_id MYUID", 'Markdown', true);
}

if(preg_match('/^\/poke(\s)+/i', $msg)) {   
  $nick = trim(str_replace('/poke','',strtolower($msg)));
  $uid      = $USER->_search($nick);
  $from     = $USER->_searchNick($chat_id);

  $msg      = "poke from *$from* ";
  if($uid)
    $poke_msg = "poke to *$nick*";
  else
    $poke_msg = "oops! *$nick* is not an user of TelegramBot";

  sm($chat_id, $donate.$poke_msg, 'Markdown', true);
  sm($uid, $msg, 'Markdown', true);
}
