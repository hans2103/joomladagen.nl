<?php
/**
 *  @package    Quick2Cart
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plug_jticketing_tax_default', JPATH_ADMINISTRATOR);

class plgJticketingtaxjticketing_tax_default extends JPlugin
{

	//function Add Tax
	function addTax($amt)
	{
			//print_r( $this->tax_per);die;
		$tax_per=$this->params->get('tax_per');
		$tax_value= ($tax_per*$amt)/100;
		$return=new Stdclass;
		$return->percent=$tax_per."%";
		$return->taxvalue=$tax_value;	
			 
		return $return;			
	}//function ends

}//class ends 
