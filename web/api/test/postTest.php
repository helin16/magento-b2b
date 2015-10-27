<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/';

$url = $baseUrl . 'UserAccount/login';
$params = array('username' => 'helin16', 'password' => '262bab1f48755709edd4c9c8774ec2d0d97857e7');
$result = post($url, $params);
var_dump($result);

$array = json_decode($result, true);
var_dump($array);

$params = array('token' => $array['token'] );
$url = $baseUrl . 'Product/dataFeedImport';
$result = post($url, $params);
var_dump($result);

function post($url, $data)
{
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($data));
    curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}