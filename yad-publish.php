<?php
// Publish file YandexDisk
require_once('func-yad.php');
require_once('str.php');
$dir='txt'; // Dir name files
$login=''; // Login yandex.ru
$pass=''; // Pass yandex.ru
$ar_fname=dir_to_array_nr($dir);
foreach($ar_fname as $r) {
  if (strpos($r,'.7z')===false) continue;
  echo "File: $r <br>\r\n";
  $bname=str_replace('.7z','',$r);
  $finfo=$bname.'.txt';
  if (!file_exists($dir.'/'.$finfo)) {echo "Not found file $finfo <br>\r\n";continue;}
  $fbody=file_get_contents($dir.'/'.$finfo);
  if ($fbody==false) {echo "Error read file $finfo <br>\r\n";continue;}
  $ar_info=unserialize($fbody);
  if ($ar_info==false) {echo "Error unserialize $finfo <br>\r\n";continue;}
  $url=yad_publish('',$r,$login,$pass);
  if ($url==false) {echo "Error <br>\r\n";continue;}
  echo "Url: $url <br>\r\n";
  $is_find_url=false;
  if (isset($ar_info['urls'])) {
    foreach($ar_info['urls'] as $ourl) {
      if ($ourl==$url) $is_find_url=true;
    }
  }
  if ($is_find_url) {echo "Find url <br>\r\n";continue;}
  if (!isset($ar_info['urls'])) $ar_info['urls']=array();
  $ar_info['urls'][]=$url;
  $fh=fopen($dir. '/' .$finfo,'wb');
  if ($fh==false) {echo "Error save file $finfo <br>\r\n";continue;}
  fwrite($fh,serialize($ar_info));
  fclose($fh);  
}