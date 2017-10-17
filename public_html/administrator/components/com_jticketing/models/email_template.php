<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Class for email template
 *
 * @since  1.6
 */
class JticketingModelEmail_Template extends JModelLegacy
{
	/**
	 * Method to store email template
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function store()
	{
		$app      = JFactory::getApplication();
		$input    = JFactory::getApplication()->input;
		$config   = JRequest::getVar('data', '', 'post', 'array', JREQUEST_ALLOWHTML);
		$file     = JPATH_ADMINISTRATOR . "/components/com_jticketing/email_template.php";
		$msg      = '';
		$msg_type = '';

		if ($config)
		{
			$template_css = $config['template_css'];
			unset($config['template_css']);
			$file_contents = "<?php \n\n";
			$file_contents .= "\$emails_config=array(\n" . $this->row2text($config) . "\n);\n";
			$file_contents .= "\n?>";

			if (JFile::write($file, $file_contents))
			{
				$msg = JText::_('CONFIG_SAVED');
			}
			else
			{
				$msg      = JText::_('CONFIG_SAVE_PROBLEM');
				$msg_type = 'error';
			}

			$cssfile = JPATH_SITE . "/components/com_jticketing/assets/css/email_template.css";
			JFile::write($cssfile, $template_css);
		}

		$app->redirect('index.php?option=com_jticketing&view=email_template', $msg, $msg_type);
	}

	/**
	 * Method to get data
	 *
	 * @param   array  $row    row for template
	 * @param   array  $dvars  dvars
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function row2text($row, $dvars = array())
	{
		reset($dvars);

		while (list($idx, $var) = each($dvars))
		{
			unset($row[$var]);
		}

		$text = '';
		reset($row);
		$flag = 0;
		$i    = 0;

		while (list($var, $val) = each($row))
		{
			if ($flag == 1)
			{
				$text .= ",\n";
			}
			elseif ($flag == 2)
			{
				$text .= ",\n";
			}

			$flag = 1;

			if (is_numeric($var))
			{
				if ($var{0} == '0')
				{
					$text .= "'$var'=>";
				}
				else
				{
					if ($var !== $i)
					{
						$text .= "$var=>";
					}

					$i = $var;
				}
			}
			else
			{
				$text .= "'$var'=>";
			}

			$i++;

			if (is_array($val))
			{
				$text .= "array(" . $this->row2text($val, $dvars) . ")";
				$flag = 2;
			}
			else
			{
				$text .= "\"" . addslashes($val) . "\"";
			}
		}

		return ($text);
	}
}
