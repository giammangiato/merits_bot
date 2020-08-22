<?php

class Bitcointalk {

    private $ch = null,
            $loginUrl = null,
            $username = null,
            $password = null,
            $captchaCode = null,
            $reponse = '',
            $headerSize = '',
            $header = '',
            $body = '';

    public function __construct($loginUrl, $username, $password, $captchaCode) {

        $this->loginUrl = $loginUrl;
        $this->username = $username;
        $this->password = $password;
        $this->captchaCode = $captchaCode;

        $tmpPath = dirname(__FILE__).'/../tmp';
        $cookieFilePath = $tmpPath.'/cookie.txt';

        if(!file_exists($tmpPath)) {

            mkdir($tmpPath);
        }

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_NOBODY, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookieFilePath);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookieFilePath);

        curl_setopt($this->ch, CURLOPT_COOKIE, "cookiename=0");
        curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.50 Safari/537.36");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_POST, 1);
    }

    private function getResponseHeader() {

        return $this->header;
    }

    private function getResponseBody() {

        return $this->body;
    }

    private function setUrl($url) {

        curl_setopt($this->ch, CURLOPT_URL, $url);
    }

    private function setPostFields($fields) {

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $fields);
    }

    private function executeRequest() {

        $this->reponse = curl_exec($this->ch);
        $this->headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $this->header = substr($this->reponse, 0, $this->headerSize);
        $this->body = substr($this->reponse, $this->headerSize);
    }

    public function isConnected() {

        $this->setUrl($this->loginUrl.';ccode='.$this->captchaCode);

        $this->executeRequest();

        return((strstr($this->getResponseHeader(), 'Location: https://bitcointalk.org/index.php')) ? true : false);
    }

    public function connect() {

        if(!$this->isConnected()) {

            $this->setUrl($this->loginUrl.';ccode='.$this->captchaCode);
            $this->setPostFields("user=".$this->username."&passwrd=".$this->password.'&cookieneverexp=on');

            $this->executeRequest();
        }
    }

    public function getLastMerits($profileId) {

        $warning = false;

        $this->setUrl('https://bitcointalk.org/index.php?action=merit;u='.$profileId);
        $this->executeRequest();

        preg_match('/Received in the last 120 days<\/h3><ul><li>(.+?)<\/li>/', $this->getResponseBody(), $last);

        $str = strip_tags($last[0]);

        $re = '/:\s([0-9]+)\s/m';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $merit = trim(str_replace(':','',$matches[0][0]));

        $re = '/for\s(.+)/m';
        preg_match_all($re, $str, $matches2, PREG_SET_ORDER, 0);
        $thread = trim(str_replace('for ','',$matches2[0][0]));

        $re = '/from\s(.+)\s(for)/m';
        preg_match_all($re, $str, $matches3, PREG_SET_ORDER, 0);
        $from = trim(str_replace(array('from','for'),'',$matches3[0][0]));
        return array('thread' => $thread, 'from' => $from, 'merit'=>$merit);
    }

    public function getTrustsInfos($profileId) {

        $warning = false;

        $this->setUrl('https://bitcointalk.org/index.php?action=profile;u='.$profileId);
        $this->executeRequest();

        preg_match('/<span class="trustscore" style="color:black">(.+?)<\/span>/', $this->getResponseBody(), $trusts);
        preg_match('/<title>(.+?)<\/title>/', $this->getResponseBody(), $username);

        if(!isset($trusts[0])) {

            preg_match('/<span class="trustscore" style="color:#DC143C">(.+?)<\/span>/', $this->getResponseBody(), $trusts);

            $warning = true;
        }

        return((isset($trusts[0])) ? [
                'warning' => $warning,
                'username' => str_replace('View the profile of ', '', strip_tags($username[0])),
                'trusts' => array_map('trim', explode('/', str_replace('!!!:  ', '', strip_tags($trusts[1]))))
            ] : [
            'error'=> true
        ]);
    }
}

$config = [
    'login' => [
      'link' => 'https://bitcointalk.org/index.php?action=login2', 
      'username' => 'USERNAME', 
      'password' => 'PASS', 
      'captcha-code' => 'CAPTCHA-CODE'
    ],
    'refreshCacheTimeInHours' => 2
];

?>
