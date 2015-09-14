<?php
/**
 * This is the LastMemoPanel
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class LastMemoPanel extends TTemplateControl
{
	public $pageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		$scriptArray = BPCPageAbstract::getLastestJS(get_class($this));
		foreach($scriptArray as $key => $value) {
			if(($value = trim($value)) !== '') {
				if($key === 'js')
					$this->getPage()->getClientScript()->registerScriptFile('LastMemoPanelJs', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('LastMemoPanelCss', $this->publishAsset($value));
			}
		}
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack) {
			$js = 'if(typeof(LastMemoPanelJs) !== "undefined") {';
				$js .= 'LastMemoPanelJs.callbackIds = ' . json_encode(array(
						'addMemo' => $this->addMemoBtn->getUniqueID()
				)) . ';';
			$js .= '}';
			$this->getPage()->getClientScript()->registerEndScript('lmpJs', $js);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * Creating a new Memo
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 *
	 * @throws Exception
	 */
	public function addMemo($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->entity) || !isset($param->CallbackParameter->entityId) || ($entityName = trim($param->CallbackParameter->entity)) === '' || !($entity = $entityName::get(trim($param->CallbackParameter->entityId))) instanceof $entityName)
				throw new Exception('System Error: no entity provided for the Memo');
			if(!isset($param->CallbackParameter->data) || !isset($param->CallbackParameter->data->comments) || ($comments = trim($param->CallbackParameter->data->comments)) === '')
				throw new Exception('You can NOT create a Memo with a blank message.');
			$newComments = null;
			$entity->addComment($comments, Comments::TYPE_MEMO, '', $newComments);
			$results['item'] = $newComments->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
