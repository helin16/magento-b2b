<?php
class Task extends BaseEntityAbstract
{
	/**
	 * The entity name of the task is created from
	 *
	 * @var string
	 */
	private $fromEntityName;
	/**
	 * The id of the entity of the task is created from
	 *
	 * @var int
	 */
	private $fromEntityId;
	/**
	 * Task status
	 *
	 * @var TaskStatus
	 */
	protected $status;
	/**
	 * The user
	 *
	 * @var UserAccount
	 */
	protected $technician;
	/**
	 * The dueDate of the Task
	 *
	 * @var UDate
	 */
	private $dueDate;
	/**
	 * The instruction of the Task
	 *
	 * @var string
	 */
	private $instruction;
	/**
	 * Getter for fromEntityName
	 *
	 * @return string
	 */
	public function getFromEntityName()
	{
	    return $this->fromEntityName;
	}
	/**
	 * Setter for fromEntityName
	 *
	 * @param string $value The fromEntityName
	 *
	 * @return Task
	 */
	public function setFromEntityName($value)
	{
	    $this->fromEntityName = $value;
	    return $this;
	}
	/**
	 * Getter for fromEntityId
	 *
	 * @return string
	 */
	public function getFromEntityId()
	{
	    return $this->fromEntityId;
	}
	/**
	 * Setter for fromEntityId
	 *
	 * @param int $value The fromEntityId
	 *
	 * @return Task
	 */
	public function setFromEntityId($value)
	{
	    $this->fromEntityId = $value;
	    return $this;
	}
	/**
	 * Getter for status
	 *
	 * @return TaskStatus
	 */
	public function getStatus()
	{
		$this->loadManyToOne(status);
	    return $this->status;
	}
	/**
	 * Setter for status
	 *
	 * @param TaskStatus $value The status
	 *
	 * @return Task
	 */
	public function setStatus(TaskStatus $value)
	{
	    $this->status = $value;
	    return $this;
	}
	/**
	 * Getter for technician
	 *
	 * @return UserAccount
	 */
	public function getTechnician()
	{
		$this->loadManyToOne('technician');
	    return $this->technician;
	}
	/**
	 * Setter for technician
	 *
	 * @param UserAccount $value The technician
	 *
	 * @return Task
	 */
	public function setTechnician(UserAccount $value = null)
	{
	    $this->technician = $value;
	    return $this;
	}
	/**
	 * Getter for dueDate
	 *
	 * @return UDate
	 */
	public function getDueDate()
	{
	    return new UDate(trim($this->dueDate));
	}
	/**
	 * Setter for dueDate
	 *
	 * @param unkown $value The dueDate
	 *
	 * @return Task
	 */
	public function setDueDate($value)
	{
	    $this->dueDate = $value;
	    return $this;
	}
	/**
	 * Getter for instruction
	 *
	 * @return string
	 */
	public function getInstruction()
	{
	    return $this->instruction;
	}
	/**
	 * Setter for instruction
	 *
	 * @param unkown $value The instruction
	 *
	 * @return Task
	 */
	public function setInstruction($value)
	{
	    $this->instruction = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) === '' && (!$this->getStatus() instanceof TaskStatus))
			$this->setStatus(TaskStatus::get(TaskStatus::ID_NEW));
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 't');

		DaoMap::setStringType('fromEntityName','varchar', 50);
		DaoMap::setIntType('fromEntityId');
		DaoMap::setManyToOne('status', 'TaskStatus', 't_st');
		DaoMap::setManyToOne('technician', 'UserAccount', 't_tech', true);
		DaoMap::setDateType('dueDate');
		DaoMap::setStringType('instructions', 'text');

		parent::__loadDaoMap();

		DaoMap::createIndex('fromEntityName');
		DaoMap::createIndex('fromEntityId');
		DaoMap::createIndex('dueDate');
		DaoMap::commit();
	}
}