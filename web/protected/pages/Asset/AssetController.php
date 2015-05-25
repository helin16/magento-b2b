<?php
/**
 * This is the Asset Streamer
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 *
 */
class AssetController extends TService
{
    /**
     * (non-PHPdoc)
     * @see TService::run()
     */
    public function run()
    {
        try 
        {
            $method = '_' . ((isset($this->Request['method']) && trim($this->Request['method']) !== '') ? trim($this->Request['method']) : '');
            if(!method_exists($this, $method))
                throw new Exception('No such a method: ' . $method . '!');
            $this->$method($_REQUEST);
        }
        catch (Exception $ex)
        {
            $this->getResponse()->write($ex->getMessage());
        }
    }
    /**
     * Getting the id
     * 
     * @param array $params
     * 
     * @return mix
     */
    private function _get($params)
    {
    	if(!isset($params['id']) || ($assetId = trim($params['id'])) === '')
    		throw new Exception('Nothing to get!');
    	$asset = null;
    	//try to use apc
    	if(extension_loaded('apc') && ini_get('apc.enabled'))
    	{
    		if(!apc_exists($assetId))
    		{
    			$asset = Asset::getAsset($assetId);
    			apc_add($assetId, $asset);
    		}
    		else
    		{
    			$asset = apc_fetch($assetId);
    		}
    		
    	}
    	else
    	{
    		$asset =  Asset::getAsset($assetId);
    	}
    	
    	if(!$asset instanceof Asset)
	        throw new Exception('invalid id(' . $assetId . ') to get!');
    	$this->getResponse()->writeFile($asset->getFileName(), file_get_contents($asset->getPath()), $asset->getMimeType(), null, false);
    }
    /**
     * Getting the Barcode
     * 
     * @param unknown $params
     * 
     * @throws Exception
     */
    private function _renderBarcode($params)
    {
    	if(!isset($params['text']) || ($text = trim($params['text'])) === '')
    		throw new Exception('Nothing to draw!');
    	$noText = (isset($params['noText']) && trim($params['noText']) === '1');
    	$result = PhpBarcode::drawBarcode($text, $noText);
    	$this->getResponse()->writeFile($result['filename'], $result['content'], $result['mimeType'], null, false);
    }
}