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
 * Class XmapTableSitemap
 */
class XmapTableSitemap extends JTable
{
    /**
     * @var array
     */
    protected $_jsonEncode = array('params', 'selections');

    /**
     * @param JDatabaseDriver $db
     */
    public function __construct($db)
    {
        parent::__construct('#__xmap_sitemap', 'id', $db);
    }

    /**
     * @param mixed $array
     * @param array $ignore
     *
     * @return bool
     */
    public function bind($array, $ignore = array())
    {
        if (isset($array['selections']) && is_array($array['selections']))
        {
            foreach ($array['selections'] as $menutype => $options)
            {
                if (isset($options['enabled']))
                {
                    unset($array['selections'][$menutype]['enabled']);
                } else
                {
                    unset($array['selections'][$menutype]);
                }
            }
        }

        return parent::bind($array, $ignore);
    }

    /**
     * @todo alias duplication check
     *
     * return bool
     */
    public function check()
    {
        if (empty($this->alias))
        {
            $this->alias = $this->title;
        }

        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) == '')
        {
            $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        return true;
    }

    /**
     * @param bool $updateNulls
     *
     * @return bool
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id)
        {
            $this->modified = $date->toSql();
            $this->modified_by = $user->get('id');
        } else
        {
            $this->created = $date->toSql();
            $this->created_by = $user->get('id');
        }

        // for old xmap installations
        if (!$this->created_by)
        {
            $this->created_by = $user->get('id');
        }

        return parent::store($updateNulls);
    }
}
