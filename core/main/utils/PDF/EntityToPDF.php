<?php
/**
 * an utils to convert an Entity to PDF
 * @author lhe
 *
 */
class EntityToPDF
{
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
	public static function getPDF(BaseEntityAbstract $entity)
	{
		$class = get_class($entity);
		switch($class)
		{
			case 'Order': {
				$url = 'print/order/' . $entity->getId() . '.html';
				break;
			}
			case 'PurchaseOrder': {
				break;
			}
			default: {
				throw new CoreException('NO such a function to covert entity:' . $class);
			}
		}
		$url .= "?user=" . Core::getUser()->getUserName() . '&pass=' . Core::getUser()->getPassword();
		$command = 'wkhtmltopdf --disable-javascript http://localhost/' . $url . ' /tmp/' . md5(new UDate()) . '.pdf';
		var_dump($command);
		$output = exec($command);
		var_dump($output);
	}
}