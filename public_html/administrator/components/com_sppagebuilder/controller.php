<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.controller');

class SppagebuilderController extends JControllerLegacy
{
	function display( $cachable = false, $urlparams = false )
	{
		$input = JFactory::getApplication()->input;
		$input->set('view', $this->input->get('view', 'pages'));
		SppagebuilderHelper::addSubmenu('pages');
		parent::display($cachable, $urlparams);
	}

	public function export(){
		$input  = JFactory::getApplication()->input;

		$id = $input->get('id','','INT');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('text', 'alias')));
		$query->from($db->quoteName('#__sppagebuilder'));
		$query->where($db->quoteName('id') . ' = '. $db->quote($id));
		$db->setQuery($query);
		$result = $db->loadObject();

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=$result->alias.json");
		header("Content-Type: application/json");
		header("Content-Transfer-Encoding: binary ");

		echo $result->text;
		die();
	}
}
