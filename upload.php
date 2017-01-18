<?php

require_once('config.qiniu.php');

 function _urlsafe_base64_encode($str){
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($str));
}

$filename = md5(time());
$putPolicy = array(
    'scope' => QINIU_BUCKET.':'.$filename ,
    'deadline' => time()+3600 ,
    'returnBody' => '{
        "name": $(fname),
        "file_url": $(x:file_url)
    }'
);

// 2.将上传策略序列化成为JSON格式：
$putPolicy = json_encode($putPolicy);

// 3.对 JSON 编码的上传策略进行URL安全的Base64编码，得到待签名字符串：
$encodedPutPolicy = _urlsafe_base64_encode($putPolicy);

// 4.使用SecretKey对上一步生成的待签名字符串计算HMAC-SHA1签名：
$sign = hash_hmac('sha1' ,$encodedPutPolicy, QINIU_SECRET_KEY, true);

// 5.对签名进行URL安全的Base64编码：
$encodedSign = _urlsafe_base64_encode($sign);

$uploadToken = QINIU_ACCESS_KEY . ':' . $encodedSign . ':' . $encodedPutPolicy;

echo json_encode(array('token' => $uploadToken, 'key' => $filename, 'fileurl' => QINIU_DOMAIN));