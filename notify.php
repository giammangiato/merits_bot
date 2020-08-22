<?php
require 'config.php';
define('API_KEY',   $TOKEN);
define('DEBUG',     false);  
require 'utils.php';
require 'bitcointalk.php';

$users_db = new NickDB();
$users = $users_db->__getList();

/*
$db   = explode("\n", file_get_contents('bot_data/NICK-db.txt'));
$users = array();
foreach($db as $line) {
  if(trim($line)!=='') {
    $d = explode(':',trim($line));
    $users[strtolower($d[0])] = $d[1];
  }
}
*/

function isMentioned($users, $nick, $href) {
  $user = $users[$nick];
  $userfile = 'bot_data/user_'.$user.'.txt';
  $mentions = explode("\n", file_get_contents($userfile));
  echo 'CHECK MENTION FOR '.$nick."\n";
  foreach($mentions as $mention) {
    if($href === trim($mention)) {
      echo "[###] POST FOUND IN MEMORY \n";
      return true;
    }
  }
  file_put_contents($userfile, $href."\n", FILE_APPEND);
  echo "[###] POST NOT FOUND IN MEMORY \n";
  return false;
}

function evaluateNick($t, $h, $nick, $users, $poster, $amsg) {
  $real = str_replace('@','',$nick);
  echo 'REAL '.$real."\n";
  // echo print_r($users);
  if(isset($users[$real])) {
    $msg = 'mentioned by <b>'.$poster.'</b> in <a href="'.$h.'">'.$t."</a>\n<pre>".$amsg."</pre>";
    if(!isMentioned($users, $real, $h)) {
      sm($users[$real], $msg, 'Html');
      echo "[".$nick."] ";
      echo '>>>>>>>>>>>>>>> SEND MESSAGE '.$users[$real]."\n";
    }
  }
}

$url  = 'https://bitcointalk.org/index.php?action=recent';

$html = file_get_contents($url);
$doc  = new \DOMDocument();
$doc->loadHTML($html);

$xpath    = new \DOMXpath($doc);
$posts    = $xpath->query('//table[@class="bordercolor"]/tr');
$re  = '/(\@[a-zA-Z0-9\-\_\s]*)/m';
$req = '/Quote from: ([a-zA-Z0-9\-\_\s]*) on/m';

$count = 0;
foreach($posts as $post) {
  if($count>0) {
    $classe = $post->getAttribute('class');

    if($classe == 'titlebg2') {
      $data = explode("\n", $post->nodeValue);
      $TITLE = trim($data[2]);

      $tds = $post->getElementsByTagName('td');
      foreach($tds as $td) {
        if($td->getAttribute('class')=='middletext') {

          $divs = $td->getElementsByTagName('div');
          foreach($divs as $div) {

            $as = $div->getElementsByTagName('a');
            $ca = 0;
            foreach($as as $a) {
              if($ca==2) {
                $HREF = $a->getAttribute('href');
              }
              $ca++;
            }
          }
        }
      }
    } 

    $tds = $post->getElementsByTagName('td');
    foreach($tds as $td) {
      $td_classe = $td->getAttribute('class');

      if($td_classe=='catbg') {
        $posters = explode('by',trim($td->nodeValue));
        $POSTER = trim(strtolower($posters[2]));
        echo '[+] Evaluate POSTER '.$POSTER."\n";
      }

      if($td_classe=='windowbg2') {
        echo '[+] Evaluate POST '.$TITLE."\n";
        echo '[-] '. $HREF."\n";
        $MSG_SUB  = '**preview**';

        $divs = $td->getElementsByTagName('div');
        foreach($divs as $div) {
          if($div->getAttribute('class') == 'post') {

            $text = str_replace("\n", " ",$div->textContent);
            $text = html_entity_decode($div->textContent, ENT_QUOTES, 'UTF-8');
            $text = $doc->saveXML($div);

            $div_children = $div->getElementsByTagName('div');
            foreach($div_children as $div_second_child) {
              while ($div_second_child->hasChildNodes()) {
                $div_second_child->removeChild($div_second_child->firstChild);
              }
            }
            $msg_text = trim($post->nodeValue);
            $MSG_SUB  = substr($msg_text, 0,50);
            echo "\n#####\n".$MSG_SUB."\n#####\n\n";

            preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
            foreach($matches as $mm) {
              foreach($mm as $m) {
                if(trim($m)!=='') {
                  echo '[+] CHECK '.$m."\n";
                  $finalnick = trim(strtolower($m));

                  if($finalnick === $POSTER) {
                    echo "[!!!] Same user\n";
                  } else {
                    evaluateNick(
                      $TITLE, $HREF, $finalnick, $users, $POSTER, $MSG_SUB
                    );
                  }
                }
              }
            }
            preg_match_all($req, $text, $matches, PREG_SET_ORDER, 0);
            foreach($matches as $mm) {
              foreach($mm as $m) {
                $mn = str_replace(' on','',$m);
                if(trim($mn)!=='') {
                  echo '[+] QUOTE '.$mn."\n";
                  $finalnick = trim(strtolower($mn));

                  if($finalnick === $POSTER) {
                    echo "[!!!] Same user\n";
                  } else {
                    evaluateNick(
                      $TITLE, $HREF, $finalnick, $users, $POSTER, $MSG_SUB
                    );
                  }
                }
              }
            }
          }
        }
        echo "\n";

      }
    }

  }

  $count++;
}















