<?php

class NickDBv1 {
  private $path = './bot_data/';
  private $file_uid = 'NICK-db.txt';
  private $DB;

  public function __construct() {
    $this->DB = file($this->path.$this->file_uid);
  }
  public function __getUserFromNick($nick) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[0]) == trim(strtolower($nick)) ) {
        return $data[1];
      }
    }
  }
  public function __getNickFromUid($uid) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim(strtolower($uid)) ) {
        return $data[0];
      }
    }
  }
  public function __updateUser($tuid, $buid) {
    $new = array();
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        $new[] = $buid.':'.$tuid;
      } else {
        $new[] = $line;
      }
    }
    file_put_contents($this->path.$this->file_uid, implode("\n", $new));
  }
  public function __addUser($tuid, $buid) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        $this->__updateUser($tuid, $buid);
        return false;
      }
    }
    file_put_contents($this->path.$this->file_uid, $buid.':'.$tuid.PHP_EOL, FILE_APPEND);
    return true;
  }
  public function __getUser($tuid) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        return trim($data[0]);
      }
    }
  }
  public function __getList() {
    $out = array();
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[0])!=='')
        $out[$data[0]] = $data[1];
    }
    return $out;
  }
}

class UidDBv1 {
  private $path = './bot_data/';
  private $file_uid = 'UID-db.txt';
  private $DB;

  public function __construct() {
    $this->DB = file($this->path.$this->file_uid);
  }
  public function __updateUser($tuid, $buid) {
    $new = array();
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        $new[] = $buid.':'.$tuid;
      } else {
        $new[] = $line;
      }
    }
    file_put_contents($this->path.$this->file_uid, implode("\n", $new));
  }
  public function __addUser($tuid, $buid) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        $this->__updateUser($tuid, $buid);
        return false;
      }
    }
    file_put_contents($this->path.$this->file_uid, $buid.':'.$tuid.PHP_EOL, FILE_APPEND);
    return true;
  }
  public function __getUser($tuid) {
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[1]) == trim($tuid)) {
        return trim($data[0]);
      }
    }
  }
  public function __getList() {
    $out = array();
    foreach($this->DB as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[0])!=='')
        $out[$data[0]] = $data[1];
    }
    return $out;
  }
}

class NickDB {
  private $DB;

  public function __construct() {
    $DB = new PDO('sqlite:/home/d/meritbot.db');
    $this->DB = $DB; 
  }

  private function __returnID($tuid) {
    $rid = 0;
    $id  = 'SELECT id FROM users WHERE telegramuid="'.$tuid.'"';
    $result = $this->DB->query($id);
    if($result) {
      echo "SEARCH RID - ";
      foreach($result as $row) {
        $rid = $row['id'];
      }
    }
    return $rid;
  }

  private function __returnWID($rid) {
    $ridd = 0;
    $id = 'SELECT id FROM watching WHERE userid="'.$rid.'"';
    $result = $this->DB->query($id);
    foreach($result as $row) {
      $ridd = $row['id'];
    }
    return $ridd;
  }

  public function __getUserFromNick($bnick) {
    $sql = 'SELECT bnick,buid,telegramuid FROM watching JOIN users WHERE bnick="'.$bnick.'" AND users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      return $row['telegramuid'];
    }
  }

  public function __getNickFromUid($uid) {
    $sql = 'SELECT bnick,buid,telegramuid FROM watching JOIN users WHERE telegramuid="'.$uid.'" AND users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      return $row['bnick'];
    }
  }

  public function __updateUser($tuid, $bnick) {
    $sql = 'UPDATE "main"."watching" SET "bnick"="'.$bnick.'" WHERE userid=(SELECT userid FROM users WHERE telegramuid="'.$tuid.'")';
    $db->query($sql);
  }

  public function __addUser($tuid, $bnick) {
    $rid = $this->__returnID($tuid);

    echo "REAL ID $rid - ";
    if($rid===0) {
      echo "TRY TO INSERT\n";
      $sql = 'INSERT INTO "main"."users"("telegramuid") VALUES ("'.$tuid.'");';
      $this->DB->query($sql);
      $rid = $this->__returnID($tuid);
      echo "REAL ID $rid - ";
    }

    $wid = $this->__returnWID($rid);
    echo "REAL WID $wid - ";

    if($wid===0) {
      echo "TRY TO INSERT - ";
      $sql = 'INSERT INTO "main"."watching"("bnick","buid","userid") VALUES ("'.$bnick.'", NULL, "'.$rid.'");';
      $this->DB->query($sql);
      return true;
    } else {
      echo "TRY TO UPDATE - ";
      $sql = 'UPDATE "main"."watching" SET "bnick"="'.$bnick.'" WHERE "id"="'.$wid.'";';
      echo $sql;
      $this->DB->query($sql);
      return false;
    }
    
  }

  public function __getUser($tuid) {
    $sql = 'SELECT bnick,buid,telegramuid FROM watching JOIN users WHERE telegramuid="'.$tuid.'" AND users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      return $row['bnick'];
    }
  }

  public function __getList() {
    $out = array();
    $sql = 'SELECT bnick,telegramuid FROM watching JOIN users WHERE users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      $out[$row['bnick']] =  $row['telegramuid'];
    }
    return $out;
  }
}

