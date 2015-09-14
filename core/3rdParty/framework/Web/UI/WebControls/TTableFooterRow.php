<?php
/**
 * TTableFooterRow class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005-2014 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @package System.Web.UI.WebControls
 */

/**
 * Includes TTableRow class.
 */
Prado::using('System.Web.UI.WebControls.TTableRow');

/**
 * TTableFooterRow class.
 *
 * TTableFooterRow displays a table footer row.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package System.Web.UI.WebControls
 * @since 3.0.1
 */
class TTableFooterRow extends TTableRow
{
	/**
	 * @return string location of a row in a table. Always returns 'Footer'.
	 */
	public function getTableSection()
	{
		return 'Footer';
	}

	/**
	 * @param string location of a row in a table.
	 * @throws TInvalidOperationException if this method is invoked
	 */
	public function setTableSection($value)
	{
		throw new TInvalidOperationException('tablefooterrow_tablesection_readonly');
	}
}

