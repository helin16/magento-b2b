<?php
ini_set('memory_limit','1024M');
class ItemExport_Magento extends ExportAbstract
{
	protected static function _getData()
	{
		$now = new UDate();
		$now->modify('-1 day');
		$productPrices = ProductPrice::getAllByCriteria('updated >= :fromDate and updated < :toDate', array('fromDate' => $now->format('Y-m-d') . ' 00:00:00', 'toDate' => $now->format('Y-m-d') . '23:59:59'));
		
		$return = array();
		foreach($productPrices as $productPrice)
		{
			if(!($product = $productPrice->getProduct()) instanceof Product)
				continue;
			if(!isset($return[trim($product->getSku())])) {
				$return[trim($product->getSku())] = array(
						'store' => 'admin'
						,'websites' => 'base'
						,'attribute_set' => ''
						,'type' => 'simple'
						,'category_ids' => implode(',', array_map(create_function('$a', '$a->getMageId()'), $product->getCategories()))
						,'sku' => $product->getSku()
						,'has_options' => '0'
						,'name' => $product->getName()
						,'meta_title' => ''
						,'meta_description' => ''
						,'image' => ''
						,'small_image' => ''
						,'thumbnail' => ''
						,'url_key' => ''
						,'url_path' => ''
						,'custom_design' => ''
						,'page_layout' => 'No layout updates'
						,'options_container' => 'Block after Info Column'
						,'image_label' => ''
						,'small_image_label' => ''
						,'thumbnail_label' => ''
						,'country_of_manufacture' => ''
						,'msrp_enabled' => 'Use config'
						,'msrp_display_actual_price_type' => 'Use config'
						,'gift_message_available' => ''
						,'supplier' => ''                                          //TODO!!
						,'man_code' => ''                                           //TODO!!
						,'price'=> ''                                                //TODO!!
						,'special_price' => ''
						,'weight' => ''
						,'msrp' => ''
						,'manufacturer' => ($product->getManufacturer() instanceof Manufacturer ? $product->getManufacturer()->getName() : '')
						,'status' => (intval($product->getActive()) === 1 ? 'Enabled' : 'Disabled')
						,'is_recurring' => 'No'
						,'visibility' => 'Catalog, Search'
						,'tax_class_id' => 'Taxable Goods'
						,'all_ln_stock' =>  $product->getStatus()
						,'pc_sln_ssd' =>  ''
						,'hd_sln_interface' =>  ''
						,'description' =>  ''                                           //TODO!!
						,'short_description' =>  trim($product->getShortDescription())
						,'meta_keyword' =>  ''
						,'custom_layout_update' =>  ''
						,'videobox' =>  ''
						,'customtab' =>  ''
						,'customtabtitle' =>  'Features'
						,'shortparams' =>  ''
						,'special_from_date' =>  ''                                          //TODO!!
						,'special_to_date' =>  ''                                          //TODO!!
						,'news_from_date' =>  trim($product->getAsNewFromDate())
						,'news_to_date' =>  trim($product->getAsNewToDate())
						,'custom_design_from' =>  ''
						,'custom_design_to' =>  ''
						,'qty' =>  intval($product->getStockOnHand())
						,'min_qty' =>  0
						,'use_config_min_qty' =>  1
						,'is_qty_decimal' =>  0
						,'backorders' =>  0
						,'use_config_backorders' => 1
						,'min_sale_qty' => 1
						,'use_config_min_sale_qty' => 1
						,'max_sale_qty' => 0
						,'use_config_max_sale_qty' => 1
						,'is_in_stock' => 1
						,'low_stock_date' => ''
						,'notify_stock_qty' => 0
						,'use_config_notify_stock_qty' => 1
						,'manage_stock' => 0
						,'use_config_manage_stock' => 1
						,'stock_status_changed_auto' => 0
						,'use_config_qty_increments' => 1
						,'qty_increments' => 0
						,'use_config_enable_qty_inc' => 1
						,'enable_qty_increments' => 0
						,'is_decimal_divided' => 0
						,'stock_status_changed_automatically' => 0
						,'use_config_enable_qty_increments' => 1
						,'product_name' => trim($product->getName())
						,'store_id' => 0
						,'product_type_id' => 'simple'
						,'product_status_changed' => ''
						,'product_changed_websites' => ''
						,'reward_point_product' => ''
						,'mw_reward_point_sell_product' => ''
				);
			}
			$productPrice = new ProductPrice();
			if(trim($productPrice->getType()->getId()) === trim(ProductPriceType::ID_RRP)) {
				$return[trim($product->getSku())]['price'] = $productPrice->getPrice();
			} else if(trim($productPrice->getType()->getId()) === trim(ProductPriceType::ID_CASUAL_SPECIAL)) {
				$return[trim($product->getSku())]['special_price'] = $productPrice->getPrice();
				$return[trim($product->getSku())]['special_from_date'] = trim($productPrice->getStart());
				$return[trim($product->getSku())]['special_to_date'] = trim($productPrice->getEnd());
			}
		}
		return $return;
	}
	protected static function _getMailTitle()
	{
		return 'Product Exported for Magento on ' . trim(new UDate());
	}
	protected static function _getMailBody()
	{
		return 'Product Exported for Magento on ' . trim(new UDate());
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'product_magento_import_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}