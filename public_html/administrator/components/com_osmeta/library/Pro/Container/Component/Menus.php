<?php
/**
 * @package   OSMeta
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2013-2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Alledia\OSMeta\Pro\Container\Component;

use Alledia\OSMeta\Free\Container\AbstractContainer;
use JFactory;
use JRequest;
use JRegistry;
use JRoute;
use JUri;
use JHtml;
use JText;

// No direct access
defined('_JEXEC') or die();

/**
 * Menu Item Metatags Container
 *
 * @since  1.1.0
 */
class Menus extends AbstractContainer
{
    /**
     * Code
     *
     * @var    int
     * @since  1.1.0
     */
    public $code = 5;

    /**
     * True, if this content allow to automatically generate
     * description from the content
     *
     * @var boolean
     */
    public $supportGenerateDescription = false;

    /**
     * Get the base query
     *
     * @return JDatabaseQuery
     */
    protected function getQuery()
    {
        $filterSearch                = JRequest::getVar("filter_search", "");
        $filterMenuType              = JRequest::getVar("filter_menu_type", "");
        $filterState                 = JRequest::getVar("filter_show_published", "");
        $filterAccess                = JRequest::getVar("filter_access", "");
        $filterShowEmptyDescriptions = JRequest::getVar("filter_show_empty_descriptions", "-1");

        $db = JFactory::getDBO();

        // Set specific collate if Joomla 3.5+
        $collate = version_compare(JVERSION, '3.5', '>=') ? 'COLLATE utf8mb4_unicode_ci' : '';

        $query = $db->getQuery(true)
            ->select(
                array(
                    'SQL_CALC_FOUND_ROWS m.id',
                    'm.title',
                    'm.menutype',
                    'm.alias',
                    'm.link',
                    'e.element as extension',
                    'm.params as params'
                )
            )
            ->from($db->quoteName('#__menu') . ' AS m')
            ->leftJoin($db->quoteName('#__extensions') . ' AS e ON m.component_id = e.extension_id')
            ->where(
                array(
                    'm.client_id = 0',
                    'm.level > 0',
                    'm.type = ' . $db->quote('component')
                )
            );

        if ($filterSearch !== "") {
            $where = 'm.id = ' . $db->quote($filterSearch)
                . ' OR m.title LIKE ' . $db->quote('%' . $filterSearch . '%')
                . ' OR m.params RLIKE ' . $db->quote('"menu-meta_description"\:"[^\"]*' . $filterSearch . '[^\"]*')
                . ' OR m.params RLIKE ' . $db->quote('"page_title"\:"[^\"]*' . $filterSearch . '[^\"]*')
                . ' OR m.alias LIKE ' . $db->quote('%' . $filterSearch . '%');
            $query->where('(' . $where . ')');
        }


        if (!empty($filterMenuType)) {
            $query->where('m.menutype = ' . $db->quote($filterMenuType));
        }

        if ($filterState == 1) {
            $query->where('m.published = 1');
        }

        if ($filterShowEmptyDescriptions != "-1") {
            $query->where("(m.params LIKE '%\"menu-meta_description\":\"\"%')");
        }

        if (!empty($filterAccess)) {
            $query->where('m.access = ' . $db->quote($filterAccess));
        }

        return $query;
    }

