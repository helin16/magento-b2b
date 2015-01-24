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
	public static function getPDF(BaseEntityAbstract $entity, $method = '')
	{
		$class = get_class($entity);
		switch($class)
		{
			case 'Order': {
				if(trim($method) === 'docket')
					$url = 'printdocket/order/' . $entity->getId() . '.html';
				else
					$url = 'print/order/' . $entity->getId() . '.html';
				break;
			}
			case 'PurchaseOrder': {
				$url = 'print/purchase/' . $entity->getId() . '.html';
				break;
			}
			default: {
				throw new CoreException('NO such a function to covert entity:' . $class);
			}
		}
		$url .= "?user=" . Core::getUser()->getUserName() . '&pass=' . Core::getUser()->getPassword();
		$command = '/usr/local/bin/wkhtmltopdf --disable-javascript "http://localhost/' . $url . '" ' . ($file = '/tmp/' . md5(new UDate()) . '.pdf');
		$output = '';
		exec($command, $output);
		if(!is_file($file))
			throw new Exception('Could NOT generate pdf @' . $file);
		return $file;
	}
}