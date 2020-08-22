<?php
$EMOJI_UP = '🔼';

include 'utils_sqlite.php';

class User {
  private $TUID;
  private $BUID;
  private $NICK;
  private $DB;
  private $NDB;

  public function __construct(String $telegramuid, UidDB $db, NickDB $ndb) {
    $this->TUID = $telegramuid;
    $this->DB   = $db;
    $this->NDB  = $ndb;
  }
  public function __setUid($btcuid) {
    $this->BUID = $btcuid;
    if(
      $this->DB->__addUser($this->TUID, $btcuid)
    ) {
      return true;
    }
    return false;
  }
  public function __getBUid() {
    return $this->DB->__getUser($this->TUID);
  }

  public function __setNick($btcuid) {
    $this->BUID = $btcuid;
    if(
      $this->NDB->__addUser($this->TUID, $btcuid)
    ) {
      return true;
    }
    return false;
  }
  public function __getBNick() {
    return $this->NDB->__getUser($this->TUID);
  }


  public function _search($nick) {
    return $this->NDB->__getUserFromNick($nick);
  }
  public function _searchNick($uid) {
    return $this->NDB->__getNickFromUid($uid);
  }
}

function makeHTTPRequest($method,$datas=[]){
  $url = "https://api.telegram.org/bot".API_KEY."/".$method;
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
  $res = curl_exec($ch);
  if(curl_error($ch)) {
    var_dump(curl_error($ch));
  } else {
    return json_decode($res);
  }
}

function sTyping($chatid, $action='typing') {
  makeHTTPRequest('sendChatAction',[
    'chat_id'   => $chatid,
    'action'    => 'typing'
  ]);
}

function sm($chatid, $msg, $format='Markdown', $action=null) {
  if($action) {
    sTyping($chatid);
  }
  makeHTTPRequest('sendMessage',[
    'chat_id'   => $chatid,
    'text'      => $msg,
    'parse_mode'=> $format,
  ]);
}

function logThis($id, $msg) {
  $file   = './'. date('Ymd-').'bot.log';
  file_put_contents($file, date('Y-m-d H:i').' | '.$id.' | '.$msg."\n", FILE_APPEND);
}


