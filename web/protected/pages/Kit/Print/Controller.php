<?php
/**
 * This is the Kit Print page
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'kit';
	/**
	 * The kit that we are viewing
	 *
	 * @var Kit
	 */
	public $kit = null;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->isPostBack)
		{
			$this->kit = Kit::get($this->Request['kitId']);
			if(!$this->kit instanceof Kit)
				die('Invalid Kit!');
			if(isset($_REQUEST['pdf']) && intval($_REQUEST['pdf']) === 1)
			{
				$file = EntityToPDF::getPDF($this->kit);
				header('Content-Type: application/pdf');
				// The PDF source is in original.pdf
				readfile($file);
				die;
			}
		}
	}
	public function getKitProductInfo()
	{
		$html = '<div class="kit-product">';
			$html .= '<h4>' . $this->kit->getBarcode() . '</h4>';
			$html .= '<div>[' . $this->kit->getProduct()->getSku() . ']' . $this->kit->getProduct()->getName() . '</div>';
		$html .= '</div>';
		return $html;
	}
	/**
	 * Getting the tr for each row
	 * @param unknown $qty
	 * @param unknown $sku
	 * @param unknown $name
	 * @param unknown $uprice
	 * @param unknown $tprice
	 * @return string
	 */
	public function getRow($qty, $sku, $name, $isTitle=false)
	{
		$tag = ($isTitle === true ? 'th' : 'td');
		return "<tr><$tag class='sku col-xs-2'>$sku</td><$tag class='name'>$name</td><$tag class='qty col-xs-2'>$qty</td></tr>";
	}
	/**
	 *
	 * @return string
	 */
	public function showComponents()
	{
		$html = '';
		foreach(KitComponent::getAllByCriteria('kitId=?', array($this->kit->getId())) as  $index => $kitComponent)
		{
			$html .= $this->getRow($kitComponent->getQty(), $kitComponent->getComponent()->getSku(), $kitComponent->getComponent()->getName(), 'itemRow');
		}
		return $html;
	}
}
?>