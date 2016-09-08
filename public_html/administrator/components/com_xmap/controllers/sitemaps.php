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
 * Class XmapControllerSitemaps
 */
class XmapControllerSitemaps extends JControllerAdmin
{
    /**
     * @var string
     */
    protected $text_prefix = 'COM_XMAP_SITEMAPS';

    /**
     * @param string $name
     * @param string $prefix
     * @param array $config
     *
     * @return object
     */
    public function getModel($name = 'Sitemap', $prefix = 'XmapModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * task for plugins button, redirect to plugin manager
     */
    public function plugins()
    {
        $this->setRedirect('index.php?option=com_plugins&filter_folder=xmap');
        $this->redirect();
    }

    /**
     * task for ping button, ping enabled search engines with selected sitemaps
     */
    public function ping()
    {
        $ids = $this->input->get('cid', array(), 'array');

        if (empty($ids))
        {
            JError::raiseWarning(500, JText::_('JGLOBAL_NO_ITEM_SELECTED'));
        } else
        {
            $model = $this->getModel('Sitemaps');

            try
            {
                $model->ping($ids);
            } catch (RuntimeException $e)
            {

            }
        }

        $this->setRedirect('index.php?option=com_xmap');
    }


}