    /**
     * Get Meta Tags
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getMetatags($lim0, $lim, $filter = null)
    {
        $db = JFactory::getDBO();

        // Sorting
        $order    = JRequest::getCmd("filter_order", "title");
        $orderDir = JRequest::getCmd("filter_order_Dir", "ASC");

        $orderStatement = '';
        switch ($order) {
            case "meta_title":
                $orderStatement = 'metatitle';
                break;
            case "meta_desc":
                $orderStatement = 'metadesc';
                break;
            default:
                $orderStatement = 'title';
                break;
        }

        if ($orderDir === "asc") {
            $orderStatement .= " ASC";
        } else {
            $orderStatement .= " DESC";
        }

        $query = $this->getQuery();
        $query->order($orderStatement);

        $db->setQuery($query, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        // Get the total
        $db->setQuery('SELECT FOUND_ROWS();');
        $total = $db->loadResult();

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $row->edit_url = "index.php?option=com_menus&view=item&layout=edit&id={$row->id}";

            // Get the item view url
            $url    = JRoute::_($row->link . '&Itemid=' . $row->id);
            $uri    = JUri::getInstance();
            $url    = $uri->toString(array('scheme', 'host', 'port')) . $url;
            $url    = str_replace('/administrator/', '/', $url);

            $row->view_url = $url;

            // Metadata
            $params = @json_decode($row->params, true);

            if (isset($params['menu-meta_description'])) {
                $row->metadesc = $params['menu-meta_description'];
            }

            if (isset($params['page_title'])) {
                $row->metatitle = $params['page_title'];
            }
        }

        return array(
            'rows'  => $rows,
            'total' => $total
        );
    }

    /**
     * Get Pages
     *
     * @param int $lim0   Offset
     * @param int $lim    Limit
     * @param int $filter Filter
     *
     * @access  public
     *
     * @return array
     */
    public function getPages($lim0, $lim, $filter = null)
    {
        $db = JFactory::getDBO();

        $query = $this->getQuery();
        $db->setQuery($query, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        return $rows;
    }

    /**
     * Save meta tags
     *
     * @param array $ids              IDs
     * @param array $metatitles       Meta titles
     * @param array $metadescriptions Meta Descriptions
     * @param array $aliases          Aliases
     *
     * @access  public
     *
     * @return void
     */
    public function saveMetatags($ids, $metatitles = array(), $metadescriptions = array(), $aliases = array())
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDBO();

        if (empty($metatitles) && empty($metadescriptions) && empty($aliases)) {
            return;
        }

        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            // Get the menu params and update the metadata
            $queryParams = $db->getQuery(true)
                ->select('params')
                ->select('alias')
                ->from('#__menu')
                ->where('id = ' . $id);
            $db->setQuery($queryParams);
            $current = $db->loadObject();

            $query = $db->getQuery(true)
                ->update('#__menu')
                ->where('id = ' . $db->quote($id));

            if (isset($metatitles[$i]) || isset($metadescriptions[$i])) {
                $params = new JRegistry();
                $params->loadString($current->params);

                if (isset($metatitles[$i])) {
                    $params->set('page_title', $metatitles[$i]);
                }

                if (isset($metadescriptions[$i])) {
                    $params->set('menu-meta_description', $metadescriptions[$i]);
                }

                $current->params = $params->toString();
                $query->set('params = ' . $db->quote($current->params));
            }

            if (isset($aliases[$i])) {
                if (!empty($aliases[$i])) {

                    $alias = $this->stringURLSafe($aliases[$i]);

                    if ($current->alias !== $alias) {
                        // Check if the alias already exists and ignore it
                        if ($this->isUniqueAlias($alias)) {
                            $query->set('alias = ' . $db->quote($alias));
                            $query->set('path = ' . $db->quote($alias));
                        } else {
                            $app->enqueueMessage(
                                JText::sprintf('COM_OSMETA_WARNING_DUPLICATED_ALIAS', $alias),
                                'warning'
                            );
                        }
                    }
                } else {
                    JFactory::getApplication()->enqueueMessage(
                        JText::_('COM_OSMETA_WARNING_EMPTY_ALIAS'),
                        'warning'
                    );
                }
            }

            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Method to copy the item title to title
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function copyItemTitleToSearchEngineTitle($ids)
    {
        $db = JFactory::getDBO();

        foreach ($ids as $key =>$value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'title'
                )
            )
            ->from('#__menu')
            ->where('id IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        $ids        = array();
        $metatitles = array();
        foreach ($items as $item) {
            $ids[]        = $item->id;
            $metatitles[] = $item->title;
        }

        $this->saveMetatags($ids, $metatitles);
    }

    /**
     * Method to generate descriptions. We can't support all components,
     * so let's hide this option
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function generateDescriptions($ids)
    {

    }

    /**
     * Method to get Filter
     *
     * @access  public
     *
     * @return string
     */
    public function getFilter()
    {
        $filterSearch                = JRequest::getVar("filter_search", "");
        $filterMenuType              = JRequest::getVar("filter_menu_type", "");
        $filterAccess                = JRequest::getVar("filter_access", "");
        $filterShowPublished         = JRequest::getVar("filter_show_published", "-1");
        $filterShowEmptyDescriptions = JRequest::getVar("filter_show_empty_descriptions", "-1");

        $result =  JText::_('COM_OSMETA_FILTER_LABEL') . ':
            <input type="text" name="filter_search" id="search" value="' . $filterSearch.'" class="text_area" onchange="document.adminForm.submit();" title="' . JText::_('COM_OSMETA_FILTER_DESC') . '"/>
            <button onclick="this.form.submit();">' . JText::_('COM_OSMETA_GO_LABEL') . '</button>
            <button onclick="document.getElementById(\'search\').value=\'\';;this.form.getElementById(\'catid\').value=\'0\';this.form.getElementById(\'filter_authorid\').value=\'0\';this.form.getElementById(\'filter_state\').value=\'\';this.form.submit();">' . JText::_('COM_OSMETA_RESET_LABEL') . '</button>

            &nbsp;&nbsp;&nbsp;';

        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('menutype')
            ->select('title')
            ->from('#__menu_types');
        $db->setQuery($query);

        $menus = $db->loadObjectList();

        $result .=  "<select name=\"filter_menu_type\" onchange=\"document.adminForm.submit();\">".
            "<option value=\"\">" . JText::_("COM_OSMETA_SELECT_MENU") . "</option>";

        foreach ($menus as $menu) {
            $result .= "<option value=\"{$menu->menutype}\" "
                . ($menu->menutype == $filterMenuType?" selected=\"true\"" : "") .">{$menu->title}</option>";
        }

        $result .= "</select>";

        $result .= '<br/>
            <label>' . JText::_('COM_OSMETA_SHOW_ONLY_PUBLISHED') . '</label>
            <input type="checkbox" value="1" onchange="document.adminForm.submit();" name="filter_show_published" '.($filterShowPublished!="-1"?'checked="yes" ':'').'/>
            <label>' . JText::_('COM_OSMETA_SHOW_ONLY_EMPTY_DESCRIPTIONS') . '</label>
            <input type="checkbox" onchange="document.adminForm.submit();" name="filter_show_empty_descriptions" '.($filterShowEmptyDescriptions!="-1"?'checked="yes" ':'').'/>&nbsp;';

        $result .= JHtml::_('access.level', 'filter_access', $filterAccess, 'onchange="submitform();"');

        return $result;
    }

    /**
     * Method to get the Metadata
     *
     * @param int $id Item ID
     *
     * @access  public
     *
     * @return array
     */
    public function getMetadata($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'm.id',
                    'm.id AS item_id',
                    'm.title',
                    'm.link',
                    'm.type',
                    'm.params'
                )
            )
            ->from($db->quoteName('#__menu') . ' AS m')
            ->leftJoin($db->quoteName('#__extensions') . ' AS e ON (m.component_id = e.extension_id)')
            ->where('m.id = ' . $db->quote($id));

