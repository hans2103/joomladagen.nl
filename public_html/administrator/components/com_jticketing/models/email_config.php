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

class jticketingModelEmail_config extends JModelLegacy
{
    /*
     Function saves configuration data to a file
     */
    function store(){

        $app 		= JFactory::getApplication();
        $input=JFactory::getApplication()->input;
		$config=JRequest::getVar( 'data', '', 'post', 'array', JREQUEST_ALLOWHTML );
        $file=JPATH_ADMINISTRATOR.DS."components".DS."com_jticketing".DS."config.php";
        $msg 		= '';
        $msg_type	= '';



        if ($config)
        {
            $template_css=$config['template_css'];
            unset($config['template_css']);

            $file_contents="<?php \n\n";
            $file_contents.="\$emails_config=array(\n".$this->row2text($config)."\n);\n";
            $file_contents.="\n?>";

            if (JFile::write($file, $file_contents)) {
                $msg = JText::_('CONFIG_SAVED');
            } else {
                $msg = JText::_('CONFIG_SAVE_PROBLEM');
                $msg_type = 'error';
            }

            $cssfile = JPATH_SITE.DS."components".DS."com_jticketing".DS."assets/css".DS."email.css";
		    		JFile::write($cssfile,$template_css);
        }
        $app->redirect('index.php?option=com_jticketing&view=email_config', $msg, $msg_type);
    }//store() ends



    /*
     This formats the data to be stored in the config file
     */
    function row2text($row,$dvars=array())
    {

        reset($dvars);
        while(list($idx,$var)=each($dvars))
        unset($row[$var]);
        $text='';
        reset($row);
        $flag=0;
        $i=0;
        while(list($var,$val)=each($row))
        {
            if($flag==1)
            $text.=",\n";
            elseif($flag==2)
            $text.=",\n";
            $flag=1;

            if(is_numeric($var))
            if($var{0}=='0')
            $text.="'$var'=>";
            else
            {
                if($var!==$i)
                $text.="$var=>";
                $i=$var;
            }
            else
            $text.="'$var'=>";
            $i++;

            if(is_array($val))
            {
                $text.="array(".$this->row2text($val,$dvars).")";
                $flag=2;
            }
            else
            $text.="\"".addslashes($val)."\"";
        }

        return($text);
    }
}
