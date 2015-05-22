<?php
class Task extends BaseEntityAbstract
{
	const DUE_DATE_PERIOD = '+4 day';
	/**
	 * The entity name of the task is created from
	 *
	 * @var string
	 */
	private $fromEntityName = '';
	/**
	 * The id of the entity of the task is created from
	 *
	 * @var int
	 */
	private $fromEntityId = 0;
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
	private $instructions;
	/**
	 * The customer of the task
	 *
	 * @var Customer
	 */
	protected $customer;
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
		$this->loadManyToOne('status');
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
	public function getInstructions()
	{
	    return $this->instructions;
	}
	/**
	 * Setter for instruction
	 *
	 * @param unkown $value The instruction
	 *
	 * @return Task
	 */
	public function setInstructions($value)
	{
	    $this->instructions = $value;
	    return $this;
	}
	/**
	 * Getter for customer
	 *
	 * @return Customer
	 */
	public function getCustomer()
	{
		$this->loadManyToOne('customer');
	    return $this->customer;
	}
	/**
	 * Setter for customer
	 *
	 * @param Customer $value The customer
	 *
	 * @return Task
	 */
	public function setCustomer($value)
	{
	    $this->customer = $value;
	    return $this;
	}
	/**
	 * Getting the from entity
	 *
	 * @return BaseEntityAbstract
	 */
	public function getFromEntity()
	{
		if(($entityClass = trim($this->getFromEntityName())) === '')
			return null;
		return $entityClass::get(trim($this->getFromEntityId()));
	}
	/**
	 * checking whether we can do the action
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 * @return Task
	 */
	private function _preActionCheck(UserAccount $user)
	{
		if(trim($this->getId()) === '')
			throw new EntityException('Please save the task before you do any actions to the task');
		if(!($status = $this->getStatus()) instanceof TaskStatus || in_array(intval($this->getStatus()->getId()), TaskStatus::getClosedStatusIds()))
			throw new EntityException('Can NOT Take Task(' . $this->getId() . ') is at status:' . ($status instanceof TaskStatus ? $this->getStatus()->getName() : ''));
		if($this->getTechnician() instanceof UserAccount) {
			if($user->getId() === $this->getTechnician()->getId())
				return $this;
			throw new EntityException('Can NOT Action on a Task(' . $this->getId() . ') owned by tech:' . $this->getTechnician()->getPerson()->getFullName());
		}
		return $this;
	}
	/**
	 * Take this task into a tech
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 */
	public function take(UserAccount $user, $comments = '')
	{
		$this->_preActionCheck($user)
			->setTechnician($user)
			->setStatus(TaskStatus::get(TaskStatus::ID_OPEN))
			->save();
		if(trim($comments) !== '')
			$this->addComment($comments, Comments::TYPE_WORKSHOP);
		return $this;
	}
	/**
	 * start this task into a tech
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 */
	public function start(UserAccount $user, $comments = '')
	{
		$this->_preActionCheck($user)
			->setStatus(TaskStatus::get(TaskStatus::ID_IN_PROGRESS))
			->save();
		if(trim($comments) !== '')
			$this->addComment($comments, Comments::TYPE_WORKSHOP);
		return $this;
	}
	/**
	 * release this task into a tech
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 */
	public function release(UserAccount $user, $comments = '')
	{
		$this->_preActionCheck($user)
			->setTechnician(null)
			->setStatus(TaskStatus::get(TaskStatus::ID_OPEN))
			->save();
		if(trim($comments) !== '')
			$this->addComment($comments, Comments::TYPE_WORKSHOP);
		return $this;
	}
	/**
	 * finish this task into a tech
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 */
	public function finish(UserAccount $user, $comments = '')
	{
		$this->_preActionCheck($user)
			->setStatus(TaskStatus::get(TaskStatus::ID_FINISHED))
			->save();
		if(trim($comments) !== '')
			$this->addComment($comments, Comments::TYPE_WORKSHOP);
		return $this;
	}
	/**
	 * finish this task into a tech
	 *
	 * @param UserAccount $user
	 *
	 * @throws EntityException
	 */
	public function onHold(UserAccount $user, $comments = '')
	{
		$this->_preActionCheck($user)
			->setStatus(TaskStatus::get(TaskStatus::ID_ON_HOLD))
			->save();
		if(trim($comments) !== '')
			$this->addComment($comments, Comments::TYPE_WORKSHOP);
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
		if(!$this->isJsonLoaded($reset))
		{
			$array['customer'] = $this->getCustomer()->getJson();
			$array['fromEntity'] = $this->getFromEntity() instanceof BaseEntityAbstract ? $this->getFromEntity()->getJson() : array();
			$array['technician'] = $this->getTechnician() instanceof UserAccount ? $this->getTechnician()->getJson() : array();
			$array['status'] = $this->getStatus() instanceof TaskStatus ? $this->getStatus()->getJson() : array();
			$array['createdBy'] = $this->getCreatedBy() instanceof UserAccount ? $this->getCreatedBy()->getJson() : array();
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) === '') {
			if(!$this->status instanceof TaskStatus)
				$this->setStatus(TaskStatus::get(TaskStatus::ID_NEW));
			if(trim($this->getDueDate()) === trim(UDate::zeroDate()))
				$this->setDueDate(UDate::now()->modify(self::DUE_DATE_PERIOD));
		} else {
			$changed = array();
			$origTech = $origCustomer = $origOrder = $origStatus = null;
			$origTask = Task::get($this->getId());
			if((($customer = $this->getCustomer()) instanceof Customer && !($origCustomer = $origTask->getCustomer()) instanceof Customer) || (!$customer instanceof Customer && $origCustomer instanceof Customer) || ($customer instanceof Customer && $origCustomer instanceof Customer && $customer->getId() !== $origCustomer->getId()))
				$changed[] = 'Customer Changed["' . ($origCustomer instanceof Customer ? $origCustomer->getName() : '')  . '" => "' .  ($customer instanceof Customer ? $customer->getName() : '') . '"]';
			if((($tech = $this->getTechnician()) instanceof UserAccount && !($origTech = $origTask->getTechnician()) instanceof UserAccount) || (!$tech instanceof UserAccount && $origTech instanceof UserAccount) || ($tech instanceof UserAccount && $origTech instanceof UserAccount && $tech->getId() !== $origTech->getId()))
				$changed[] = 'Technician Changed["' . ($origTech instanceof UserAccount ? $origTech->getPerson()->getFullName() : '')  . '" => "' .  ($tech instanceof UserAccount ? $tech->getPerson()->getFullName() : '') . '"]';
			if((($order = $this->getFromEntity()) instanceof Order && !($origOrder = $origTask->getFromEntity()) instanceof Order) || (!$order instanceof Order && $origOrder instanceof Order) || ($order instanceof Order && $origOrder instanceof Order && $order->getId() !== $origOrder->getId()))
				$changed[] = 'Order Changed["' . ($origOrder instanceof Order ? $origOrder->getOrderNo() : '')  . '" => "' .  ($order instanceof Order ? $order->getOrderNo() : '') . '"]';
			if((($status = $this->getStatus()) instanceof TaskStatus && !($origStatus = $origTask->getStatus()) instanceof TaskStatus) || (!$status instanceof TaskStatus && $origStatus instanceof TaskStatus) || ($status instanceof TaskStatus && $origStatus instanceof TaskStatus && $status->getId() !== $origStatus->getId()))
				$changed[] = 'Status Changed["' . ($origStatus instanceof TaskStatus ? $origStatus->getName() : '')  . '" => "' .  ($status instanceof TaskStatus ? $status->getName() : '') . '"]';
			if(count($changed) > 0)	{
				$this->addComment(implode(', ', $changed), Comments::TYPE_SYSTEM);
			}
		}
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
		DaoMap::setManyToOne('customer', 'Customer', 't_cust');
		DaoMap::setManyToOne('technician', 'UserAccount', 't_tech', true);
		DaoMap::setDateType('dueDate');
		DaoMap::setStringType('instructions', 'text');

		parent::__loadDaoMap();

		DaoMap::createIndex('fromEntityName');
		DaoMap::createIndex('fromEntityId');
		DaoMap::createIndex('dueDate');
		DaoMap::commit();
	}
	/**
	 * creating a task
	 *
	 * @param UDate       $dueDate
	 * @param string      $instructions
	 * @param UserAccount $tech
	 *
	 * @return Task
	 */
	public static function create(Customer $customer, UDate $dueDate = null, $instructions = '', UserAccount $tech = null, BaseEntityAbstract $fromEntity = null)
	{
		$task = new Task();
		$task->setDueDate($dueDate)
			->setCustomer($customer)
			->setInstructions($instructions = trim($instructions))
			->setTechnician($tech);
		if($fromEntity instanceof BaseEntityAbstract) {
			$task->setFromEntityId($fromEntity->getId())
				->setFromEntityName(get_class($fromEntity));
		}
		$task->save()
			->addComment('Task created with(Customer: ' . $customer->getName() . ', DueDate:' . $dueDate . '(UTC), ' . ($instructions === '' ? 'no insturctions' : ' some instructions ') . ', tech ' . ($tech instanceof UserAccount ? $tech->getPerson() : ''));
		return $task;
	}
}