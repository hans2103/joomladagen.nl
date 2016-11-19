<?php
/**
 * @package   OSMeta
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Alledia\OSMeta\Pro\Container\Component;

use Alledia\OSMeta\Free\Container\AbstractContainer;
use JFactory;
use JRequest;
use JModelLegacy;
use JFolder;
use JRoute;
use K2HelperRoute;
use JUri;
use JHtml;
use JText;

// No direct access
defined('_JEXEC') or die();

$path = JPATH_SITE . '/components/com_k2/helpers/route.php';
if (!class_exists('K2HelperRoute') && file_exists($path)) {
    require $path;
}

/**
 * K2 Item Metatags Container
 *
 * @since  1.1.0
 */
class K2 extends AbstractContainer
{
    /**
     * Code
     *
     * @var    int
     * @since  1.1.0
     */
    public $code = 3;

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
        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id,
            c.title,
            c.metadesc, IF(ISNULL(m.title) OR m.title='',c.title,m.title) as metatitle, c.catid, c.alias
            FROM `#__k2_items` c
            LEFT JOIN `#__osmeta_metadata` m ON m.item_id=c.id and m.item_type=\"{$this->code}\" WHERE 1";

        $search    = JRequest::getVar("filter_search", "");
        $cat_id    = JRequest::getVar("filter_category_id", "0");
        $author_id = JRequest::getVar("com_content_filter_authorid", "0");
        $state     = JRequest::getVar("com_content_filter_show_published", "");
        $access    = JRequest::getVar("com_content_filter_access", "");

        $com_content_filter_show_empty_descriptions = JRequest::getVar(
            "com_content_filter_show_empty_descriptions",
            "-1"
        );

        if ($search != "") {
            $sql .= " AND (";
            $sql .= " c.title LIKE " . $db->quote('%' . $search . '%');
            $sql .= " OR m.title LIKE " . $db->quote('%' . $search . '%');
            $sql .= " OR c.metadesc LIKE " . $db->quote('%' . $search . '%');
            $sql .= " OR c.alias LIKE " . $db->quote('%' . $search . '%');
            $sql .= " OR c.id = " . $db->quote($search);
            $sql .= ")";
        }

        if ($cat_id > 0) {
            $sql .= " AND c.catid=" . $db->quote($cat_id);
        }

