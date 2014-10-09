<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
function updateCategory($categoryId = '')
{
	$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, 
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL), 
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), 
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
	$categories = $connector->getCategoryLevel($categoryId);
	if(count($categories) === 0)
		return;
	foreach($categories as $category)
	{
		$mageId = trim($category->category_id);
		var_dump( 'getting ProductCategory(mageId=' . $mageId . ')');
		$productCategory = ProductCategory::getByMageId($mageId);
		$category = $connector->catalogCategoryInfo($mageId);
		$description = isset($category->description) ? trim($category->description) : trim($category->name);
		if(!$productCategory instanceof ProductCategory)
		{
			var_dump( 'found empty category(mageId=' . $mageId . ')');
			$productCategory = ProductCategory::create(trim($category->name), $description, ProductCategory::getByMageId(trim($category->parent_id)), true, $mageId);
		}
		else
		{
			var_dump( 'found category(mageId=' . $mageId . ', ID=' . $productCategory->getId() . ')' );
			$productCategory->setName(trim($category->name))
				->setDescription($description)
				->setParent(ProductCategory::getByMageId(trim($category->parent_id)));
		}
		$productCategory->setActive(trim($category->is_active) === '1')
			->setIncludeInMenu(isset($category->include_in_menu) && trim($category->include_in_menu) === '1')
			->setIsAnchor(trim($category->is_anchor) === '1')
			->setUrlKey(trim($category->url_key))
			->save();

		updateCategory(trim($category->category_id));
	}
}

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
updateCategory();
echo 'DONE';