<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class XmapController
 */
class XmapController extends JControllerLegacy
{
    /**
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @return JControllerLegacy
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        $urlparams = array('id' => 'INT', 'itemid' => 'INT', 'uid' => 'CMD', 'action' => 'CMD', 'property' => 'CMD', 'value' => 'CMD');

        $viewName = JFactory::getApplication()->input->getCmd('view');
        $viewType = JFactory::getDocument()->getType();

        $view = $this->getView($viewName, $viewType);

        $view->setModel($this->getModel('Sitemap'), true);

        return parent::display($cachable, $urlparams);
    }
}