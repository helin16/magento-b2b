<?php
class ConnectorException extends CoreException
{
	public function __construct($message)
	{
		parent::__construct($message);
		$this->code = '01';
	}
}