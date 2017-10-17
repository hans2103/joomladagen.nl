<?php
/**
 * @package    LMS_Shika
 * @copyright  Copyright (C) 2009 -2015 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plg_tjvideo_vimeo', JPATH_ADMINISTRATOR);

/**
 * Class for Video API
 *
 * @since  1.0.0
 */
class PlgTjvideoVimeo extends JPlugin
{
	/**
	 *Function to get Sub Format options when creating / editing lesson format
	 *
	 * @param   String  &$subject  Subject
	 * @param   Array   $config    Config
	 *
	 * @return void
	 *
	 * @since	1.0
	 */
	public function plgTjvideoVimeo(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Function to get Sub Format options when creating / editing lesson format
	 *
	 * @param   Array  $config  Config
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function getSubFormat_ContentInfo($config = array('vimeo'))
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj         = array();
		$obj['name'] = $this->params->get('plugin_name', 'vimeo player');
		$obj['id']   = $this->_name;

		return $obj;
	}

	/**
	 * Function to get needed data from this API
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function getData()
	{
		$input     = JFactory::getApplication()->input;
		$lesson_id = $input->get('lesson_id', '', 'INT');
		$attempt   = $input->get('last_attempt', '', 'INT');
		$type      = $input->get('type', '', 'STRING');
		$score     = 0;
		$oluser_id = JFactory::getUser()->id;
		require_once JPATH_SITE . '/components/com_tjlms/helpers/tracking.php';
		$comtjlmstrackingHelper = new comtjlmstrackingHelper;

		if ($type == 'started')
		{
			$total_content = round($input->get('duration', '', 'FLOAT'), 2);
			$lesson_status = 'started';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, $total_content);
		}
		elseif ($type == 'update_current')
		{
			$duration      = round($input->get('duration', '', 'FLOAT'), 2);
			$spent         = round($input->get('spent', '', 'FLOAT'), 2);
			$lesson_status = 'incomplete';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $duration, $spent);
		} /*else if($type == 'update_total'){ // update the total content of video
		$total_content	= round($input->get('duration','','FLOAT'),2);
		$lesson_status = 'incomplete';
		$trackingid = $comtjlmstrackingHelper->update_lesson_track($lesson_id,$attempt,$score,$lesson_status,$oluser_id,$total_content,'','');
		}*/
		elseif ($type == 'update_pause')
		// Update current_position of video
		{
			$duration      = round($input->get('duration', '', 'FLOAT'), 2);
			$spent         = round($input->get('spent', '', 'FLOAT'), 2);
			$lesson_status = 'incomplete';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $duration, $spent);
		}
		elseif ($type == 'update_spent')
		// Update current_position of video & total spent
		{
			$duration      = round($input->get('duration', '', 'FLOAT'), 2);
			$current       = round($input->get('current', 0, 'FLOAT'), 2);
			$lesson_status = 'completed';
			$trackingid    = $comtjlmstrackingHelper->update_lesson_track($lesson_id, $attempt, $score, $lesson_status, $oluser_id, '', $current, $duration);
		}
	}

	/**
	 * Function to get Sub Format HTML when creating / editing lesson format
	 *
	 * @param   INT     $mod_id       Mod Id
	 * @param   INT     $lesson_id    Lesson Id
	 * @param   String  $lesson       Lesson
	 * @param   String  $comp_params  Com Param
	 *
	 * $lesson object
	 * $comp_params object
	 *
	 * @return  array $result
	 *
	 * @since 1.0.0
	 */
	public function getSubFormat_vimeoContentHTML($mod_id, $lesson_id, $lesson, $comp_params)
	{
		$result      = array();
		$plugin_name = $this->_name;
		$source      = (isset($lesson->format_details['source'])) ? $lesson->format_details['source'] : '';
		$html        = '
			<div class="control-label">' . JText::_("COM_TJLMS_VIDEO_FORMAT_URL_OPTIONS") . '</div>

			<div  class="controls">
				<input type="hidden" id="lesson_format' . $plugin_name . 'video_source" name="lesson_format[' . $plugin_name . '][video_source]" value="url"/>
				<div id="video_textarea" >
					<textarea id="video_url" class="input-block-level"cols="50" rows="2" name="lesson_format[' .
					$plugin_name . '][video_format_source]" >' . $source . '</textarea>
				</div>
			</div>
		';

		return $html;
	}

	/**
	 * Function to render the plugin html
	 *
	 * @param   Array  $config  Config
	 *
	 * @return  string $html
	 *
	 * @since 1.0.0
	 */
	public function renderPluginHTML($config)
	{
		// YOUR CODE TO RENDER HTML
		if (empty($config['height']))
		{
			$config['height'] = '';
		}

		$html = '

		<div id="' . $config['divId'] . '"></div>

		<script src="//f.vimeocdn.com/js/froogaloop2.min.js"></script>

		<script type="text/javascript">

			techjoomla.jQuery(function(){
				wheight = techjoomla.jQuery(window.parent).height()-80;
				wwidth = techjoomla.jQuery(window.parent).width()

				var height	= "' . $config['height'] . '";

				if( height)
				{
					wheight = height;
				}

				var iframeobj  = "";
				iframeobj += "<iframe src=\'https://player.vimeo.com/video/' . $config['videoId'] . '?api=1&autoplay=1&player_id=vimeoplayer\' ";
				iframeobj += " id=\'vimeoplayer\'  ";
				iframeobj += " width=\' "+wwidth+"\'  height=\'"+wheight+"\'  ";
				iframeobj += " frameborder=\'0\' webkitallowfullscreen mozallowfullscreen allowfullscreen";
				iframeobj += "></iframe>";
				techjoomla.jQuery("#' . $config['divId'] . '").html(iframeobj);

				var iframe = techjoomla.jQuery("#vimeoplayer")[0];
				var froogaloop = $f(iframe); ';

		if ($config["client"] === "com_tjlms")
		{
			$html .= '
					var tjvimeo_flag = "0";
					var newtime = "0";
					var tjvimeo_counter = 0;
					var tjvimeo_isPaused = false;
					var tjvimeo_myInterval = setInterval(function () {
						if(!tjvimeo_isPaused) {
							tjvimeo_lastcounter = tjvimeo_counter;
							++tjvimeo_counter;
							newtime = tjvimeo_counter - tjvimeo_lastcounter;
						}
					}, 1000);

					/* When the player is ready, add listeners for pause, finish, and playProgress */
					froogaloop.addEvent("ready", function() {

						console.log("ready " );
						froogaloop.addEvent("play", onPlay);
						froogaloop.addEvent("pause", onPause);
						froogaloop.addEvent("finish", onFinish);
						froogaloop.addEvent("playProgress", onPlayProgress);

						froogaloop.api("getDuration", function (duration, player_id) {
							techjoomla.jQuery.ajax({
								url: "index.php?option=com_tjlms&task=callSysPlgin&plgType=tjvideo&plgName=' .
								$this->_name . '&plgtask=getData&type=started&lesson_id=' .
								$config['lesson_id'] . '&last_attempt=' . $config['attempt'] . '&duration="+duration,
								dataType: "json",
								async:false,
								success: function(response)
								{
								}
							});
						});

						froogaloop.api("play");
					});

					/* Call the API when a button is pressed */
					techjoomla.jQuery("button").bind("click", function() {
						froogaloop.api(techjoomla.jQuery(this).text().toLowerCase());
					});

					/*video onplay event... seek to duration time*/
					function onPlay(data) {
						tjvimeo_Paused = false;
						console.log("play event : " );
						if(tjvimeo_flag == "0"){
							console.log("onplay");
							froogaloop.api("seekTo", "' . $config['current'] . '");
							tjvimeo_flag = 1;
						}
					}

					function onPlayProgress(data, id) {
						techjoomla.jQuery.ajax({
							url: "index.php?option=com_tjlms&task=callSysPlgin&plgType=tjvideo&plgName=' .
							$this->_name . '&plgtask=getData&type=update_current&lesson_id=' .
							$config['lesson_id'] . '&last_attempt=' . $config['attempt'] . '&duration="+data.seconds+"&spent="+newtime,
							dataType: "json",
							success: function(response)
							{
								attempt = response;
							}
						});
				/*		if(tjvimeo_flag == "0"){
							tjvimeo_flag = "1";
							techjoomla.jQuery.ajax({
								url: "index.php?option=com_tjlms&task=callSysPlgin&plgType=tjvideo&plgName=' .
								$this->_name . '&plgtask=getData&type=update_total&lesson_id=' .
								$config['lesson_id'] . '&last_attempt=' . $config['attempt'] . '&duration="+data.duration,
								dataType: "json",
								success: function(response)
								{
									attempt = response;
								}
							});
						}
				*/
					}

					function onPause(id) {
						tjvimeo_Paused = true;
						/*video paused event*/

						froogaloop.api("paused", function (value, player_id) {
							/* Log out the value in the API Console */
							console.log("onPause Current : " + value);
							techjoomla.jQuery.ajax({
								url: "index.php?option=com_tjlms&task=callSysPlgin&plgType=tjvideo&plgName=' .
								$this->_name . '&plgtask=getData&type=update_pause&lesson_id=' .
								$config['lesson_id'] . '&last_attempt=' . $config['attempt'] . '&duration="+value+"&spent="+newtime,
								dataType: "json",
								success: function(response)
								{
									attempt = response;
								}
							});
						});
					}

					function onFinish(id) {
						tjvimeo_Paused = true;
						console.log("Im done");
						duration =  0;
						froogaloop.api("getDuration", function (value, player_id) {
							duration = value;
						});
						techjoomla.jQuery.ajax({
							url: "index.php?option=com_tjlms&task=callSysPlgin&plgType=tjvideo&plgName=' .
							$this->_name . '&plgtask=getData&type=update_spent&lesson_id=' .
							$config['lesson_id'] . '&last_attempt=' . $config['attempt'] . '&duration="+duration+"&current="+duration,
							dataType: "json",
							success: function(response)
							{
							}
						});
					} ';
		}

		$html .= '
			});
		</script>
		';

		return $html;
	}
}
