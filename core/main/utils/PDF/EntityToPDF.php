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
				$conent = self::_order($entity);
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
		$values = array();
		return str_replace(array_keys($values), array_values($values), $templateString);
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
		$values = array();
		return str_replace(array_keys($values), array_values($values), $templateString);
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
		if(!isset(self::$_templateDir[get_class($entity)])) {
			$fileName = self::$_templateDir . $fileName;
			if(!is_file($fileName))
				throw new CoreException('System Error: no such a template file:' . $fileName);
			$content = file_get_contents($fileName);
			self::$_templateDir[get_class($entity)] = $content;
		}
		return self::$_templateDir[get_class($entity)];
	}
}