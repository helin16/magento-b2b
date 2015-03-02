<?php
/**
 * The CommentsDiv Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class CommentsDiv extends TTemplateControl
{
	public function onInit($param)
	{
		parent::onInit($param);

		$scriptArray = BPCPageAbstract::getLastestJS(get_class($this));
		foreach($scriptArray as $key => $value)
		{
			if(($value = trim($value)) !== '')
			{
				if($key === 'js')
					$this->getPage()->getClientScript()->registerScriptFile('CommentsDiv.Js', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('CommentsDiv.css', $this->publishAsset($value));
			}
		}
	}

	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack)
		{
			$js = 'CommentsDivJs.SAVE_BTN_ID = "' . $this->addCommentsBtn->getUniqueID() . '";';
			$this->getPage()->getClientScript()->registerEndScript('CommentsDivJS', $js);
		}
	}
	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function addComments($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->entityName) || ($entityName = trim($params->CallbackParameter->entityName)) === '')
				throw new Exception('System Error: EntityName is not provided!');
			if(!isset($params->CallbackParameter->entityId) || ($entityId = trim($params->CallbackParameter->entityId)) === '')
				throw new Exception('System Error: entityId is not provided!');
			if(!($entity = $entityName::get($entityId)) instanceof $entityName)
				throw new Exception('System Error: no such a entity exisits!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: invalid comments passed in!');

			$comment = Comments::addComments($entity, $comments, Comments::TYPE_NORMAL);
			$results['item'] = $comment->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}