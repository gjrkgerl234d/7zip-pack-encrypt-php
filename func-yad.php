<?php
function yad_url_token($client_id) {
  return 'https://oauth.yandex.ru/authorize?response_type=code&client_id='.$client_id;
}
function yad_get_token_from_code($code,$client_id,$client_secret) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,'https://oauth.yandex.ru/token');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,'grant_type=authorization_code&code='.$code.'&client_id='.$client_id.'&client_secret='.$client_secret);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  curl_close ($ch);
  $json=json_decode($server_output,true);
  //echo $server_output;
  //echo '<pre>';print_r($json);echo '</pre>';
  if ($json==false) {
    return false;
  }
  if (isset($json['access_token'])) {
    return $json['access_token'];
  }
}

// Загрузка файла на яндекс диск
function yad_put_file($token,$file,$fname) {
  $filesize=filesize($file);
  $put_data=fopen($file,'rb');
  if ($put_data==false) {
    echo "Error open file $file <br>\r\n";
    return false;
  }
  
  $ch = curl_init();
  //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_URL,'https://webdav.yandex.ru/'.$fname);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
  curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
  curl_setopt($ch, CURLOPT_INFILE, $put_data);
  curl_setopt($ch, CURLOPT_PUT, 1);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_HEADER,1);
  curl_setopt($ch, CURLINFO_HEADER_OUT,1);
  
  
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  fclose($put_data);
  $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
  curl_close ($ch);
  //$json=json_decode($server_output,true);
  //echo $server_output;
  //echo '<pre>';print_r($json);echo '</pre>';
  return $code;
}

// Загрузка файла на яндекс диск с использование контрольной суммы
function yad_put_file_md5($token,$fname,$fsize,$fsha256,$fmd5,$login='',$pass='') {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_URL,'https://webdav.yandex.ru/'.$fname);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $arr_head=array();
  if (!empty($token)) {$arr_head[]='Authorization: OAuth '.$token;}
  else {
    $token_base=base64_encode($login.':'.$pass);
    $arr_head[]='Authorization: Basic '.$token_base;
  }
  $arr_head[]='Etag: '.$fmd5;
  $arr_head[]='Sha256: '.$fsha256;
  $arr_head[]='Expect: 100-continue';
  $arr_head[]='Content-Type: application/binary';
  $arr_head[]='Content-Length: '.$fsize;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_head);
  //curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
  //curl_setopt($ch, CURLOPT_INFILE, $put_data);
  curl_setopt($ch, CURLOPT_PUT, 1);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_HEADER,1);
  curl_setopt($ch, CURLINFO_HEADER_OUT,1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
  curl_close ($ch);
  //$json=json_decode($server_output,true);
  //echo $server_output;
  //echo '<pre>';print_r($json);echo '</pre>';
  return $code;
}

// Возвращает список файлов и каталогов пользователя
function yad_list($token,$dir) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,'https://cloud-api.yandex.net/v1/disk/resources?path=/'.$dir);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
  curl_setopt($ch, CURLOPT_VERBOSE, false);
  curl_setopt($ch, CURLOPT_HEADER,false);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  fclose($put_data);
  curl_close ($ch);
  $json=json_decode($server_output,true);
  //echo $server_output;
  //echo '<pre>';print_r($json);echo '</pre>';
  if (!isset($json['_embedded']['items'])) return false;
  $r=array();
  foreach($json['_embedded']['items'] as $i) {
    $name=$i['name'];
    $public_url='';
    $type='';
    if (isset($i['public_url'])) $public_url=$i['public_url'];
    if (isset($i['type'])) $type=$i['type'];
    $r[]=array('name'=>$name,'public_url'=>$public_url,'type'=>$type);
  }
  return $r;
}

// Публикация файла или папки
function yad_publish($token,$file,$login='',$pass='') {
  $str='<propertyupdate xmlns="DAV:">
  <set>
    <prop>
      <public_url xmlns="urn:yandex:disk:meta">true</public_url>
    </prop>
  </set>
</propertyupdate>';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPPATCH');
  curl_setopt($ch, CURLOPT_URL,'https://webdav.yandex.ru/'.$file);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  if (!empty($token)) curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
  else {
    $token_base=base64_encode($login.':'.$pass);
    //echo "$token_base <br>\r\n";
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$token_base));
  }
  curl_setopt($ch, CURLOPT_VERBOSE, false);
  curl_setopt($ch, CURLOPT_HEADER,false);
  curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  //echo "$server_output <br>\r\n";
  curl_close ($ch);
  $str_find='<d:status>HTTP/1.1 200 OK</d:status>';
  if (strpos($server_output,$str_find)==false) {return false;}
  $out=array();
  if (preg_match('/(?ims)<public_url [^>]+>(.*)<\/public_url>/U',$server_output,$out)!==1) {return false;}
  if (!isset($out[1])) {return false;}
  return trim($out[1]);
}

// Получаем логин и id пользователя по токену
function yad_get_user_info($token) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,'https://login.yandex.ru/info?format=json');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
  curl_setopt($ch, CURLOPT_VERBOSE, false);
  curl_setopt($ch, CURLOPT_HEADER,false);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec ($ch);
  curl_close ($ch);
  //echo $server_output;
  $json=json_decode($server_output,true);
  return $json;
}

// Получаем логин id токен пользователя сохраненные в локальном каталоге если ошибка тогда FALSE
function yad_get_user_info_local($userid) {
  $fname=$userid.'/info.txt';
  if (file_exists($fname)) {
    $file_body=file_get_contents($fname);
    if ($file_body==false) return false;
    $json=json_decode($file_body,true);
    return $json;
  } else return false;
}

// Устаревшие функции
function yad_pub($configname,$fname,$fname_url)
{
  $log="$configname $fname $fname_url";
  echo $log." <br>\r\n";
  //add_log($log);
  $str='yandex-disk publish --config='.$configname.' '.$fname;
  $out=array();
  exec($str,$out);
  $str='';
  if (count($out)>0)
  {
    foreach($out as $r)
    {
      $str.=$r;
    }
  }
  $str=trim($str);
  if (strpos($str,'http')===0) {
    file_put_contents($fname_url,trim($str));
  }
  echo $str."<br>\r\n";
  add_log($str);
}

function yad_upd($dirname,$basename) {
  // Опубликовать на каталогах
  GLOBAL $urls_upload;
  
  add_log("$dirname,$basename");
  $title=file_get_contents($dirname.'/'.$basename.'-name.txt');
  $opis=file_get_contents($dirname.'/'.$basename.'-opis.txt');
  $url=file_get_contents($dirname.'/'.$basename.'-url.txt');
  foreach ($urls_upload as $target_url) {
    $fname = $dirname.'/'.$basename.'.jpg';   
    $cfile = new CURLFile($fname);
    $post = array ('pass'=>'asfgkj23kjlsdf','title'=>urlencode($title),'opis'=>urlencode($opis),'url'=>urlencode($url),'userfile' => $cfile);    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");   
    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 900);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec ($ch);
    if ($result === FALSE) {
      add_log("Error sending $target_url $fname " . curl_error($ch));
      echo "Error send $target_url <br>\r\n";
      curl_close ($ch);
    } else {
      curl_close ($ch);
      add_log("Result $fname " . $result);
      echo "$result <br>\r\n";
    }
  }
}
