<?php
/**
 * Service for accessing/storing content in shared storage
 *
 * @package    Core
 * @subpackage Service
 * @author     lhe<helin16@gmail.com>
 *
 */
class AssetService extends BaseServiceAbastract
{
    private $_assetRootPath = '';
	/**
	 * constructor
	 */
	public function __construct($rootPath = '')
	{
		parent::__construct('Asset');
		$this->_assetRootPath = ($rootPath === '' ? dirname(__FILE__) : $rootPath);
	}
	/**
	 * Setting the root path
	 * 
	 * @param string $rootPath The root path of the assets
	 * 
	 * @return AssetService
	 */
	public function setRootPath($rootPath)
	{
	    $this->_assetRootPath = $rootPath;
	    return $this;
	}
	/**
	 * Register a file with the Asset server and get its asset id
	 *
	 * @param string $filename The name of the file
	 * @param string $data     The data within that file we are trying to save
	 * 
	 * @return string 32 char MD5 hash
	 */
	public function registerAsset($filename, $dataOrFile)
	{
	    if(!is_string($dataOrFile) && (!is_file($dataOrFile)))
	        throw new CoreException(__CLASS__ . '::' . __FUNCTION__ . '() will ONLY take string to save!');
	    
	    $assetId = md5($filename . '::' . Core::getUser()->getId() .  '::' . microtime());
	    $path = $this->_getSmartPath($assetId);
	    $this->_copyToAssetFolder($path, $dataOrFile);
		$asset = new Asset();
		$asset->setFilename($filename)
		    ->setAssetId($assetId)
		    ->setMimeType(self::getMimeType($filename))
		    ->setPath($path);
		$this->save($asset);
		return $asset->getAssetId();
	}
	/**
	 * Getting the smart parth
	 * 
	 * @param string $assetId The asset id
	 * 
	 * @return string
	 */
	private function _getSmartPath($assetId)
	{
	    $now = new UDate();
	    $year = $now->format('Y');
	    if(!is_dir($yearDir = trim($this->_assetRootPath .DIRECTORY_SEPARATOR . $year)))
	    {
	        mkdir($yearDir);
	        chmod($yearDir, 0777);
	    }
	    $month = $now->format('m');
	    if(!is_dir($monthDir = trim($yearDir .DIRECTORY_SEPARATOR . $month)))
	    {
	        mkdir($monthDir);
	        chmod($monthDir, 0777);
	    }
	    return $monthDir . DIRECTORY_SEPARATOR . $assetId;	    
	}
	/**
	 * Remove an asset from the content server
	 *
	 * @param array $assetIds The assetids of the content
	 *
	 * @return bool
	 */
	public function removeAssets($assetIds)
	{
		if(count($assetIds) === 0)
			return $this;
		
		$where = "assetId in (" . implode(', ', array_fill(0, count($assetIds), '?')) . ")";
		$params = $assetIds;
		foreach($this->findByCriteria($where, $assetIds) as $asset)
		{
		    // Remove the file from the NAS server
		    unlink($asset->getPath());
		}
		// Delete the item from the database
		$this->updateByCriteria('set active = ?', $where, array_merge(array(0), $params));
		return $this;
	}
	/**
	 * copy the provided file or data into the new path
	 * 
	 * @param string $filename   The new filename
	 * @param string $dataOrFile the file or data
	 * 
	 * @return number|boolean
	 */
	private function _copyToAssetFolder($newFile, $dataOrFile)
	{
	    if(!is_file($dataOrFile))
	        return file_put_contents($newFile, $dataOrFile);
	    return rename($dataOrFile, $newFile);
	}
	/**
	 * Getting the Asset object
	 * 
	 * @param string $assetId The assetid of the content
	 * 
	 * @return Ambigous <unknown, array(HydraEntity), Ambigous, multitype:, string, multitype:Ambigous <multitype:, multitype:NULL boolean number string mixed > >
	 */
	public function getAsset($assetId)
	{
		$content = $this->findByCriteria('assetId = ?', array($assetId), false, 1, 1);
		return count($content) === 0 ? null : $content[0];
	}
	/**
	 * Simple method for detirmining mime type of a file based on file extension
	 * This isn't technically correct, but for our problem domain, this is good enough
	 *
	 * @param string $filename The name of the file
	 * 
	 * @return string
	 */
	public static function getMimeType($filename)
	{
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        switch(strtolower($fileSuffix[1]))
        {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/".strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

            case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";

            case "mov" :
                return "video/quicktime";

            case "zip" :
                return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
        }
        		
		if(function_exists("mime_content_type"))
			$fileSuffix = mime_content_type($filename);

		return "unknown/" . trim($fileSuffix[0], ".");
	}
}

?>