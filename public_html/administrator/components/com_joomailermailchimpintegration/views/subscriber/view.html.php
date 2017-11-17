<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * */

// no direct access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.html.html.bootstrap');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomlamailer/hotActivityComposite.php');

class joomailermailchimpintegrationViewSubscriber extends jmView {

	public function display($tpl = null) {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root(true) . '/media/com_joomailermailchimpintegration/backend/css/subscriber.css');

        JToolBarHelper::title(JText::_('JM_NEWSLETTER_SUBSCRIBERS'), $this->getPageTitleClass());

        $option = $this->input->getCmd('option');
		$this->limit = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->getCfg('list_limit'), 'int');
		$this->limitstart = $this->app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

		if ($MCapi && $JoomlamailerMC->pingMC()) {
			JToolBarHelper::custom('goToLists', 'list-2', 'list-2', 'Lists', false, false);
			JToolBarHelper::spacer();
			if ($this->input->getString('type') == 's') {
				JToolBarHelper::custom('unsubscribe', 'minus-sign', 'minus-sign', 'Unsubscribe', true, false);
				JToolBarHelper::spacer();
				JToolBarHelper::custom('delete', 'unpublish', 'unpublish', 'Delete', true, false);
			} else if ($this->input->getString('type') == 'u') {
				//JToolBarHelper::custom('resubscribe', 'resubscribe', 'resubscribe', 'Resubscribe', false, false);
			}
		}

        $listId = $this->input->getString('listId');
        $this->user = JFactory::getUser($this->input->getUint('uid'));
        $this->memberInfo = $this->getModel()->getListMemberInfo($listId, $this->user->email);

		//TODO convert $start to GMT using JConfig and tzoffset
		$start = $this->user->registerDate;

		$campaigns = $this->getModel()->getCampaignsSince($start);

		$this->stats = array();
        if (!empty($campaigns['total_items'])) {
            foreach ($campaigns['campaigns'] as $campaign) {
                // check if this email was ever subscribed to this list
                try {
                    $listMemberInfo = $this->getModel()->getListMemberInfo($campaign['recipients']['list_id'], $this->user->email);
                } catch(Exception $e) {
                    continue;
                }

                $this->stats[$campaign['id']]['list_sub'] = ($listMemberInfo['timestamp_signup']
                    ? $listMemberInfo['timestamp_signup'] : $listMemberInfo['timestamp_opt']);

                $clicks = $opens = 0;
                try {
                    $clickStats = $this->getModel()->reportsEmailActivity($campaign['id'], $this->user->email);
                    foreach ($clickStats['activity'] as $cs) {
                        if ($cs['action'] == 'open') {
                            $opens++;
                        }
                        if ($cs['action'] == 'click') {
                            $clicks++;
                        }
                    }
                } catch(Exception $e) {}

                $this->stats[$campaign['id']]['opens'] = $opens;
                $this->stats[$campaign['id']]['clicks'] = $clicks;

                $this->stats[$campaign['id']]['title'] = $campaign['settings']['title'];
                $this->stats[$campaign['id']]['send_time'] = $campaign['send_time'];
                $this->stats[$campaign['id']]['segment_text'] = $campaign['recipients']['segment_text'];
            }
		}

		$this->avatar = false;
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/admin.comprofiler.php')) {
			// community builder is being used
            $query = $this->db->getQuery(true)
                ->select($this->db->qn('avatar'))
                ->from($this->db->qn('#__comprofiler'))
                ->where($this->db->qn('id') . ' = ' . $this->db->q($this->user->id));
			$avatarPath = $this->db->setQuery($query)->loadResult();
			if ($avatarPath) {
				$this->avatar = JURI::root() . 'images/comprofiler/' . $avatarPath;
			}
		} else if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_community/community.php')) {
			// jomsocial is being used
            $query = $this->db->getQuery(true)
                ->select($this->db->qn('avatar'))
                ->from($this->db->qn('#__community_users'))
                ->where($this->db->qn('userid') . ' = ' . $this->db->q($this->user->id));
            $avatarPath = $this->db->setQuery($query)->loadResult();
			if ($avatarPath) {
				$this->avatar = JURI::root() . $avatarPath;
			}
		}

        $this->kloutScore = $this->getModel()->getKloutScore();
        $this->twitterName = $this->getModel()->getTwitterName();
        $this->facebookName = $this->getModel()->getFacebookName();

		$composite = new hotActivityComposite();
		$this->hotActivity = $composite->getActivity();
		$this->hotnessRating = $composite->getHotnessValue();

		$this->jomSocialGroups = $this->getModel()->getJomSocialGroups();
        $this->jomSocialDiscussions = $this->getModel()->getRecentJomSocialDiscussions();
        $this->totalDiscussionsOfUser = $this->getModel()->getTotalJomSocialDiscussionsOfUser();

        jimport('joomla.html.pagination');
		$this->pagination = new JPagination(count($this->stats), $this->limitstart, $this->limit);

        parent::display($tpl);
		require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
	}
}
