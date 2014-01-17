<?php
require_once dirname(__FILE__)."/../main/bootstrap.php";
require_once dirname(__FILE__)."/SchemaGenerator.php";

$args = array();
if(isset($argv)) 
{
    foreach ($argv as $k=>$v)
    {
        if($k==0) 
        	continue;
        	
        $it = explode("=",$argv[$k]);
        if(isset($it[1])) 
        	$args[$it[0]] = $it[1];
    }
} 
$gen = new SchemaGenerator('test');
if(isset($_REQUEST["submitForm"]))
{
    $gen->run($_REQUEST);
}
else
{
    $gen->form();
}
?>