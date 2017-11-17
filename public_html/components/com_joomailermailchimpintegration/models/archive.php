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

class joomailermailchimpintegrationModelArchive extends jmModel {
    var $_data;

    public function getCampaigns() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');

        $menuItemId = $this->app->input->getUint('Itemid');
        if ($menuItemId) {
            $jSite = new JSite();
            $menu = $jSite->getMenu();
            $menuParams = $menu->getParams($menuItemId);
            $params->merge($menuParams);
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/campaigns.php');
        $campaignsModel = new joomailermailchimpintegrationModelCampaigns();

        $filters = array('status' => 'sent');
        $limit = $params->get('limit', 10);

        return $campaignsModel->getCampaigns($filters, $limit, 0, 'sent_date');
    }
}
