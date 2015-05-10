<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
abstract class ProductAgeingReport
{
	const PAGE_SIZE = DaoQuery::DEFAUTL_PAGE_SIZE;
	const DEBUG = true;
	
	public static function run()
	{
		try {
			Dao::beginTransaction();
			
			$start = self::_logMsg("== START: processing Product Ageing ==", __CLASS__, __FUNCTION__);
			
			self::_emptyProductAgeingLog();
			$productCount = 0;
			
			$products = self::_getProducts();
			if(self::DEBUG === true)
				self::_logMsg('ProductCount: ' . count($products), __CLASS__, __FUNCTION__);
			
			foreach ($products as $product)
			{
				$productCount ++;
				if(self::DEBUG === true)
					self::_logMsg('ProductId: ' . $product->getId(), __CLASS__, __FUNCTION__);
				$lastPurchase = self::_getLastPurchase($product);
				if($lastPurchase instanceof ProductQtyLog)
					self::_recordProductAgeingLog($lastPurchase);
			}
			
			$end = new UDate();
			self::_logMsg("== FINISHED: process product ageing, (productCount: " . $productCount . ", ProductAgeingLogCount: " . ProductAgeingLog::countByCriteria('active = 1') . ")", __CLASS__, __FUNCTION__);
			
			Dao::commitTransaction();
		} catch (Exception $e) {
			Dao::rollbackTransaction();
			echo $e->getTraceAsString();
		}
	}
	
	private static function _emptyProductAgeingLog()
	{
		if(self::DEBUG === true)
			self::_logMsg('Empty ALL content in ProductAgeingLog', __CLASS__, __FUNCTION__);
		ProductAgeingLog::deleteByCriteria('active = 1');
		return true;
	}
	private static function _getLastPurchase(Product $product, $pageSize = self::PAGE_SIZE)
	{
		$totalReceivedQty = 0;
		$lastPurchaseTime = new UDate();
		$totalPages = ceil(ProductQtyLog::countByCriteria('productId = ? and active = 1', array($product->getId())) / $pageSize);
		if(self::DEBUG === true)
			self::_logMsg('totalPages: ' . $totalPages, __CLASS__, __FUNCTION__);
		for($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++)
		{
			foreach (self::_getProductQtyLogs($product, $pageNumber, $pageSize) as $productQtyLog)
			{
				if(self::DEBUG === true)
				{
					self::_logMsg('pageNumber: ' . $pageNumber, __CLASS__, __FUNCTION__);
					self::_logMsg('pageSize: ' . $pageSize, __CLASS__, __FUNCTION__);
					self::_logMsg('ProductQtyLogId: ' . $productQtyLog->getId(), __CLASS__, __FUNCTION__);
				}
				$totalReceivedQty += $productQtyLog->getStockOnHandVar();
				if($totalReceivedQty >= $product->getStockOnHand())
				{
					return $productQtyLog;
				}
			}
		}
		return null;
	}
	private static function _recordProductAgeingLog(ProductQtyLog $productQtyLog, $comments = '')
	{
		return ProductAgeingLog::create($productQtyLog, $comments);
	}
	private static function _getProducts()
	{
		return Product::getAllByCriteria('pro.active = 1 and pro.stockOnHand > 0');
	}
	private static function _getProductQtyLogs(Product $product, $pageNumber, $pageSize = self::PAGE_SIZE)
	{
		return ProductQtyLog::getAllByCriteria('pql.active = 1 and pql.productId = ? and pql.stockOnHandVar > 0 and pql.type in (?, ?, ?)', array($product->getId(), ProductQtyLog::TYPE_PO, ProductQtyLog::TYPE_STOCK_MOVE_INTERNAL, ProductQtyLog::TYPE_STOCK_ADJ), true, $pageNumber, $pageSize, array('id' => 'desc'));
	}
	
	private static function _logMsg($msg, $className, $funcName) {
		$now = new UDate();
		echo trim($now) . '(UTC)::' . $className . '::' . $funcName . ': ' . $msg . "\n";
		return $now;
	}
}

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
ProductAgeingReport::run();