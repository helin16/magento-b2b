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
			$js = 'if(typeof(LatestETAPanel) !== "undefined" && pageJs) {';
				$js .= 'var lepJs = new LatestETAPanel(pageJs); ';
				$js .= 'lepJs.resultDiv = "dtw_eta_' . $this->getId() . '";';
				$js .= 'lepJs.callBackId = "'.$this->getLatestEtaBtn->getUniqueID().'";';
				$js .= 'lepJs.setPagination('.$this->pageNumber.', '.$this->pageSize.');';
				$js .= 'lepJs.loadLatestETA();';
			$js .= '}';
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
			$notSearchStatusIds = array(OrderStatus::ID_CANCELLED, OrderStatus::ID_PICKED, OrderStatus::ID_SHIPPED);
			OrderItem::getQuery()->eagerLoad('OrderItem.order', 'inner join', 'ord', 'ord.id = ord_item.orderId and ord.active = 1');
			$stats = array();
			$oiArray = OrderItem::getAllByCriteria("(eta != '' and eta IS NOT NULL and eta != ? and ord.statusId not in (" . implode(',', $notSearchStatusIds). "))", array(trim(UDate::zeroDate())), true, $pageNo, $pageSize, array("ord_item.eta" => "ASC", "ord_item.orderId" => "DESC"), $stats);
			$result['pagination'] =  $stats;
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
