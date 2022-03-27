<?php

echo "Backup...\n";

include "simple_html_dom.php";

// cpanel login
$username = 'your-username';
$password = 'your-password';

$baseURL  = 'https://your-whmURL-without-Port.com';
$cPanelPort = 2083;

// backup ftp login
$backupHost = 'your-host';
$backupUser = 'your-username';
$backupPass = 'your-password';
$backupPort = '21';             // using ftp
$backupPath = '/home/backup';
$backupMail = 'your-email';

$html = new simple_html_dom();

$cPanelUrl = $baseURL.':'.$cPanelPort;

// login WHM
$ch = curl_init();
$url = $cPanelUrl."/login/";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'user='.$username.'&pass='.$password);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);

echo "1\n";

// get cpsess
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$url = explode("/",$url);
$cpsess = $url[3];
$theme = $url[5];

echo "2\n";
print_r($url);

$url = "$cPanelUrl/$cpsess/frontend/$theme/backup/dofullbackup.html?dest=ftp&server=$backupHost&user=$backupUser&pass=$backupPass&port=$backupPort&rdir=$backupPath&email=$backupMail&submit=Generate Backup";

curl_setopt($ch, CURLOPT_URL, $url);
$out=curl_exec($ch);

echo "done\n";
