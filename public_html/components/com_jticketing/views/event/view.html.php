<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');


/**
 * Class for Jticketing Event view
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingViewEvent extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $params;

	/**
	 * Method to display event
	 *
	 * @param   object  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$this->params = JComponentHelper::getParams('com_jticketing');
		$integration = $this->params->get('integration');
		JLoader::import('JticketingCommonHelper', JUri::root() . 'components/com_jticketing/helpers/common.php');
		$this->jtCommonHelper = new JticketingCommonHelper;
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->jticketingmainhelper     = new jticketingmainhelper;

		$plgData = JPluginHelper::importPlugin('system');
		$dispatcher = JDispatcher::getInstance();
		$result = $dispatcher->trigger('getActivityScript', array('eventfeed'));

		$config = JFactory::getConfig();
		$this->siteName = $config->get('sitename');

		// Native Event Manager.
		if ($integration < 1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$this->state  = $this->get('State');
		$item   = $this->get('Data');
		$this->item = "";
		$groups = $user->getAuthorisedViewLevels();
		$i = 0;

		if (!empty($item->access) and in_array($item->access, $groups))
		{
			$this->item = $item;
			$i++;
		}
		elseif (empty($item->access))
		{
			$this->item = $item;
			$i++;
		}

		if (empty($this->item))
		{
		?>
			<div class="alert alert-info alert-help-inline">
				<?php echo JText::_('COM_JTICKETING_USER_UNAUTHORISED');?>
			</div>
		<?php

			return false;
		}

		$venueid = $this->item->venue;

		// Get integration set
		$this->integration = $this->params->get('integration', '', 'INT');

		if (!empty($this->item->catid))
		{
			$catNm = $this->getModel()->getCategoryName($this->item->catid);

			if (!empty($catNm))
			{
				$this->item->category_id_title = $catNm->title;
			}

			if ($this->item->venue != 0)
			{
				$eventVenueData = $this->jticketingfrontendhelper->getVenue($this->item->venue);
				$this->venueName = $eventVenueData->name;
				$this->venueAddress = $eventVenueData->address;
				$this->venuedatails = $eventVenueData->params;
			}
		}

		$this->enableSelfEnrollment       = $this->params->get('enable_self_enrollment', '', 'INT');
		$this->supressBuyButton           = $this->params->get('supress_buy_button', '', 'INT');
		$this->accessLevelsForEnrollment  = $this->params->get('accesslevels_for_enrollment');

		// Shows Book Ticket Button
		require_once JPATH_SITE . "/components/com_jticketing/models/events.php";
		$Jticketingfrontendhelper = new JticketingModelEvents;
		$this->eventData = $Jticketingfrontendhelper->getTJEventDetails($this->item->id);

		$this->extraData      = $this->get('DataExtra');
		$this->GetTicketTypes = $this->get('TicketTypes');

		$arrayCount = count($this->GetTicketTypes);

		for ($i = 0; $i < $arrayCount; $i++)
		{
			$this->availableSeats = $this->GetTicketTypes[$i]->available;

			if (!empty($this->GetTicketTypes[$i]->count))
			{
				$this->availableCount = $this->GetTicketTypes[$i]->count;
			}

			$this->unlimitedSeats = $this->GetTicketTypes[$i]->unlimited_seats;

			if ($this->availableSeats != '0' && $this->availableSeats != '0' || $this->availableSeats == '1')
			{
				break;
			}
		}

		// Content triggers for short description
		$dispatcher = JDispatcher::getInstance();

		if (!empty($this->item->short_description) )
		{
			$item = new StdClass;
			$item->text = $this->item->short_description;
			JPluginHelper::importPlugin('content');
			$item->params = 'com_jticketing.short_description';
			$short_description = $dispatcher->trigger('onPrepareContent', array (& $item, & $item->params, 0));

			if (!empty($short_description) and $short_description['0'] != 1)
			{
				$this->item->short_description .= "<br/>" . $short_description['0'];
			}
		}

		// Content triggers for long description
		$dispatcher = JDispatcher::getInstance();
		$item->text = $this->item->long_description;
		JPluginHelper::importPlugin('content');
		$item->params = 'com_jticketing.long_description';
		$long_description = $dispatcher->trigger('onPrepareContent', array (& $item, & $item->params, 0));

		if (!empty($long_description)  and $long_description['0'] != 1)
		{
			$this->item->long_description .= "<br/>" . $long_description['0'];
		}

		$this->item->language = JFactory::getLanguage()->getTag();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($this->_layout == 'edit')
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');

			if ($authorised !== true)
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
			}
		}

		$input                 = JFactory::getApplication()->input;
		$this->userid          = JFactory::getUser()->id;
		$eventid               = $input->get('id', '', 'INT');
		$this->isEventbought   = $this->jticketingmainhelper->isEventbought($eventid, $this->userid);
		$this->showbuybutton   = $this->jticketingmainhelper->showbuybutton($eventid);
		$this->buyTicketItemId = $this->jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=order&layout=default');
		$this->allEventsItemid = $this->jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=events&layout=default');
		$this->createEventItemid  = $this->jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->_prepareDocument();

		// Google Map Data
		$address = str_replace(" ", "+", $this->item->event_address);
		$url = "https://maps.google.com/maps/api/geocode/json?address=$address";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$this->response_a = json_decode($response);

		$this->currentTime = JFactory::getDate()->toSql();
		$plugin = JPluginHelper::getPlugin('tjevents', 'plug_tjevents_adobeconnect');
		$params = new JRegistry($plugin->params);
		$this->beforeEventStartTime = $params->get('show_em_btn', '5');
		$this->showAdobeButton = 0;

		if ($this->item->online_events == 1)
		{
			$time = strtotime($this->item->startdate);
			$time = $time - ($this->beforeEventStartTime * 60);
			$current = strtotime($this->currentTime);
			$date = date("Y-m-d H:i:s", $time);
			$datetime = strtotime($date);

			if ($datetime < $current  or $this->userid == $this->item->created_by)
			{
				$this->showAdobeButton = 1;
			}
		}

		if ($this->_layout == 'default_playvideo')
		{
			$this->_playVideo();
		}

		parent::display($tpl);
	}

	/**
	 * Method to prepare document
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_JTICKETING_DEFAULT_PAGE_TITLE'));
		}

		if (empty($title))
		{
			if (!empty($this->item))
			{
				$title = $this->item->title;
			}
			else
			{
				$title = $this->params->get('page_title', '');
			}
		}

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->siteHTTPS = $app->getCfg('force_ssl');
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Function to Build data to call video plugin
	 *
	 * @parmas
	 *
	 * @return  void
	 */
	protected function _playVideo()
	{
		$input = JFactory::getApplication()->input;
		$vid   = $input->get('vid', '', 'INT');
		$type   = $input->get('type', '', 'STRING');
		$model = $this->getModel('event');

		if (!empty($vid))
		{
			// Get video data
			$this->video = $model->getVideoData($vid, $type);

			if (!empty($this->video))
			{
				$this->video_params = array();

				if (!empty($type))
				{
					$this->video->type = substr($this->video->type, 6);

					switch (trim($type))
					{
						case 'youtube' || 'vimeo':
							switch ($this->video->type)
							{
								// Video provider youtube
									case 'youtube':
										// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
										$explodedUrl = explode('/', $this->video->path);

										if (!empty($explodedUrl))
										{
											$videoId = end($explodedUrl);
											$this->video_params['file'] = 'https://www.youtube.com/watch?v=' . $videoId;
											$this->video_params['videoId'] = $videoId;

											// Plugin to call to pay video
											$this->video_params['plugin'] = 'jwplayer';
										}
									break;

									// Video provider vimeo
									case 'vimeo':
										$explodedUrl = explode('/', $this->video->url);

										if (!empty($explodedUrl))
										{
											$videoId = end($explodedUrl);

											// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
											$this->video_params['videoId'] = $videoId;

											// Plugin to call to pay video
											$this->video_params['plugin'] = 'vimeo';
										}
									break;

									// Other video provider than above
									default:
										// For future
									break;
							}
						break;
					}
				}

				$this->video_params['client'] = $input->get('option', '', 'STRING');
			}
		}
	}
}
