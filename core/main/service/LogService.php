<?php
/**
 * LogService
 *
 * @package    Core
 * @subpackage Service
 * @author     lhe<helin16@gmail.com>
 *
 */
class LogService extends BaseServiceAbastract
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct("Log");
    }
    
    public function findGroupedLogs($pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE)
    {
//     	$sql = "select transId, created"
    }
}
?>
