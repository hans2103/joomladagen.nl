<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
	defined('_JEXEC') or die('Restricted access'); 
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

class jticketingModelSettings extends JModelLegacy
{
	
	function getAPIpluginData()
	{
		$condtion = array(0 => '\'payment\'');
		$condtionatype = join(',',$condtion);  
		if(JVERSION >= '1.6.0')
		{
			$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE folder in ($condtionatype) AND enabled=1";
		}
		else
		{
			$query = "SELECT id,name,element,published FROM #__plugins WHERE folder in ($condtionatype) AND published=1";
		}
		$this->_db->setQuery($query);
		return $this->_db->loadobjectList();
	}
	function store($post)
	{
		global $mainframe;	
		$mainframe = JFactory::getApplication();
		$config=$post->get('data', '', 'post', 'array', JREQUEST_ALLOWRAW );
		
		if ($config)
		{
			$file_contents[] = '<?php';
			$file_contents[] = "\n";
			$file_contents[] = '$jticketing_config = array(';
			foreach ($config as $k => $v) 
			{
				if(is_array($v))
				{
					
					$str = 'array(';
					$str1=array();
					foreach ($v as $kk => $vv)
					{
						$str1[]= "'{$kk}' => '" . $vv . "'";
					} 	
					$str.= implode(",", $str1);;
					$str .= ')';
					$opts[] ="'{$k}' => " . $str ;
				}
				else
					$opts[] = "'{$k}' => '" . addslashes($v) . "'";
			}
	
			$file_contents[] = implode(",\n", $opts);
			$file_contents[] = ')';		
			$file_contents[] = "\n";
			$file_contents[] = "?>";
			$file_content = implode("\n", $file_contents);
			if (JFile::write($file, $file_content)) 
				return true;
			else
				return false;
		}
		return false;
		
		
	}//store() ends

}
?>
