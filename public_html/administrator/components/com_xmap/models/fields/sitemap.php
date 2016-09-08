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

JFormHelper::loadFieldClass('list');

class JFormFieldSitemap extends JFormFieldList
{
    public $type = 'Sitemap';

    public function getOptions()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('s.id AS value')
            ->select('s.title AS text')
            ->from('#__xmap_sitemap AS s')
            ->where('s.published = ' . $db->quote(1));
        $db->setQuery($query);

        $options = $db->loadObjectList();

        array_unshift($options, JHtml::_('select.option', '', JText::_('JSELECT')));

        return array_merge(parent::getOptions(), $options);
    }

}