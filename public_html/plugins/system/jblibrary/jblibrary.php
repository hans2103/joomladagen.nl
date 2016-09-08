<?php
/**
* @id			$Id$
* @author 		Joomla Bamboo
* @package  	JB Library
* @copyright 	Copyright (C) 2006 - 2010 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  	GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
/** Thanks to onejQuery for being the inspiration of our unique jQuery function **/
/** ensure this file is being included by a parent file */
jimport( 'joomla.plugin.plugin' );
class plgSystemJblibrary extends JPlugin {
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$app = JFactory::getApplication();
        $this->_jqpath = '';
		//Dont Add Jquery in Admin
        if($app->isAdmin())return;
    }
    function onAfterInitialise() {
        if(JFactory::getApplication()->isAdmin())return;
		$doc =& JFactory::getDocument();
        $source = $this->params->get('source','google');    
        $jQueryVersion = $this->params->get('jQueryVersion','1.5.1');
		$noConflict = $this->params->get('noConflict',1);
        $ie6Warning = $this->params->get('ie6Warning',1); 
        $scrolltop = $this->params->get('scrollTop',1);
        $scrollStyle = $this->params->get('scrollStyle','dark');
        $scrollText = $this->params->get('scrollText','^ Back To Top');
		$resizeImage = $this->params->get('resizeImage','1');
		$riContent = $this->params->get('riContent','1');
		//$prettyPhoto = $this->params->get('prettyPhoto','1');
		$ppContent = $this->params->get('ppContent','1');
        $llSelector = $this->params->get('llSelector','img');
        $selectedMenus = $this->params->get('menuItems','');
        $lazyLoad = $this->params->get('lazyLoad',1);
        $itemid = JRequest::getInt('Itemid');
        if(!$itemid) $itemid = 1;
        if($llSelector == '') $llSelector = 'img';
        if (is_array($selectedMenus)){
            $menus = $selectedMenus;
        } elseif (is_string($selectedMenus) && $selectedMenus!=''){
            $menus[] = $selectedMenus;
        } elseif ($selectedMenus == ''){
            $menus[] = $itemid;
        }
        //module base
        $modbase = JURI::root (true).DS.'media'.DS.'plg_jblibrary'.DS;
    	$jsbase = $modbase.'jquery'.DS;
		$helperbase = JPATH_SITE.DS.'media'.DS.'plg_jblibrary'.DS.'helpers'.DS;
		$document =& JFactory::getDocument();
		if(in_array($itemid,$menus)){
		   	
			if(JFactory::getApplication()->get('jquery') == false) {
				
				// Tell other extensions jQuery has been loaded
				JFactory::getApplication()->set('jquery', true);// Load Mootools first
		   		JHTML::_(' behavior.mootools');
				if ($jQueryVersion == '1.6') {
						$this->_jqpath = 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js';
						$document->addScript($this->_jqpath); 
		   		}else{
					if ($source == 'local') {
						$this->_jqpath = $jsbase . 'jquery-'.$jQueryVersion.'.min.js';
					}
					if ($source == 'google') {
						$this->_jqpath = 'http://ajax.googleapis.com/ajax/libs/jquery/'.$jQueryVersion.'/jquery.min.js';
					}
					$document->addScript($this->_jqpath);	
				}
					if(!($jQueryVersion == "none") and $noConflict){
						$document->addScript($modbase.'jquery/jquery.noconflict.js');
					}
			}
   		}	   	
	   	//Detect Browser
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$browser = substr('$browser', 25, 8);
		//Load Scroll To Top if Not IE6
		if ($scrolltop and ($browser != 'MSIE 6.0')){
			if($scrollStyle == 'dark')
			{
				$document->addStyleDeclaration('#toTop {width:100px;z-index: 10;border: 1px solid #333; background:#121212; text-align:center; padding:5px; position:fixed; bottom:0px; right:0px; cursor:pointer; display:none; color:#fff;text-transform: lowercase; font-size: 0.7em;}');
			}
			if($scrollStyle == 'light')
			{
				$document->addStyleDeclaration('#toTop {width:100px;z-index: 10;border: 1px solid #eee; background:#f7f7f7; text-align:center; padding:5px; position:fixed; bottom:0px; right:0px; cursor:pointer; display:none;  color:#333;text-transform: lowercase; font-size: 0.8em;}');
			}
		}		
	
		//Load Lazy Load Script
		if ($lazyLoad){
			$document->addScript($jsbase. 'jquery.lazyload.js');
		}
	}
	

	function onAfterRender() {
		if(JFactory::getApplication()->isAdmin()){return;}	
		$jqRegex = $this->params->get('jqregex','([\/a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|min|pack)*?.js');
		$jqUnique = $this->params->get('jqunique',0);
		$stripCustom = $this->params->get('stripCustom',0);
		$customScripts = $this->params->get('customScripts','');
		$stripMootools = $this->params->get('stripMootools',0);
		$stripMootoolsMore = $this->params->get('stripMootoolsMore',0);
		$replaceMootools = $this->params->get('replaceMootools',0);
		$ie6Warning = $this->params->get('ie6Warning',1);
		$mootoolsPath = $this->params->get('mootoolsPath','http://ajax.googleapis.com/ajax/libs/mootools/1.2.4/mootools-yui-compressed.js');
		$scrolltop = $this->params->get('scrollTop',1);
		$lazyLoad = $this->params->get('lazyLoad',1);
		$scrollStyle = $this->params->get('scrollStyle','dark');
		$scrollTextTranslate = $this->params->get('scrollTextTranslate',1);
		$scrollText = $this->params->get('scrollText','^ Back To Top');
		$scrollText = $scrollTextTranslate ? JText::_($scrollText) : $scrollText;
		$llSelector = $this->params->get('llSelector','img');
		if($llSelector == '') $llSelector = 'img';
		$body =& JResponse::getBody();
		if($stripMootools){
			$body = preg_replace("#([\/a-zA-Z0-9_:\.-]*)mootools-core.js#", "", $body);
			$body = preg_replace("#([\/a-zA-Z0-9_:\.-]*)caption.js#", "", $body);
			$body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
		}
		if($stripMootoolsMore){
			$body = preg_replace("#([\/a-zA-Z0-9_:\.-]*)mootools-more.js#", "", $body);
			$body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
		}
		if($replaceMootools){
			if ($mootoolsPath != ''){$body = preg_replace("#([\/a-zA-Z0-9_:\.-]*)mootools-core.js#", "MTLIB", $body, 1);}
			$body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
			$body = preg_replace("#MTLIB#", $mootoolsPath, $body);
		}
		if($jqUnique && $jqRegex){
			if ($this->_jqpath != ''){$body = preg_replace("#$jqRegex#", "JQLIB", $body, 1);}
            $body = preg_replace("#$jqRegex#", "", $body);
            $body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
            $body = preg_replace("#jQuery\.noConflict\(\);#", "", $body);
            $body = preg_replace('#(<script src="JQLIB" type="text/javascript"></script>)#', '\\1<script type=\'text/javascript\'>jQuery.noConflict();</script>', $body);
            $body = preg_replace("#JQLIB#", $this->_jqpath, $body);
		}
		if($stripCustom && ($customScripts != '')){
			$customScripts = preg_split("/[\s,]+/", trim($customScripts));
			foreach($customScripts as $scriptName){
				$scriptRegex = "([\/a-zA-Z0-9_:\.-]*)".trim($scriptName);
				$body = preg_replace("#$scriptRegex#", "", $body);
			}
			$body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
		}
		//Detect Browser
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$browser = substr('$browser', 25, 8);
		$scripts = '';
		if ($ie6Warning and ($browser == 'MSIE 6.0')) { 
	   			$scripts = '
	   			<!--[if lte IE 6]>
	   			<script type="text/javascript" src="'.$jsbase.'jquery.badBrowser.js"></script> 
	   			 <![endif]-->
	   			 ';	
		 }
		//Load Scroll To Top if Not IE6
		if ($scrolltop and ($browser != 'MSIE 6.0')){
			$scripts .= '
			<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(function () {
				var scrollDiv = document.createElement("div");
				jQuery(scrollDiv).attr("id", "toTop").html("'.$scrollText.'").appendTo("body");    
				jQuery(window).scroll(function () {
						if (jQuery(this).scrollTop() != 0) {
							jQuery("#toTop").fadeIn();
						} else {
							jQuery("#toTop").fadeOut();
						}
					});
					jQuery("#toTop").click(function () {
						jQuery("body,html").animate({
							scrollTop: 0
						},
						800);
					});
				});
			});
			</script>
			';
		}
		if ($lazyLoad){
			$scripts .= '
			<script type="text/javascript">
			jQuery(document).ready(function(){jQuery("'.$llSelector.'").lazyload({ 
		    effect : "fadeIn" 
		    });
		});
		</script>
		';
		}
		
		$path= "media".DS."plg_jblibrary".DS."user";
		$files = JFolder::files($path, 'js', false, true);
		$files ? $result = count($files) : $result = 0;

		if ($result > 0) {	
			foreach($files as $file){
				$scripts .= '<script type="text/javascript" src="'.JURI::root (true).'/'.$file.'"></script>';	
			}
		}
			
		$body = str_replace ("</body>", $scripts."</body>", $body);
		JResponse::setBody($body);
		return true;
	 }
}
?>