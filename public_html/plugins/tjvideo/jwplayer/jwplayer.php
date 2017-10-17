<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_tjvideo_jwplayer', JPATH_ADMINISTRATOR);

/**
 * PlgTjvideoJwplayer class.
 *
 * @package  JTicketing
 * @since    1.8
 */
class PlgTjvideoJwplayer extends JPlugin
{
	/**
	 * Function constructor.
	 *
	 * @param   String  &$subject  Reference val of Subject
	 * @param   Array   $config    Config
	 *
	 * @return void
	 *
	 * @since	1.0
	 */
	public function plgTjvideoJwplayer(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Function getSubFormat_ContentInfo.
	 *
	 * @param   Array  $config  Config
	 *
	 * @return object
	 */
	public function getSubFormat_ContentInfo($config = array('jwplayer'))
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj         = array();
		$obj['name'] = $this->params->get('plugin_name', 'jwplayer player');
		$obj['id']   = $this->_name;

		return $obj;
	}

	/**
	 * Function to get Sub Format HTML when creating / editing lesson format
	 *
	 * @param   INT     $mod_id       Mod Id
	 * @param   INT     $lesson_id    Lesson Id
	 * @param   String  $lesson       Lesson
	 * @param   Array   $comp_params  Com Params
	 *
	 * @return html
	 */
	public function getSubFormat_jwplayerContentHTML($mod_id, $lesson_id, $lesson, $comp_params)
	{
		$result         = array();
		$plugin_name    = $this->_name;

		// Video format...used when lesson format is selected as video

		$video_format   = array();
		$video_format[] = JHtml::_('select.option', 'url', JText::_('Enter Video / Audio URL'));
		$video_format[] = JHtml::_('select.option', 'upload', JText::_('Upload Video / Audio'));
		$source         = (isset($lesson->format_details['source'])) ? $lesson->format_details['source'] : '';
		$html           = '
		<script type="text/javascript">
		//repective input to show depending on video format if lesson format is video...
function getVideoFormat(subformat,thiselement)
{
	var format_lesson_form = techjoomla.jQuery(thiselement).closest(".lesson-format-form");
	var thiselementval = techjoomla.jQuery(thiselement).val();

	if(thiselementval != "upload")
	{
		techjoomla.jQuery(".video_subformat #video_package",format_lesson_form).hide();
		techjoomla.jQuery(".video_subformat #video_textarea",format_lesson_form).show();
	}
	else
	{
		techjoomla.jQuery(".video_subformat #video_package",format_lesson_form).show();
		techjoomla.jQuery(".video_subformat #video_textarea",format_lesson_form).hide();
	}
}
		</script>
					<div class="control-label">' . JText::_("COM_TJLMS_VIDEO_FORMAT_OPTIONS") . '</div>

					<div  class="controls">
						<div class="lesson_video_format_container">
							' . JHtml::_('select.genericlist', $video_format, "lesson_format[" .
							$plugin_name . "][video_source]", 'class="class_video_format"
							onchange="getVideoFormat(\'' . $plugin_name . '\',this);"', "value", "text", 'upload') . '
						</div>

						<div id="video_textarea" style="display:none">
							<textarea id="video_url" class="input-block-level"cols="50"
							rows="2" name="lesson_format[' . $plugin_name . '][video_format_source]" >' .
							$source . '</textarea>
						</div>
						<div id="video_package">
							<div class="fileupload fileupload-new pull-left" data-provides="fileupload">
								<div class="input-append">
									<div class="uneditable-input span4">
										<span class="fileupload-preview">
											' . JText::sprintf('COM_TJLMS_UPLOAD_FILE_WITH_EXTENSION', 'flv, mp4, mp3', $comp_params->get('lesson_upload_size', '0', 'INT')) . '
										</span>
									</div>
									<span class="btn btn-file">
										<span class="fileupload-new">' . JText::_("COM_TJLMS_BROWSE") . '</span>
										<input type="file" id="video_upload" name="lesson_format[' .
										$plugin_name . '][video]" onchange="validate_file(this,\'' . $mod_id . '\',\'' . $plugin_name . '\')">
									</span>
								</div>
							</div>
							<div style="clear:both"></div>
							<div class="format_upload_error alert alert-error" style="display:none" ></div>
							<div class="format_upload_success alert alert-info" style="display:none"></div>
						</div>
						<input type="hidden" class="valid_extensions" value="flv,mp4,mp3"/>
					</div>';

		return $html;
	}

