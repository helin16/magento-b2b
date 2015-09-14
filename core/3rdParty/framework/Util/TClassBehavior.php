<?php
/**
 * TClassBehavior class file.
 *
 * @author Brad Anderson <javalizard@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2008-2011 Pradosoft
 * @license http://www.pradosoft.com/license/
 */

/**
 * TClassBehavior is a convenient base class for whole class behaviors.
 * @author Brad Anderson <javalizard@gmail.com>
 * @package System.Util
 * @since 3.2.3
 */
class TClassBehavior extends TComponent implements IClassBehavior
{

	/**
	 * Attaches the behavior object to the component.
	 * @param TComponent the component that this behavior is to be attached to.
	 */
	public function attach($component)
	{
	}

	/**
	 * Detaches the behavior object from the component.
	 * @param TComponent the component that this behavior is to be detached from.
	 */
	public function detach($component)
	{
	}
}