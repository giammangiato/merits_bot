<?php
error_reporting(0);

function activityFromForum($user = 1) {
  $url = 'https://bitcointalk.org/index.php?action=profile;u='.$user;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  curl_close ($ch);
  if ($server_output) {
    return array(
      'posts' => extractElm('Posts:', $server_output),
      'activity' => extractElm('Activity:', $server_output),
      'position' => extractElm('Position:', $server_output),
      'last' => extractElm('Last Active:', $server_output),
      'merit' => extractElm('Merit:', $server_output),
    );
  } else {
    return 0;
  }
}

function extractElm($how, $html) {
  $html = explode('<td class="windowbg" width="420">', $html)[1];
  $doc = new DOMDocument();
  $doc->loadHTML($html);

  $h1Tags = $doc->getElementsByTagName('tr');
  foreach ($h1Tags as $tr) {
    $info = array();
    $flag = false;

    foreach ($tr->getElementsByTagName('td') as $tag) {
      $tdValue = $tag->nodeValue;
      if ($flag === true) {
        $flag = false;
        return $tag->nodeValue;
      }

      if (trim($tdValue) === trim($how)) {
        $flag = true;
      }
    }
  }
}

