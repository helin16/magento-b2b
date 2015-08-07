<?php
abstract class APIClassAbstract
{
	/**
	 * Result code for success
	 * @var int
	 */
	const RESULT_CODE_SUCC = 0;
	/**
	 * Result code for fail
	 * @var int
	 */
	const RESULT_CODE_FAIL = 1;
	/**
	 * Result code for imcomplete
	 * @var int
	 */
	const RESULT_CODE_IMCOMPLETE = 2;
	/**
	 * Result code for other error
	 * @var int
	 */
	const RESULT_CODE_OTHER_ERROR = 3;
	
	protected function _getResponse(UDate $time)
	{
		$response = new SimpleXMLElement('<Response />');
		$response->addAttribute('Time', trim($time));
		$response->addAttribute('TimeZone',trim($time->getTimeZone()->getName()));
		return $response;
	}
	protected function addCData($name, $value, &$parent) {
		$child = $parent->addChild($name);
		if ($child !== NULL) {
			$child_node = dom_import_simplexml($child);
			$child_owner = $child_node->ownerDocument;
			$child_node->appendChild($child_owner->createCDATASection($value));
		}
		return $child;
	}
}