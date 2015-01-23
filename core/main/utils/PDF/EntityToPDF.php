<?php
/**
 * an utils to convert an Entity to PDF
 * @author lhe
 *
 */
class EntityToPDF
{
	/**
	 * The root directory of the template files
	 *
	 * @var string
	 */
	private static $_templateDir = '';
	/**
	 * The loaded template files
	 *
	 * @var array
	 */
	private static $_tempCache = array();
	/**
	 * Getting the a HTML2PDF object from an entity
	 *
	 * @param BaseEntityAbstract $entity
	 *
	 * @throws CoreException
	 * @return HTML2PDF
	 */
	public static function getPDF(BaseEntityAbstract $entity, $orientation = 'P', $format = 'A4', $langue = 'en', $unicode = true, $encoding='UTF-8', $marges = array(5, 5, 5, 8))
	{
		self::$_templateDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		$class = get_class($entity);
		switch($class)
		{
			case 'Order': {
				$content = self::_order($entity);
				break;
			}
			case 'PurchaseOrder': {
				$content = self::_purchaseOrder($entity);
				break;
			}
			default: {
				throw new CoreException('NO such a function to covert entity:' . $class);
			}
		}
		$html2pdf = new HTML2PDF($orientation, $format, $langue, $unicode, $encoding, $marges);
		//$html2pdf->setModeDebug();
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($content);
		return $html2pdf;
	}
	/**
	 * converting a Order to be a pdf content string
	 *
	 * @param Order $entity
	 *
	 * @return string
	 */
	private static function _order(Order $entity)
	{
		$templateString = self::_getTemplateFile($entity, 'order.tpl');
		$values = self::_getDefaultValues();
		$values['orderNo'] = $entity->getOrderNo();
		return self::_bindData($templateString, $values);
	}
	/**
	 * getting the PurchaseOrder pdf string
	 *
	 * @param PurchaseOrder $entity
	 *
	 * @return string
	 */
	private static function _purchaseOrder(PurchaseOrder $entity)
	{
		$templateString = self::_getTemplateFile($entity, 'purchaseorder.tpl');
		$values = self::_getDefaultValues();
		return self::_bindData($templateString, $values);
	}
	/**
	 * Binding some data to the template string
	 *
	 * @param unknown $templateString
	 * @param array   $values
	 *
	 * @return string
	 */
	private static function _bindData($templateString, array $values = array())
	{
		return str_replace(array_map(create_function('$a', 'return "{" . $a . "}";'), array_keys($values)), array_values($values), $templateString);
	}
	/**
	 * Getting the default value for the templates
	 *
	 * @return multitype:string
	 */
	private static function _getDefaultValues()
	{
		return array(
			'imgDir' => ($imgDir = self::$_templateDir . DIRECTORY_SEPARATOR . 'images')
			,'logoUrl' => $imgDir . '/logo.png'
			,'headerSepUrl' => $imgDir . '/inv_sep.png'
		);
	}
	/**
	 * Getting the template file of entity's pdf
	 *
	 * @param BaseEntityAbstract $entity
	 * @param string             $fileName
	 *
	 * @return string
	 */
	private static function _getTemplateFile(BaseEntityAbstract $entity, $fileName)
	{
		$entityClass = get_class($entity);
		if(!isset(self::$_tempCache[$entityClass])) {
			$fileName = self::$_templateDir . $fileName;
			if(!is_file($fileName))
				throw new CoreException('System Error: no such a template file:' . $fileName);
			self::$_tempCache[$entityClass] = file_get_contents($fileName);
		}
		return self::$_tempCache[$entityClass];
	}
}