        $db->setQuery($query);
        $row = $db->loadAssoc();

        $params = json_decode($row['params'], true);

        // Meta-description
        if (isset($params['menu-meta_description'])) {
            $row['metadescription'] = $params['menu-meta_description'];
        } else {
            $row['metadescription'] = '';
        }

        // Meta-title
        if (isset($params['page_title'])) {
            $row['metatitle'] = $params['page_title'];
        } else {
            $row['metatitle'] = '';
        }

        // Make sure we have the type index
        if (!isset($row['type'])) {
            $row['type'] = '';
        }

        return $this->verifyMetadata($row);
    }

    /**
     * Method to get Metadata
     *
     * @param string $query Query
     *
     * @access  public
     *
     * @return array
     */
    public function getMetadataByRequest($query)
    {
        $params   = array();
        $metadata = $this->getDefaultMetadata();

        parse_str($query, $params);

        if (isset($params["id"]) && isset($params["Itemid"])) {
            $metadata = $this->getMetadata($params["Itemid"]);
        }

        return $metadata;
    }

    /**
     * Method to set Metadata by request
     *
     * @param string $url  URL
     * @param array  $data Data
     *
     * @access  public
     *
     * @return void
     */
    public function setMetadataByRequest($url, $data)
    {
        $params = array();
        parse_str($query, $params);

        if (isset($params["id"]) && $params["id"]) {
            $this->setMetadata($params["id"], $data);
        }
    }

    /**
     * Method to check if an alias already exists
     *
     * @param  string $alias The original alias
     * @return string        The new alias, incremented, if needed
     */
    public function isUniqueAlias($alias)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__menu')
            ->where('alias = ' . $db->quote($alias));

        $db->setQuery($query);
        $count = (int) $db->loadResult();

        return $count === 0;
    }

    /**
     * Check if the component is available
     *
     * @return boolean
     */
    public static function isAvailable()
    {
        return true;
    }
}
