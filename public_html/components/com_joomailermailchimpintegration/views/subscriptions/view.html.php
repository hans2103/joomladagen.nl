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

// no direct access
defined('_JEXEC') or die('Restricted Access');

class joomailermailchimpintegrationViewSubscriptions extends jmView {

    public function display($tpl = null) {
        $this->user = JFactory::getUser();

        // redirect guests to login page
        if (!$this->user->id) {
            $uri = JUri::getInstance();;
            $this->app->enqueueMessage(JText::_('JM_ONLY_LOGGED_IN_USERS_CAN_VIEW_SUBSCRIPTIONS'), 'error');
            $this->app->redirect('index.php?option=com_users&view=login&return=' . base64_encode($uri->toString()));
        }

        $this->lists = $this->get('Lists');
        $this->getModel()->checkListSubscriptions($this->lists, $this->user->email);

        // retrieve page title from the menuitem
        $jSite = new JSite();
        $this->menuParams = $jSite->getMenu()->getActive()->params;

        parent::display($tpl);
    }
}
