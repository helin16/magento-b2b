<?php
class ComScriptCURL
{
	const CURL_TIMEOUT = 360000;
	/**
	 * download the url to a local file
	 *
	 * @param string $url       The url
	 * @param string $localFile The local file path
	 *
	 * @return string The local file path
	 */
	public static function downloadFile($url, $localFile, $timeout = null, $extraOpts = array())
	{
		$timeout = trim($timeout);
		$timeout = (!is_numeric($timeout) ? self::CURL_TIMEOUT : $timeout);
		$fp = fopen($localFile, 'w+');
		$options = array(
				CURLOPT_FILE    => $fp,
				CURLOPT_TIMEOUT => $timeout, // set this to 8 hours so we dont timeout on big files
				CURLOPT_URL     => $url
				,CURLOPT_PROXY   => 'proxy.bytecraft.internal:3128'
		);
		foreach($extraOpts as $key => $value)
			$options[$key] = $value;
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		curl_exec($ch);
		fclose($fp);
		curl_close($ch);
		return $localFile;
	}
	/**
	 * read from a url
	 *
	 * @param string  $url             The url
	 * @param int     $timeout         The timeout in seconds
	 * @param array   $data            The data we are POSTING
	 * @param string  $customerRequest The type of the post: DELETE or POST etc...
	 *
	 * @return mixed
	 */
	public static function readUrl($url, $timeout = null, array $data = array(), $customerRequest = '', $extraOpts = array())
	{
		$timeout = trim($timeout);
		$timeout = (!is_numeric($timeout) ? self::CURL_TIMEOUT : $timeout);
		$options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $timeout, // set this to 8 hours so we dont timeout on big files
				CURLOPT_URL     => $url
				,CURLOPT_PROXY   => 'proxy.bytecraft.internal:3128'
		);
		foreach($extraOpts as $key => $value)
			$options[$key] = $value;
		if(count($data) > 0)
		{
			if(trim($customerRequest) === '')
				$options[CURLOPT_POST] = true;
			else
				$options[CURLOPT_CUSTOMREQUEST] = $customerRequest;
			$options[CURLOPT_POSTFIELDS] = http_build_query($data);
		}
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$data =curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	public static function is404($url, $timeout = null, $extraOpts = array()) 
	{
		$timeout = trim($timeout);
		$timeout = (!is_numeric($timeout) ? self::CURL_TIMEOUT : $timeout);
		$options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => $timeout, // set this to 8 hours so we dont timeout on big files
				CURLOPT_URL            => $url,
				CURLOPT_NOBODY         => true
				,CURLOPT_PROXY   => 'proxy.bytecraft.internal:3128'
		);
		foreach($extraOpts as $key => $value)
			$options[$key] = $value;
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$response =curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		/* If the document has loaded successfully without any redirection or error */
		if ($httpCode >= 200 && $httpCode < 300) {
			return false;
		} else {
			return true;
		}
	}
}