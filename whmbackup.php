<?php

echo "Backup...\n";

include "config.php";
include "simple_html_dom.php";

$html = new simple_html_dom();

$whmUrl    = $baseURL.':'.$whmPort;
$cPanelUrl = $baseURL.':'.$cPanelPort;

// login WHM
$ch = curl_init();
$url = $whmUrl."/login/";
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

echo "2\n";
print_r($url);

// get cpanel account list
$url = $whmUrl."/".$cpsess."/scripts4/listaccts?viewall=1&search=&searchtype=&acctp=30&sortrev=&sortorder=domain";
curl_setopt($ch, CURLOPT_URL, $url);
$out = curl_exec($ch);

echo "3\n";

// get cpanel username
$html->load($out);

$tdshade1 = $html->find('tr.tdshade1');
$tdshade2 = $html->find('tr.tdshade2');

$users = array();

foreach($tdshade1 as $item) {
        $a = $item->attr;
        $users[] = $a['user'];
}
foreach($tdshade2 as $item) {
        $a = $item->attr;
        $users[] = $a['user'];
}

sort($users);

print_r($users);

foreach($users as $user) {
        echo "backup user $user \n";

        //echo "1\n";

        $i = 1;

        // xfercpanel ke user
        $url = $whmUrl."/xfercpanel";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'token=/'.$cpsess.'&user='.$user);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $out = curl_exec($ch);
        //file_put_contents($user.$i++,$out); // 1

        // parsing url ke cPanel user
        $html = new simple_html_dom();
        $html->load($out);

        $out = $html->find('meta[http-equiv=refresh]');
        $out = $out[0]->attr;
        $out = $out['content'];
        $url = str_replace("2;URL=","",$out);

        // login ke cPanel user
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $out = curl_exec($ch);
        //file_put_contents($user.$i++,$out); // 2

        // get cpsess2 & theme
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $url = explode("/",$url);
        $cpsess2 = $url[3];
        $theme = $url[5];

        //echo "2\n";
        //echo "$theme\n";

        /*
        $url = "$cPanelUrl/$cpsess2/frontend/$theme/index.html";
        curl_setopt($ch, CURLOPT_URL, $url);
        $out=curl_exec($ch);
        file_put_contents($user.$i++,$out); // 3
        */

        // backup
        $url = "$cPanelUrl/$cpsess2/frontend/$theme/backup/dofullbackup.html?dest=scp&server=$backupHost&user=$backupUser&pass=$backupPass&port=$backupPort&rdir=$backupPath&email=$backupMail&submit=Generate Backup";
        curl_setopt($ch, CURLOPT_URL, $url);
        $out=curl_exec($ch);

        //echo $out;
        //break;
        //file_put_contents($user.$i++,$out); // 4

        // sleep
        sleep(60);
}
