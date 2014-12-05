<?php
/**
 * The BPCPage Page Abstract
 * 
 * @package    Web
 * @subpackage Class
 * @author     lhe<helin16@gmail.com>
 */
abstract class BPCPageAbstract extends TPage 
{
	/**
	 * The menu item identifier
	 * 
	 * @var string
	 */
	public $menuItem = '';
	/**
	 * constructor
	 */
	public function __construct()
	{
	    parent::__construct();
	     if(!Core::getUser() instanceof UserAccount && get_class($this) !== 'LoginController')
	    	$this->getResponse()->Redirect('/login.html');
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		if(get_class($this) !== 'LoginController')
			$this->getForm()->setAttribute('onSubmit', 'return false;');
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
	    if(!$this->IsPostBack && !$this->IsCallback)
	    {
	        $this->getClientScript()->registerEndScript('pageJs', $this->_getEndJs());
	    }
	}
	/**
	 * Getting The end javascript
	 * 
	 * @return string
	 */
	protected function _getEndJs() 
	{
	    $js = 'if(typeof(PageJs) !== "undefined"){';
	    	$js .= 'var pageJs = new PageJs(); ';
		$js .= '}';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see TPage::render()
	 */
	public function onPreInit($param)
	{
	    parent::onPreInit($param);
	    $this->_Load3rdPartyJs();
	    $this->getClientScript()->registerPradoScript('ajax');
	    $this->_loadPageJsClass();
        $cScripts = self::getLastestJS(get_class($this));
	    if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
	        $this->getPage()->getClientScript()->registerScriptFile('pageJs', $this->publishAsset($lastestJs));
	    if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
	        $this->getPage()->getClientScript()->registerStyleSheetFile('pageCss', $this->publishAsset($lastestCss));
	}
	/**
	 * loading the page js class files
	 */
	protected function _loadPageJsClass()
	{
		$cScripts = self::getLastestJS(__CLASS__);
		if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
			$this->getPage()->getClientScript()->registerScriptFile('BPCPageJs', $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $lastestJs));
		if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
			$this->getPage()->getClientScript()->registerStyleSheetFile('BPCPageCss', $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $lastestCss));
	    return $this;
	}
	private function _Load3rdPartyJs()
	{
		$clientScript = $this->getPage()->getClientScript();
		$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jQuery' . DIRECTORY_SEPARATOR);
		$clientScript->registerHeadScriptFile('jQuery', $folder . '/jquery-2.1.1.min.js');
		
		$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR);
		$clientScript->registerHeadScriptFile('Bootstrap.js', $folder . '/js/bootstrap.min.js');
		$clientScript->registerStyleSheetFile('Bootstrap.css', $folder . '/css/bootstrap.min.css');
		$clientScript->registerStyleSheetFile('Bootstrap.theme.css', $folder . '/bootstrap-theme.min.css');
		
		$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'FontAwesome' . DIRECTORY_SEPARATOR);
		$clientScript->registerStyleSheetFile('Awesome.font.css', $folder .  '/css/font-awesome.min.css');
	}
	/**
	 * Getting the lastest version of Js and Css under the Class'file path
	 * 
	 * @param string $className The class name
	 * 
	 * @return multitype:string
	 */
	public static function getLastestJS($className)
	{
	    $array = array('js' => '', 'css' => '');
	    try
	    {
	        //loading controller.js
	        $class = new ReflectionClass($className);
	        $fileDir = dirname($class->getFileName()) . DIRECTORY_SEPARATOR;
	        if (is_dir($fileDir))
	        {
	            //loop through the directory to find the lastes js version or css version
	            $lastestJs = $lastestJsVersionNo = $lastestCss = $lastestCssVersionNo = '';
	            foreach(glob($fileDir . '*.{js,css}', GLOB_BRACE) as $file)
	            {
                    preg_match("/^" . $className . "\.([0-9]+\.)?(js|css)$/i", basename($file), $versionNo);
                    if (isset($versionNo[0]) && isset($versionNo[1]) && isset($versionNo[2]))
                    {
                        $type = trim(strtolower($versionNo[2]));
                        $version = str_replace('.', '', trim($versionNo[1]));
                        if ($type === 'js') //if loading a javascript
                        {
                            if ($lastestJs === '' || $version > $lastestJsVersionNo)
                            $array['js'] = trim($versionNo[0]);
                        }
                        else if ($type === 'css')
                        {
                            if ($lastestCss === '' || $version > $lastestCssVersionNo)
                            $array['css'] = trim($versionNo[0]);
                        }
                    }
	            }
	        }
	        unset($className, $class, $fileDir, $lastestJs, $lastestJsVersionNo, $lastestCss, $lastestCssVersionNo);
	    }
	    catch(Exception $e)
	    {
	        //we are not doing anything if we failed here!
	    }
	    return $array;
	}
	/**
	 * Getting the 404 page
	 * 
	 * @param string $title   The title of the page
	 * @param string $content The html code content
	 * 
	 * @return string The html code of the page
	 */
	public static function show404Page($title, $content)
	{
		header("HTTP/1.0 404 Not Found");
		$html = "<h1>$title</h1>";
		$html .= $content;
		return $html;
	}
}
?>