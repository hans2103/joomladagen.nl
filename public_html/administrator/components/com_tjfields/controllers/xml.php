<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - http://www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
jimport( 'joomla.filesystem.file' );

/**
 * Field controller class.
 */
class TjfieldsControllerXml extends JControllerForm
{

	function __construct() {
		parent::__construct();
	}

	function generateXml()
	{
		//echo 'hola';

		$db     = JFactory::getDbo();

		$query  = "SELECT * FROM
		#__tjfields_fields";

		$db->setQuery($query);
		$fields = $db->loadObjectList();

		//print_r($fields);

		$newXML = new SimpleXMLElement("<form></form>");
		//$newXML->addAttribute('newsPagePrefix', 'value goes here');
		$fieldset = $newXML->addChild('fieldset');

		//~ [id] => 7
		//~ [label] => first name
		//~ [name] => first_name
		//~ [type] => text
		//~ [state] => 1
		//~ [required] => 1
		//~ [placeholder] =>
		//~ [created_by] => 41
		//~ [min] =>
		//~ [max] =>
		//~ [description] =>
		//~ [js_function] =>
		//~ [validation_class] =>
		//~ [ordering] => 2
		//~ [client] => com_jticketing
		//~ [client_type] => Event
		//~ [group_id] => 0

		 //~ type="text"
        //~ name="myTextField"
        //~ id="myTextField"
        //~ label="MY TEXT FIELD LABEL"
        //~ description="MY TEXT FIELD DESCRIPTION"
        //~ size="80"
        //~ maxLength="255"

		foreach($fields as $f)
		{
			$field = $fieldset->addChild('field');

			$field->addAttribute('name', $f->name);
			$field->addAttribute('type', $f->type);
			$field->addAttribute('label', $f->label);
			$field->addAttribute('description', $f->description);
			//$field->addAttribute('placeholder', $f->placeholder);

			//$field->addAttribute('class', $f->class);
			//$field->addAttribute('default', $f->id);
			//$field->addAttribute('readonly', $f->id);

		}

		$filePath = JPATH_SITE . DS . 'components/com_jticketing/models/forms/test.xml';
		$content  = '';

		if(!JFile::exists($filePath))
		{
			JFile::write($filePath, $content);
		}

		$newXML->asXML($filePath);//->asXML();

		//print_r($newXML);die;
		//Header('Content-type: text/xml');
		//echo $newXML->asXML();//->asXML();
		//ob_start();
		//print_r($newXML->asXML());
		//echo htmlentities($domDoc->saveXML());
		//$newXML->asXML($filePath);//->asXML();
		//ob_end_flush();
		//ob_end_clean();
		//echo $xml_content=ob_get_contents();
		//die;


	}
}
