<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appmodel.php');
use Dompdf\Dompdf;
class J2StoreModelAppPDFInvoices extends J2StoreAppModel
{
	public $_element = 'app_pdfinvoices';

	public function getParams(){
		$plugin_data = JPluginHelper::getPlugin('j2store', $this->_element);
		$params = new JRegistry;
		$params->loadString($plugin_data->params);
		return $params;
	}

	public function getAppId(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')->from('#__extensions')
			->where('folder='.$db->q('j2store'))
			->where('type='.$db->q('plugin'))
			->where('element='.$db->q($this->_element));
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function createDomPDF($order, $direct_output=false) {
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$app = JFactory::getApplication();
		$this->initialisePath();
		include_once JPATH_LIBRARIES.'/f0f/include.php';

		if(!defined('F0F_INCLUDED'))
		{
			JError::raiseError('500','F0F IS NOT INSTALLED');
		}


		$base_path = JPATH_SITE.'/templates/'.$app->getTemplate().'/css/';

		$template_html = $this->processHtml($order);

		$dompdf = $this->initDomPdf();
		if($dompdf === false) return;

		//set options
		$dompdfOptions = $dompdf->getOptions();
		$dompdfOptions->set('enable_remote', true);
		$dompdf->setBasePath($base_path.'template.css');
		$dompdf->setPaper("A4", "portrait");
		$dompdf->loadHtml($template_html);
		$dompdf->render();
		$output = $dompdf->output();

		//check if already the same invoice exists. in that case, we have to delete
		$file = $this->getInvoicePath().DIRECTORY_SEPARATOR.$this->getInvoiceFileName($order);
		if(JFile::exists($file)) {
			JFile::delete($file);
		}
		$ret = JFile::write($file, $output);

		// to render the pdf
		if( ($app->isSite() && $app->input->getString('profileTask') =='createPdf') || ($app->isAdmin() && $app->input->getString('appTask') == 'invoicePdf' )){
			$dompdf->stream($this->getInvoiceFileName($order), array("Attachment" => false));
			$app->close();
		}
	}

	public function processHtml($order) {
		$text = '';
		$text .= J2Store::invoice()->getFormatedInvoice($order);
		$template_html = $this->processInlineImages($text);
		$params = $this->getParams();
		if (function_exists('tidy_repair_string'))
		{
			$tidyConfig = array(
				'bare'							=> 'yes',
				'clean'							=> 'yes',
				'drop-proprietary-attributes'	=> 'yes',
				'clean'							=> 'yes',
				'output-html'					=> 'yes',
				'show-warnings'					=> 'no',
				'ascii-chars'					=> 'no',
				'char-encoding'					=> 'utf8',
				'input-encoding'				=> 'utf8',
				'output-bom'					=> 'no',
				'output-encoding'				=> 'utf8',
				'force-output'					=> 'yes',
				'tidy-mark'						=> 'no',
				'wrap'							=> 0,
			);
			$repaired = tidy_repair_string($template_html, $tidyConfig, 'utf8');
			if ($repaired !== false)
			{
				$template_html = $repaired;
			}
		}
		$template_html = preg_replace('~>\s+<~', '><', $template_html);
		$template_html = mb_convert_encoding($template_html, 'HTML-ENTITIES', 'UTF-8');
		$font_family = $params->get('font_family','');
		$code = '';
		if($font_family){
			$code = '*{font-family:Times New Roman;'.$font_family.'}';
		}
		$html ='
		<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>'.$order->order_id.'</title>
		<style type="text/css">
		@page {
			margin: 0;
			'.$code.'
		}
		.pages {
			margin: .5in;
			page-break-inside: avoid;
		}
		table{
   			border-collapse: collapse; width: 100%;
		}
		</style>';
		$custom_css = $params->get('custom_css', '');
		if(!empty($custom_css)) {
			$html .= '<style type="text/css">'.$custom_css.'</style>';
		}
		$html .= '
		</head>
		<body>
		<div class="pages first-page">';

		$html .= $template_html;
		$html .='</div>';
		$html .='</body></html>';
		return $html;

	}

	public function processInlineImages($templateText) {

		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link
					/*$rootURL = rtrim(JURI::base(),'/');
					$subpathURL = JURI::base(true);
					if(!empty($subpathURL) && ($subpathURL != '/')) {
						$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
					}
					$subpath = str_replace($rootURL, '', $url);
					$temp .= JPATH_ROOT.'/'.ltrim($subpath,'/');*/

					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						//$url = JPATH_ROOT.'/'.ltrim($url,'/');
						$sub_domain = JUri::root(true);
						$url = str_replace($sub_domain,'',$url);
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						$temp .= $url;
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							//$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						//$temp .= 'cid:img'.$imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}
		return $templateText;
	}

	protected function initDomPdf()
	{
		require_once JPATH_LIBRARIES .'/dompdf/autoload.inc.php';
		$file = JPATH_LIBRARIES .'/dompdf/src/Dompdf.php';

		if (!JFile::exists($file))
		{
			return false;
		}

		if (!defined('DOMPDF_ENABLE_REMOTE'))
		{
			define('DOMPDF_ENABLE_REMOTE', true);
		}

		if (!defined('DOMPDF_ENABLE_HTML5PARSER '))
		{
			define('DOMPDF_ENABLE_HTML5PARSER ', true);
		}

		if (!defined('DOMPDF_ENABLE_CSS_FLOAT'))
		{
			define('DOMPDF_ENABLE_CSS_FLOAT', true);
		}

		//set the font cache directory to Joomla's tmp directory
		$config = JFactory::getConfig();

		if (!defined('DOMPDF_FONT_CACHE'))
		{
			define('DOMPDF_FONT_CACHE', $config->get('tmp_path'));
		}
		require_once($file);
		return new Dompdf();
	}

	public function initialisePath() {
		$path = JPATH_SITE.'/media/j2store/invoices';
		if(!JFolder::exists($path)) {
			if(!JFolder::create($path)) {
				//if this fails, the create the pdf in the site's temp directory
				$tmp_path = JFactory::getConfig()->get('tmp_path');
				$path = JPATH_SITE.DIRECTORY_SEPARATOR.$tmp_path;
			}
		}
		$this->setInvoicePath($path);
	}

	public function setInvoicePath($path) {
		$this->_invoice_path = $path;
	}

	public function getInvoicePath() {
		return $this->_invoice_path;
	}

	public function getPrefix() {

		$prefix = $this->getParams()->get('file_prefix', 'invoice');
		return F0FInflector::underscore($prefix);
	}

	public function getInvoiceFileName($order) {
		$prefix = $this->getPrefix();
		$invoice_number = $order->getInvoiceNumber();
		$name = $prefix.'_'.F0FInflector::underscore($invoice_number).'.pdf';
		return $name;
	}

}