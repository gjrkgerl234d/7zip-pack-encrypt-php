<?php
// Загрузка файлов на сайт yadisk по хешам и размеру
// Upload files to Yandex.disk and publish
require_once('func-yad.php');
require_once('str.php');
$dir='txt'; // Каталог где сохранены файлы с данными 
$login=''; // Login yandex.ru
$pass=''; // Password yandex.ru
$ar_fname=dir_to_array_nr($dir);
foreach($ar_fname as $finfo) {
  if (strpos($finfo,'.txt')===false) continue;
  echo "File: $finfo <br>\r\n";
  $fname=str_replace('.txt','.7z',$finfo);
  $fbody=file_get_contents($dir.'/'.$finfo);
  if ($fbody==false) {echo "Error read file $finfo <br>\r\n";continue;}
  $inf=unserialize($fbody);
  if ($inf==false) {echo "Error unserialize $finfo <br>\r\n";continue;}
  if (!isset($inf['sz'])) {echo "Error unserialize $finfo not found sz<br>\r\n";continue;}
  if (!isset($inf['md5'])) {echo "Error unserialize $finfo not found sz<br>\r\n";continue;}
  if (!isset($inf['sha'])) {echo "Error unserialize $finfo not found sz<br>\r\n";continue;}
  $code=yad_put_file_md5('',$fname,$inf['sz'],$inf['sha'],$inf['md5'],$login,$pass);
  echo "Code $code <br>\r\n";
  if ($code<>'201') continue;
  $url=yad_publish('',$fname,$login,$pass);
  if ($url==false) {echo "Error <br>\r\n";continue;}
  echo "Url: $url <br>\r\n";
  $is_find_url=false;
  if (isset($inf['urls'])) {
    foreach($inf['urls'] as $ourl) {
      if ($ourl==$url) $is_find_url=true;
    }
  }
  if ($is_find_url) {echo "Find url <br>\r\n";continue;}
  if (!isset($inf['urls'])) $inf['urls']=array();
  $inf['urls'][]=$url;
  $fh=fopen($dir. '/' .$finfo,'wb');
  if ($fh==false) {echo "Error save file $finfo <br>\r\n";continue;}
  fwrite($fh,serialize($inf));
  fclose($fh);  
}