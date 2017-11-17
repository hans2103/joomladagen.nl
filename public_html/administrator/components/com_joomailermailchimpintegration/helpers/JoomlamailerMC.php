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
**/

require_once(__DIR__ . '/../libraries/Mailchimp/JoomlamailerMailchimp.php');

// no direct access
defined('_JEXEC') or die('Restricted Access');

class JoomlamailerMC {

    protected $session;

    public function __construct() {
        $this->session = JFactory::getSession();
    }

    public function pingMC(){
        if (!$this->session->has('mcAccountData')) {
            jimport('joomla.html.parameter');
            jimport('joomla.application.component.helper');
            $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
            $MCapi = $params->get('params.MCapi');
            $MC = new JoomlamailerMailchimp($MCapi);
            try {
                $mcAccountData = $MC->getAccountDetails();
                if (isset($mcAccountData['_links'])) {
                    unset($mcAccountData['_links']);
                }
            } catch (Exception $e) {
                return false;
            }
            $this->session->set('mcAccountData', $mcAccountData);
        }

        return $this->session->get('mcAccountData');
    }

    public function apiKeyMissing($incorrectKey = 0) {
        jimport('joomla.html.pane');
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi  = $params->get('params.MCapi');

        $html  = '<tr><td align="left" valign="center">';
        $html .= '<div id="initialScreen">';

        $html .= '<div id="authHeader">';
        $html .= '<h1>' . JText::_('JM_GETTING_STARTED') . '</h1>';
        $html .= '<div id="MClogo"><a href="http://www.mailchimp.com/?pid=joomailer&source=website" target="_blank">';
        $html .= '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/MC_logo.png" /></a></div>';
        $html .= '<div id="freddieBig"><img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/freddie.png" /></div>';

        $html .= '</div>';

        $html .= '<table width="100%"><tr><td valign="top" width="330">';
        $html .= '<div id="signupBox">';
        $html .= '<div id="signupBoxHeader">';
        $html .= '<a href="http://www.mailchimp.com/signup/?pid=joomailer&source=website" target="_blank">' . JText::_('JM_CREATE_ACCOUNT') . '</a>';
        $html .= '</div>';
        $html .= '<div id="subHead"><div id="subHeadInner">' . JText::_('JM_WHY_MAILCHIMP_IS_AWESOME') . '</div></div>';
        $html .= '<ul>';
        $html .= '<li><span class="bullet">1</span>' . JText::_('JM_FOREVER_FREE_PLAN') . '</li>';
        $html .= '<li><span class="bullet">2</span>' . JText::_('JM_TIMEWARP') . '</li>';
        $html .= '<li><span class="bullet">3</span>' . JText::_('JM_STYLISH_EMAILS') . '</li>';
        $html .= '<li><span class="bullet">4</span>' . JText::_('JM_DETAILED_REPORTS') . '</li>';
        $html .= '<li><span class="bullet">5</span>' . JText::_('JM_ITS_SOCIAL') . '</li></ul>';
        $html .= '</div>';

        $html .= '</td><td valign="top">';

        $html .= '<div id="authTabs" style="float:left;width: 100%;">';

        $html .= JHtml::_('bootstrap.startTabSet', 'create_campaign', array('active' => 'account'));
        $html .= JHtml::_('bootstrap.addTab', 'create_campaign', 'account', JText::_('JM_ALREADY_HAVE_A_MAILCHIMP_ACCOUNT', true));

        $html .= '<table width="100%"><tr><td width="50%" valign="top" style="border-right: 1px solid #dfd6c7; padding: 5px 15px 5px 5px;">';

        $html .= '<h2>' . JText::_('JM_ENTER_API_KEY') . '</h2>';
        $html .= '<p>' . JText::_('JM_API_KEY_INSTRUCTIONS') . '</p>';
        $html .= '<form action="index.php?option=com_joomailermailchimpintegration&view=main" method="post" name="adminForm" id="adminForm" autocomplete="off">';
        $html .= '<input type="text" name="MCapi" id="MCapi" value="' . $MCapi . '" class="text_area" size="45" />';
        $html .= '<button class="buttonSaveApi JMbuttonOrange" type="button" onclick="submitbutton(\'save\');">' . JText::_('JM_SAVE_DRAFT') . '</button>';
        $html .= '<input type="hidden" name="controller" value="main" />';
        $html .= '<input type="hidden" name="option" value="com_joomailermailchimpintegration" />';
        $html .= '<input type="hidden" name="task" value="" />';
        $html .= '</form>';
        $html .= '<div style="clear:both;"></div>';
        $html .= '<div id="APIinfo"><a href="http://kb.mailchimp.com/accounts/management/about-api-keys" rel="{handler: \'iframe\', size: {x: 980, y: 550}}" class="modal">' . JText::_('JM_API_INFO') . '</a></div>';

        if ($incorrectKey){
            $html .= $this->loginIncorrect();
        }

        $html .= '</td><td valign="top" width="50%" style="padding: 5px 5px 5px 15px;">';

        $html .= '<h2>' . JText::_('JM_PICK_YOUR_PRICING_PLAN') . '</h2>';
        $html .= '<div class="planDescription"><h3>' . JText::_('JM_PAY_AS_YOU_GO') . '</h3>';
        $html .= JText::_('JM_PERFECT_FOR_INFREQUENT_SENDERS') . '</div>';
        $html .= '<div class="buyButton"><a class="JMbuttonBlue" href="http://www.mailchimp.com/pricing/?pid=joomailer&source=website" target="_blank">' . JText::_('JM_BUY') . ' &raquo;</a></div>';
        $html .= '<div style="clear:both;"></div>';
        $html .= '<div class="planDescription"><h3>' . JText::_('JM_MONTHLY') . '</h3>';
        $html .= JText::_('JM_SEND_OFTEN_SAVE_A_TON') . '</div>';
        $html .= '<div class="buyButton"><a class="JMbuttonBlue" href="http://www.mailchimp.com/pricing/?pid=joomailer&source=website" target="_blank">' . JText::_('JM_BUY') . ' &raquo;</a></div>';
        $html .= '<div style="clear:both;"></div>';
        $html .= '<div id="newInfo">' . JText::_('JM_NEW_TO_MAILCHIMP') . '</div>';

        $html .= '</td></tr></table>';

        $html .= JHtml::_('bootstrap.endTab');
        $html .= JHtml::_('bootstrap.endTabSet');

        $html .= '</div>';
        $html .= '</td></tr></table>';
        $html .= '</div>';
        $html .= '<div style="clear:both;"></div>';
        $html .= '</td></tr>';
        $html .= '</table>';

        return $html;
    }

    private function loginIncorrect(){
        $html  = '<div style="clear:both;"></div>';
        $html  .= '<div>' .
        '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/MC_logo_sad_48.png" align="left" />' .
        '<br /><h2 style="color:#ff0000;font-size:12px;"> ' . JText::_('JM_INCORRECT_API_KEY_ENTERED') . '</h2></div>';

        return $html;
    }

}