        if ($author_id > 0) {
            $sql .= " AND c.created_by=" . $db->quote($author_id);
        }
        switch ($state) {
            case '1':
                $sql .= " AND c.published=1";
                break;

        }
        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }

        if (!empty($access)) {
            $sql .= " AND c.access = " . $db->quote($access);
        }

        //Sorting
        $order = JRequest::getCmd("filter_order", "title");
        $order_dir = JRequest::getCmd("filter_order_Dir", "ASC");

        switch ($order) {
            case "meta_title":
                $sql .= " ORDER BY metatitle ";
                break;
            case "meta_desc":
                $sql .= " ORDER BY metadesc ";
                break;
            default:
                $sql .= " ORDER BY title ";
                break;
        }

        if ($order_dir == "asc") {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        // Get the total
        $db->setQuery('SELECT FOUND_ROWS();');
        $total = $db->loadResult();

        for($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $row->edit_url = "index.php?option=com_k2&view=item&cid={$row->id}";

            // Get the item view url
            $url    = K2HelperRoute::getItemRoute($row->id.':'.urlencode($row->alias), $row->catid);
            $url    = JRoute::_($url);
            $uri    = JUri::getInstance();
            $url    = $uri->toString(array('scheme', 'host', 'port')) . $url;
            $url    = str_replace('/administrator/', '/', $url);

            $row->view_url = $url;
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

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.id AS id, c.title AS title
            c.fulltext AS content
            FROM `#__k2_items` c WHERE 1";

        $search = JRequest::getVar("filter_search", "");
        $category_id = JRequest::getVar("filter_category_id", "0");
        $com_content_filter_show_empty_descriptions = JRequest::getVar(
            "com_content_filter_show_empty_descriptions",
            "-1"
        );
        $state = JRequest::getVar("com_content_filter_show_published", "");

        if ($search != "") {
            if (is_numeric($search)) {
                $sql .= " AND c.id=" . $db->quote($search);
            } else {
                $sql .= " AND c.title LIKE " . $db->quote('%' . $search.'%');
            }
        }
        if ($category_id > 0) {
            $sql .= " AND c.catid=" . $db->quote($category_id);
        }
        if ($com_content_filter_show_empty_descriptions != "-1") {
            $sql .= " AND (ISNULL(c.metadesc) OR c.metadesc='') ";
        }
        switch ($state) {
            case '1':
                $sql .= " AND c.published=1";
                break;
        }

        $db->setQuery( $sql, $lim0, $lim );
        $rows = $db->loadObjectList();
        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }

        // Get outgoing links
        for($i = 0; $i < count($rows); $i++) {
            $rows[$i]->edit_url = "index.php?option=com_k2&view=item&cid={$rows[$i]->id}";
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
    public function saveMetatags($ids, $metatitles, $metadescriptions, $aliases)
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDBO();

        for($i = 0; $i < count($ids); $i++) {
            $query = $db->getQuery(true)
                ->select('alias')
                ->from('#__k2_items')
                ->where('id = ' . $db->quote((int) $ids[$i]));
            $db->setQuery($query);
            $current = $db->loadObject();

            $sql = "UPDATE `#__k2_items`
                SET metadesc=" . $db->quote($metadescriptions[$i]);

            if (isset($aliases[$i])) {
                if (!empty($aliases[$i])) {
                    $alias = $this->stringURLSafe($aliases[$i]);

                    if ($current->alias !== $alias) {
                        // Check if the alias already exists and ignore it
                        if ($this->isUniqueAlias($alias)) {
                            $sql .= ", alias=" . $db->quote($alias);
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

            $sql .= " WHERE id=" . $db->quote($ids[$i]);
            $db->setQuery($sql);
            $db->query();
            $sql = "INSERT INTO `#__osmeta_metadata` (item_id,
                item_type, title, description)
                VALUES (
                " . $db->quote($ids[$i]).",
                {$this->code},
                " . $db->quote($metatitles[$i]).",
                " . $db->quote($metadescriptions[$i])."
                ) ON DUPLICATE KEY UPDATE title=" . $db->quote($metatitles[$i])." ,
                    description=" . $db->quote($metadescriptions[$i]);

            $db->setQuery($sql);
            $db->query();
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
        foreach ($ids as $key=>$value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT id as id, title as title FROM `#__k2_items` WHERE id IN (".implode(",", $ids).")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if ($item->title != '') {
                $sql = "INSERT INTO `#__osmeta_metadata` (
                        item_id, item_type, title, description)
                        VALUES (
                        " . $db->quote($item->id).",
                        {$this->code},
                        " . $db->quote($item->title).",
                        ''
                    ) ON DUPLICATE KEY UPDATE title=" . $db->quote($item->title);

                $db->setQuery($sql);
                $db->query();
            }
        }
    }

    /**
     * Method to generate descriptions
     *
     * @param array $ids IDs list
     *
     * @access  public
     *
     * @return void
     */
    public function generateDescriptions($ids)
    {
        jimport('legacy.model.legacy');

        $max_description_length = 500;
        $model = JModelLegacy::getInstance("options", "OSModel");
        $params = $model->getOptions();
        $max_description_length = $params->max_description_length ?
            $params->max_description_length : $max_description_length;

        $db = JFactory::getDBO();

        foreach ($ids as $key=>$value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }

        $sql = "SELECT id as id, introtext as introtext FROM `#__k2_items` WHERE id IN (".implode(",", $ids).")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            if ($item->introtext != '') {
                $introtext = strip_tags($item->introtext);
                if (strlen($introtext) > $max_description_length) {
                    $introtext = substr($introtext, 0, $max_description_length);
                }
                $sql = "INSERT INTO #__osmeta_metadata (item_id,
                     item_type, title, description)
                     VALUES (
                     " . $db->quote($item->id).",
                     {$this->code},

                     '',
                     " . $db->quote($introtext)."
                    ) ON DUPLICATE KEY UPDATE description=" . $db->quote($introtext);

                $db->setQuery($sql);
                $db->query();

                $sql = "UPDATE #__k2_items SET metadesc=" . $db->quote($introtext)."
                    WHERE id=" . $db->quote($item->id);

                $db->setQuery($sql);
                $db->query();
            }
        }
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
        $search = JRequest::getVar("filter_search", "");
        $category_id = JRequest::getVar("filter_category_id", "");
        $access = JRequest::getVar("com_content_filter_access", "");
        $com_content_filter_show_published = JRequest::getVar("com_content_filter_show_published", "-1");

        $com_content_filter_show_empty_descriptions = JRequest::getVar(
            "com_content_filter_show_empty_descriptions",
            "-1"
        );

        $result =  JText::_('COM_OSMETA_FILTER_LABEL') . ':
            <input type="text" name="filter_search" id="search" value="' . $search.'" class="text_area" onchange="document.adminForm.submit();" title="' . JText::_('COM_OSMETA_FILTER_DESC') . '"/>
            <button onclick="this.form.submit();">' . JText::_('COM_OSMETA_GO_LABEL') . '</button>
            <button onclick="document.getElementById(\'search\').value=\'\';;this.form.getElementById(\'catid\').value=\'0\';this.form.getElementById(\'filter_authorid\').value=\'0\';this.form.getElementById(\'filter_state\').value=\'\';this.form.submit();">' . JText::_('COM_OSMETA_RESET_LABEL') . '</button>

            &nbsp;&nbsp;&nbsp;';
        $db = JFactory::getDBO();
        $db->setQuery("SELECT id, name FROM #__k2_categories");
        $categories = $db->loadObjectList();
        $result .=  "<select name=\"filter_category_id\" onchange=\"document.adminForm.submit();\">".
            "<option value=\"\">' . JText::_('COM_OSMETA_SELECT_CATEGORY') . '</option>";

        foreach ($categories as $category) {
            $result .= "<option value=\"{$category->id}\" "
                . ($category->id == $category_id?" selected=\"true\"" : "") .">{$category->name}</option>";
        }

        $result .= "</select>";

        $result .= '<br/>
            <label>' . JText::_('COM_OSMETA_SHOW_ONLY_PUBLISHED') . '</label>
            <input type="checkbox" value="1" onchange="document.adminForm.submit();" name="com_content_filter_show_published" '.($com_content_filter_show_published!="-1"?'checked="yes" ':'').'/>
            <label>' . JText::_('COM_OSMETA_SHOW_ONLY_EMPTY_DESCRIPTIONS') . '</label>
            <input type="checkbox" onchange="document.adminForm.submit();" name="com_content_filter_show_empty_descriptions" '.($com_content_filter_show_empty_descriptions!="-1"?'checked="yes" ':'').'/>&nbsp;';

        $result .= JHtml::_('access.level', 'com_content_filter_access', $access, 'onchange="submitform();"');

        return $result;
    }

    /**
     * Method to set Metadata
     *
     * @param int   $id   ID
     * @param array $data Data
     *
     * @access  public
     *
     * @return void
     */
    public function setMetadata($id, $data)
    {
        $db = JFactory::getDBO();
        $sql = "UPDATE `#__k2_items`
            SET ".
            (isset($data["title"])&&$data["title"]?
            "title=" . $db->quote($data["title"]).",":"").
            "metadesc=" . $db->quote($data["metadescription"])."
            WHERE id=" . $db->quote($id);
        $db->setQuery($sql);
        $db->query();

        parent::setMetadata($id, $data);
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
        $params = array();
        parse_str($query, $params);

        $metadata = $this->getDefaultMetadata();

        if (isset($params["id"])) {
            $metadata = $this->getMetadata($params["id"]);
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
            ->from('#__k2_items')
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
        jimport('joomla.filesystem.folder');

        return JFolder::exists(JPATH_SITE . '/administrator/components/com_k2');
    }
}
