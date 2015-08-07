<?php
/**
 * The soap product server for product services
 *
 * @package    Web
 * @subpackage Class
 * @author     lhe<helin16@gmail.com>
 */
class APIProduct extends APIClassAbstract
{
	/**
	 * create product
	 * 
	 * @param string $sku			The sku of product
	 * @param string $name			The name of product
	 * @param array $categoryPaths	The category paths of product (e.g. $categories = array(array('cate2', 'cate3'), array('cate4', 'cate5', 'cate6'));
	 * @param string $mageProductId //TODO
	 * @param string $isFromB2B
	 * @param string $shortDescr
	 * @param string $fullDescr
	 * @param string $brandName
	 * 
	 * @throws Exception
	 * @return string
	 * @soapmethod
	 */
	public function createProduct($sku, $name, $categoryPaths = array(), $mageProductId = '', $isFromB2B = false, $shortDescr = '', $fullDescr = '', $brandName = '')
	{
		$response = $this->_getResponse(UDate::now());
		try {
			Dao::beginTransaction();
			Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)); //TODO
			
			if(Product::getBySku(trim($sku)) instanceof Product)
				throw new Exception('sku "' . $sku .'" already exists');
			$categories = array();
			if(is_array($categoryPaths))
			{
				foreach ($categoryPaths as $categoryPath)
				{
					$parentCategory = null;
					foreach ($categoryPath as $categoryName)
					{
						if(count($i = ProductCategory::getAllByCriteria('name = ?', array(trim($categoryName)), true , 1, 1)) >0)
							$categories[$i[0]->getId()] = ($category = $i[0]);
						else 
						{
							$category = ProductCategory::create(trim($categoryName), trim($categoryName), $parentCategory);
							$categories[$category->getId()] = $category;
						}
						$parentCategory = $category;
					}
				}
			}
			// create product
			$product = Product::create(trim($sku), trim($name)); // TODO
			foreach ($categories as $category)
				$product->addCategory($category);
			$response['status'] = self::RESULT_CODE_SUCC;
			$response->addChild('product', json_encode($product->getJson()));
			Dao::commitTransaction();
		} catch (Exception $e) {
			Dao::rollbackTransaction();
			$response['status'] = self::RESULT_CODE_FAIL;
			$response->addChild('error', $e->getMessage());
		}
		return trim($response->asXML());
	}
	/**
	 * get product info by sku
	 * 
	 * @param string $sku
	 * 
	 * @return string
	 * @soapmethod
	 */
	public function getProductBySku($sku)
	{
		$response = $this->_getResponse(UDate::now());
		try {
			$sku = trim($sku);
			Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)); //TODO
			$obj =  Product::getBySku($sku);
			if($obj instanceof Product)
			{
				$response['status'] = self::RESULT_CODE_SUCC;
				$response->addChild('product', json_encode($obj->getJson()));
				return trim($response->asXML());
			}
			$response['status'] = self::RESULT_CODE_FAIL;
			$response->addChild('error', 'product with sku "' . $sku . '" does not exist.');
		} catch (Exception $e) {
			Dao::rollbackTransaction();
			$response['status'] = self::RESULT_CODE_FAIL;
			$response->addChild('error', $e->getMessage());
		}
		return trim($response->asXML());
	}
	/**
	 * get category info by magento-b2b productCategory id
	 * 
	 * @param string $systemid
	 * 
	 * @return string
	 * @soapmethod
	 */
	public function getCategory($systemid)
	{
		$response = $this->_getResponse(UDate::now());
		try {
			$systemid = intval(trim($systemid));
			Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)); //TODO
			$obj =  ProductCategory::get($systemid);
			if($obj instanceof ProductCategory)
			{
				$response['status'] = self::RESULT_CODE_SUCC;
				$response->addChild('category', json_encode($obj->getJson()));
				return trim($response->asXML());
			}
			$response['status'] = self::RESULT_CODE_FAIL;
			$response->addChild('error', 'category with system id "' . $systemid . '" does not exist.');
		} catch (Exception $e) {
			Dao::rollbackTransaction();
			$response['status'] = self::RESULT_CODE_FAIL;
			$response->addChild('error', $e->getMessage());
		}
		return trim($response->asXML());
	}
}