<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

if(isset($argv) && isset($argv[1]) && Product::get($argv[1]) instanceof Product)
	$productIds = Dao::getResultsNative('select distinct p.id from product p where p.id = ?', array($argv[1]), PDO::FETCH_ASSOC);
else $productIds = Dao::getResultsNative('select distinct p.id from product p inner join productpricematchrule r on (r.productId = p.id and r.active = 1) where p.active = 1 order by p.id', array(), PDO::FETCH_ASSOC);

$rows = count($productIds);
echo "--- Got ($rows) products having price matching rules !";

foreach ($productIds as $row)
{
	try {
		$output = '';
		$timeout = 60; // in seconds
		$cmd = 'php ' . dirname(__FILE__). '/pricematch.php ' . $row['id'];
		$output = ExecWaitTimeout($cmd, $timeout);
	// 	exec($cmd, $output);
		echo print_r($output, true) . "\n";
	} catch (Exception $e)
	{
		echo $e->getMessage() . "\n";
	}
}

/**
 * Execute a command and kill it if the timeout limit fired to prevent long php execution
 * 
 * @see http://stackoverflow.com/questions/2603912/php-set-timeout-for-script-with-system-call-set-time-limit-not-working
 * 
 * @param string $cmd Command to exec (you should use 2>&1 at the end to pipe all output)
 * @param integer $timeout
 * @return string Returns command output 
 */
function ExecWaitTimeout($cmd, $timeout=5) {
 
	echo $cmd . "\n";
	
  $descriptorspec = array(
      0 => array("pipe", "r"),
      1 => array("pipe", "w"),
      2 => array("pipe", "w")
  );
  $pipes = array();
 
  $timeout += time();
  $process = proc_open($cmd, $descriptorspec, $pipes);
  if (!is_resource($process)) {
    throw new Exception("proc_open failed on: " . $cmd);
  }
 
  $output = '';
 
  do {
    $timeleft = $timeout - time();
    $read = array($pipes[1]);
//     if($timeleft > 0)
    	stream_select($read, $write = NULL, $exeptions = NULL, $timeleft, NULL);
 
    if (!empty($read)) {
      $output .= fread($pipes[1], 8192);
    }
  } while (!feof($pipes[1]) && $timeleft > 0);
 
  if ($timeleft <= 0) {
    proc_terminate($process);
    throw new Exception("command timeout on: " . $cmd);
  } else {
    return $output;
  }
}