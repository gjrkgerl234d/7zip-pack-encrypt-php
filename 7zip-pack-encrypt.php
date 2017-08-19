<?php
require('../inc/str.php');
$if_7zip=false; // If not save 7 zip
$if_hash=true; // If not archive
$dir_name_input='z:/html/in'; // Dirname source files
$dir_name_arch='z:/html/out';// Dirname files 7z
$password_length=100; // Password length

function add_7z_archiv($dir_name_arch,$arch_name,$dir_name_input,$dir_name,$password_length=100,$file_mask='*.*',$recursive='-r')  
{
  GLOBAL $ar_info;
  $password=get_pass($password_length);
  $ar_file=dir_to_array($dir_name_input. '/' .$dir_name,true);
  $file_size=0;
  foreach($ar_file as $fname)
  {
    //echo "$fname <br>\r\n";
    if (is_array($fname)) continue;
    if (is_file($fname)) 
    {
      $cur_file_size=filesize($fname);
      $file_size+=$cur_file_size;  
    }
  }
  $ar_info=array('t'=>$dir_name,'p'=>$password,'s'=>strval($file_size));
  return '7z a '.$dir_name_arch. '/' .$arch_name.'.7z -p'.$password.' -mhe=on -mx=9 '.$recursive.' "'.$dir_name_input. '/' .$dir_name. '/' .$file_mask.'"';
}

if ($if_7zip) {
  if (make_dir_if_not_exists($dir_name_arch)==false) {echo "Error make dir $dir_name_arch <br>\r\n";exit;}
  $ar_dir=dir_to_array_nr($dir_name_input,false,false);
  print_r($ar_dir);
  foreach($ar_dir as $dir_name)
  {
    $arch_name=get_pass(14,true); // Имя файла с архивом
    $str_exec=add_7z_archiv($dir_name_arch,$arch_name,$dir_name_input,$dir_name,$password_length);
    echo "$str_exec <br>\r\n";
    exec($str_exec);
    if (file_exists($dir_name_arch.'/'.$arch_name.'.7z')) {
      $fsize=filesize($dir_name_arch.'/'.$arch_name.'.7z');
      $filemd5=hash_file('md5',$dir_name_arch.'/'.$arch_name.'.7z');
      $filesha256=hash_file('sha256',$dir_name_arch.'/'.$arch_name.'.7z');
      if (empty($fsize)) {echo "Empty filesize $arch_name <br>\r\n";continue;}
      if (empty($filemd5)) {echo "Empty md5 $arch_name <br>\r\n";continue;}
      if (empty($filesha256)) {echo "Empty sha256 $arch_name <br>\r\n";continue;}
      $ar_info['sz']=strval($fsize);
      $ar_info['md5']=strval($filemd5);
      $ar_info['sha']=strval($filesha256);
      $fh=fopen($dir_name_arch. '/' .$arch_name.'.txt','wb');
      if ($fh==false) continue;
      fwrite($fh,serialize($ar_info));
      fclose($fh);
    }
  }
}

if ($if_hash) {
  $ar_dir=dir_to_array_nr($dir_name_input);
  print_r($ar_dir);
  foreach($ar_dir as $fname) {
    if (strpos($fname,'.7z')===false) continue;
    $bname=str_replace('.7z','',$fname);
    $tname=$bname.'.txt';
    echo "$bname $fname $tname<br>\r\n";
    if (!file_exists($dir_name_input.'/'.$tname)) {
      echo "Not found file $tname <br>\r\n";
      continue;
    }
    $fbody=file_get_contents($dir_name_input.'/'.$tname);
    if ($fbody==false) {echo "Error read file $tname <br>\r\n";continue;}
    $ar_info=unserialize($fbody);
    if ($ar_info==false) {echo "Error unserialize $tname <br>\r\n";continue;}
    $fsize=filesize($dir_name_input.'/'.$fname);
    $filemd5=hash_file('md5',$dir_name_input.'/'.$fname);
    $filesha256=hash_file('sha256',$dir_name_input.'/'.$fname);
    if (empty($fsize)) {echo "Empty filesize <br>\r\n";continue;}
    if (empty($filemd5)) {echo "Empty md5 <br>\r\n";continue;}
    if (empty($filesha256)) {echo "Empty sha256 <br>\r\n";continue;}
    $ar_info['sz']=strval($fsize);
    $ar_info['md5']=strval($filemd5);
    $ar_info['sha']=strval($filesha256);
    file_put_contents($dir_name_input.'/'.$tname,serialize($ar_info));
  }
  
}