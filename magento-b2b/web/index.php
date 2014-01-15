<?php
define('PRADO_CHMOD',0755);
$basePath=dirname(__FILE__);
$assetsPath=$basePath.'/assets';
$runtimePath=$basePath.'/protected/runtime';

if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");

require 'bootstrap.php';
//check library availibility
try
{
	Core::setLibrary($_SERVER['SERVER_NAME']);
}
catch(Exception $e)
{
    echo FrontEndPageAbstract::show404Page("404 Not Found", "The page that you have requested could not be found.");
    exit();
}

//enforce https
$application=new TApplication;
if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
{
//     header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
//     exit();
}
$application->run();
?>