class UidDB {
  private $DB;

  public function __construct() {
    $DB = new PDO('sqlite:/home/d/meritbot.db');
    $this->DB= $DB;
  }

  private function __returnID($tuid) {
    $rid = 0;
    $sid = 'SELECT id FROM users WHERE telegramuid="'.$tuid.'"';
    $result = $this->DB->query($sid);
    if($result) {
      echo "SEARCH RID - ";
      foreach($result as $row) {
        $rid = $row['id'];
      }
    }
    return $rid;
  }

  private function __returnWID($rid) {
    $ridd = 0;
    $id = 'SELECT id FROM watching WHERE userid="'.$rid.'"';
    $result = $this->DB->query($id);
    foreach($result as $row) {
      $ridd = $row['id'];
    }
    return $ridd;
  }

  public function __updateUser($tuid, $buid) {
    $sql = 'UPDATE "main"."watching" SET "buid"="'.$buid.'" WHERE userid=(SELECT userid FROM users WHERE telegramuid="'.$tuid.'")';
    $db->query($sql);
  }

  public function __addUser($tuid, $buid) {
    $rid = $this->__returnID($tuid);

    if($rid===0) {
      $sql = 'INSERT "main"."users"("telegramuid") VALUES ("'.$tuid.'");';
      $this->DB->query($sql);
      $rid = $this->__returnID($tuid);
    }

    $wid = $this->__returnWID($rid);

    if($wid===0) {
      echo 'TRY TO INSERT - ';
      $sql = 'INSERT INTO "main"."watching"("bnick","buid","userid") VALUES (NULL, "'.$buid.'", "'.$rid.'");';
      $this->DB->query($sql);
      return true;
    } else {
      echo 'TRY TO UPDATE - ';
      $sql = 'UPDATE "main"."watching" SET "buid"="'.$buid.'" WHERE "id"="'.$wid.'";';
      $this->DB->query($sql);
      return false;
    }
    
  }

  public function __getUser($tuid) {
    $sql = 'SELECT bnick,buid,telegramuid FROM watching JOIN users WHERE telegramuid="'.$tuid.'" AND users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      return $row['buid'];
    }
  }

  public function __getList() {
    $out = array();
    $sql = 'SELECT bnick,buid,telegramuid FROM watching JOIN users WHERE users.id=watching.userid';
    $result = $this->DB->query($sql);
    foreach($result as $row) {
      $out[$row['buid']] =  $row['telegramuid'];
    }
    return $out;
  }
}

/*
require 'config.php';
$uiddbv1 = new UidDBv1();
$UDB = new UidDB();
foreach($uiddbv1->__getList() as $uid => $id) {
  if(trim($id)!=='')
    
    $result = $UDB->__addUser(trim($id), trim($uid));
    if($result) {
      echo "+ ".trim($id)." ".trim($uid)."\n";
    } else {
      echo "~ ".trim($id)." ".trim($uid)."\n";
    }
}

$nickdbv1 = new NickDBv1();
$NDB = new NickDB();
foreach($nickdbv1->__getList() as $nick => $id) {
  if(trim($id)!=='')

    $result = $NDB->__addUser(trim($id), trim($nick));
    if($result) {
      echo "+ ".trim($id)." ".trim($nick)."\n";
    } else {
      echo "~ ".trim($id)." ".trim($nick)."\n";
    }
}
*/
