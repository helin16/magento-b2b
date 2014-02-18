<?php
/**
 * This is the LatestETAPanel
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class LatestETAPanel extends TTemplateControl
{
	public $pageNumber = 1;
	
	public $pageSize = 10;
	 
	public function __construct()
	{
		parent::__construct();
	}
	
	public function onInit($param)
	{
		parent::onInit($param);
		
		$scriptArray = $this->_loadJsAndCssFiles(get_class($this));
		foreach($scriptArray as $key => $value)
		{
			if(($value = trim($value)) !== '')
			{
				if($key === 'js')
					$this->getPage()->getClientScript()->registerScriptFile('latestETAJs', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('latestETACss', $this->publishAsset($value));
			}
		}
	}
	
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack)
		{
			$js = 'if(typeof(LatestETAPanel) !== "undefined" && pageJs) { var lepJs = new LatestETAPanel(pageJs); } else {alert("System Error: Cannot Load Latest ETA Panel Control!!");}';
			$js .= 'lepJs.resultDiv =  "' . $this->latest_eta_result_div->getClientID() . '";';
			$js .= 'lepJs.callBackId = "'.$this->getLatestEtaBtn->getUniqueID().'";';
			$js .= 'lepJs.setPagination('.$this->pageNumber.', '.$this->pageSize.');';
			$js .= 'lepJs.loadLatestETA();';
			$this->getPage()->getClientScript()->registerEndScript('lepJs', $js);		
		}
		
	}
	
	public function getLatestETAs($sender, $param)
	{
		$result = $error = array();
		
		try 
		{
			$pageNo = $this->pageNumber;
			$pageSize = $this->pageSize;
			
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
				
			$oiArray = FactoryAbastract::dao('OrderItem')->findByCriteria("(eta != '' and eta IS NOT NULL and eta != ?)", array(trim(UDate::zeroDate())), true, $pageNo, $pageSize, array("ord_item.eta" => "ASC", "ord_item.orderId" => "DESC"));
			
			foreach($oiArray as $oi)
			{
				$tmp['eta'] = trim($oi->getEta());
				$tmp['orderNo']	= $oi->getOrder()->getOrderNo();
				$tmp['sku']	= $oi->getProduct()->getSku();
				$tmp['productName']	= $oi->getProduct()->getName();
				$tmp['id'] = $oi->getId();
				$tmp['orderId'] = $oi->getOrder()->getId();
				$result[] = $tmp;
			}
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	
	private function _loadJsAndCssFiles($className)
	{
		$array = array('js' => '', 'css' => '');
		try
		{
			//loading controller.js
			$class = new ReflectionClass($className);
			$fileDir = dirname($class->getFileName()) . DIRECTORY_SEPARATOR;
			if (is_dir($fileDir))
			{
				//loop through the directory to find the lastes js version or css version
				$lastestJs = $lastestJsVersionNo = $lastestCss = $lastestCssVersionNo = '';
				foreach(glob($fileDir . '*.{js,css}', GLOB_BRACE) as $file)
				{
					preg_match("/^" . $className . "\.([0-9]+\.)?(js|css)$/i", basename($file), $versionNo);
					if (isset($versionNo[0]) && isset($versionNo[1]) && isset($versionNo[2]))
					{
						$type = trim(strtolower($versionNo[2]));
						$version = str_replace('.', '', trim($versionNo[1]));
						if ($type === 'js') //if loading a javascript
						{
							if ($lastestJs === '' || $version > $lastestJsVersionNo)
								$array['js'] = trim($versionNo[0]);
						}
						else if ($type === 'css')
						{
							if ($lastestCss === '' || $version > $lastestCssVersionNo)
								$array['css'] = trim($versionNo[0]);
						}
					}
				}
			}
			unset($className, $class, $fileDir, $lastestJs, $lastestJsVersionNo, $lastestCss, $lastestCssVersionNo);
		}
		catch(Exception $e)
		{
			//we are not doing anything if we failed here!
		}
		return $array;
	}
}
?>
