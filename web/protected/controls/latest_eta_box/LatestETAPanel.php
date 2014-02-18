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
		
		$scriptArray = BPCPageAbstract::getLastestJS(get_class($this));
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
				
			$oiArray = FactoryAbastract::service('OrderItem')->findByCriteria("(eta != '' and eta IS NOT NULL and eta != ?)", array(trim(UDate::zeroDate())), true, $pageNo, $pageSize, array("ord_item.eta" => "ASC", "ord_item.orderId" => "DESC"));
			$result['pagination'] =  FactoryAbastract::service('OrderItem')->getPageStats();
			$result['items'] = array();
			foreach($oiArray as $oi)
			{
				$tmp['eta'] = trim($oi->getEta());
				$tmp['orderNo']	= $oi->getOrder()->getOrderNo();
				$tmp['sku']	= $oi->getProduct()->getSku();
				$tmp['productName']	= $oi->getProduct()->getName();
				$tmp['id'] = $oi->getId();
				$tmp['orderId'] = $oi->getOrder()->getId();
				$result['items'][] = $tmp;
			}
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
}
?>