	/**
	 * Function to get needed data for this API
	 *
	 * @param   Array  $data  Data
	 *
	 * @return result
	 */
	public function getData($data)
	{
		// The $data will be contain some useful data which is require to get futher data from the api
		// YOUR CODE TO GET DATA

		$input     = JFactory::getApplication()->input;

		// YOUR CODE ENDS
		$re        = '';
		$lesson_id = $input->get('lesson_id', '', 'INT');
		$attempt   = $input->get('last_attempt', '', 'INT');
		$type      = $input->get('type', '', 'STRING');
		$score     = 0;
		$oluser_id = JFactory::getUser()->id;
		$db        = JFactory::getDBO();
		require_once JPATH_SITE . '/components/com_tjlms/helpers/tracking.php';

		$comtjlmstrackingHelper = new comtjlmstrackingHelper;

		if ($type == 'update')
		{
			$lesson_status = 'started';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id);
		}
		elseif ($type == 'update_current')
		{
			$duration      = round($input->get('duration', '', 'FLOAT'), 2);
			$spent         = round($input->get('spent', '', 'FLOAT'), 2);
			$lesson_status = 'incomplete';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $duration, $spent);
		}

		// Update the total content of video
		elseif ($type == 'update_total')
		{
			$total_content = round($input->get('duration', '', 'FLOAT'), 2);
			$lesson_status = 'incomplete';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, $total_content, '', '');
		}

		// Update current_position of video
		elseif ($type == 'update_pause')
		{
			$duration      = round($input->get('duration', '', 'FLOAT'), 2);
			$spent         = round($input->get('spent', '', 'FLOAT'), 2);
			$lesson_status = 'incomplete';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $duration, $spent);
		}

		// Update current_position of video & total spent
		elseif ($type == 'update_spent')
		{
			$duration = round($input->get('duration', '', 'FLOAT'), 2);

			$current       = round($input->get('current', 0, 'FLOAT'), 2);
			$lesson_status = 'completed';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $current, $duration);
		}

		return $re;
	}

	/**
	 * Function to render the document
	 *
	 * @param   Array  $config  Config
	 *
	 * @return html
	 */
	public function renderPluginHTML($config)
	{
		$api_key = $this->params->get('appkey', '', 'STRING');

		// YOUR CODE TO RENDER HTML
		/*@TODO take jwpsrv.com/library on local rather than using the live file */

		if (empty($config['height']))
		{
			$config['height'] = '';
		}

		$html = '
		<script src="https://jwpsrv.com/library/' . $api_key . '.js"></script>

		<div id="' . $config['divId'] . '">Loading the player...</div>

		<script type="text/javascript">

			var wheight	= techjoomla.jQuery(window).height();

			if(wheight == 0)
			{
				wheight	= techjoomla.jQuery(window.parent).height();
			}

			wheight	=	wheight-80;

			var height	= "' . $config['height'] . '";

			if( height)
			{
				wheight = height;
			}

			if("' . $config['client'] . '" == "com_jticketing")
			{
			}

			jwplayer("' . $config['divId'] . '").setup({
				file: "' . $config['file'] . '",
				width: "100%",
				height: wheight,
				autostart:false
			});

		</script>
		';

		// YOUR CODE ENDS
		// This may be an iframe directlys
		return $html;
	}
}
