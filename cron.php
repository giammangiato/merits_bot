<?php

require 'config.php';
define('API_KEY',   $TOKEN);
define('DEBUG',     false);  
require 'utils.php';
require 'bitcointalk.php';
require 'logine.php';


class CronUid {
  private $DB;
  private $USERS;

  private $path = './bot_data/';
  private $file_merits = 'MERITS-db.txt';
  private $MERITS;

  public function __construct($db) {
    $this->DB     = $db;
    $this->USERS  = $db->__getList();
    $this->MERITS = file($this->path.$this->file_merits);
  }
  public function __process() {
    sleep(10);
    $bitcointalk = new Bitcointalk($config['login']['link'], $config['login']['username'], $config['login']['password'], $config['login']['captcha-code']);
    $bitcointalk->connect();

    foreach($this->USERS as $buid => $tuid) {
      sleep(10);
      $data = activityFromForum($buid);
      if($buid>0 && $buid!=null) {
        echo '# '.$buid.' => '.$data['merit']." ";
        if( (int)$data['merit']>0 && $this->__compareMerit($buid, $data['merit'])) {
          echo ' ok '.PHP_EOL;

          sleep(10);
          $l = $bitcointalk->getLastMerits($buid);

          sm($tuid, 
            '(UP) MERITS '.$data['merit']." your merit page https://bitcointalk.org/index.php?action=merit;u=".$buid."\n".
            "You received `".$l['merit']."` merit(s) from `".$l['from']."` for `".$l['thread']."`"
          );
        } 
        else {
          echo ' ko '.PHP_EOL;
        }
      }
    }
  }
  private function __compareMerit($buid, $merit) {
    $file = $this->path.$this->file_merits;
    $new    = array();
    $change = false;
    $found  = false;

    foreach($this->MERITS as $k=>$line) {
      $data = explode(':', $line);
      if(trim($data[0]) == trim($buid)) {
        $found = true;
      }
    }

    if($found) {
      foreach($this->MERITS as $k=>$line) {
        $data = explode(':', $line);

        if(trim($data[0]) == trim($buid) && (int)trim($data[1] != (int)trim($merit))) {
          $change = true;
          echo 'CAMBIO '.$data[1]." >>>> ".trim($buid).':'.trim($merit)."\n";
          $new[]  = trim($buid).':'.(int)trim($merit);
        } else {
          if(trim($line)!=='')
            $new[]  = trim($line);
        }
      }

      if($change) {
        file_put_contents($file, implode("\n", $new));
        $this->MERITS = file($this->path.$this->file_merits);
      }

    } else {
      echo "AGGIUNGO NUOVO $buid $merit";
      file_put_contents($file, "\n".$buid.":".$merit."\n", FILE_APPEND);
      $this->MERITS = file($this->path.$this->file_merits);
      $change = true;
    }

    return $change;
  }
}
    
$CRON = new CronUid(new UidDB());
$CRON->__process();

