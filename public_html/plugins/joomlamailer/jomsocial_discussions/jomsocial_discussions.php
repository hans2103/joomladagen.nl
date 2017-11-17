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

class plgJoomlamailerJomsocial_discussions extends JPlugin {

    private $db, $app;
    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'jomsocial_discussions';
    private $data;

    // determine if Jomsocial is installed
    private function isInstalled() {
        if (!$this->db || !$this->app) {
            $this->db = JFactory::getDBO();
            $this->app = JFactory::getApplication();
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn('extension_id'))
            ->from($this->db->qn('#__extensions'))
            ->where($this->db->qn('type') . ' = ' . $this->db->q('component'))
            ->where($this->db->qn('element') . ' = ' . $this->db->q('com_community'));

        return ($this->db->setQuery($query)->loadResult()) ? true : false;
    }

    /**
    * This function is called by the joomlamailer create view and allows you to add a tabslider containing a list of articles.
    * @return an Array of objects containing the data
    */
    public function getArticleList() {
        // is component is not installed - don't do anything
        if (!$this->isInstalled()) {
            return false;
        }

        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, null, true);

        $data = array();

        // specify the Tabslider's title
        $data['title'] = JText::_('JM_JOMSOCIAL_DISCUSSIONS');

        // set the article list
        $data['table'] = $this->renderTable($this->getData());

        return $data;
    }

    /**
    * Returns the query
    * @return string The query to be used to retrieve the data
    */
    private function buildQuery() {
        if (!$this->db || !$this->app) {
            $this->db = JFactory::getDBO();
            $this->app = JFactory::getApplication();
        }

        $query = $this->db->getQuery(true);
        $fields = $this->db->qn(array('d.id', 'd.title', 'd.message', 'd.creator', 'd.created', 'g.name'));
        $query->select('SQL_CALC_FOUND_ROWS ' . implode(', ', $fields))
            ->from($this->db->qn('#__community_groups_discuss') . ' AS d')
            ->join('LEFT', $this->db->qn('#__community_groups') . ' AS g ON ' . $this->db->qn('d.groupid') . ' = ' . $this->db->qn('g.id'))
            ->order($this->db->qn('d.created') . ' DESC');

        return $query;
    }

    /**
    * Retrieves the data
    * @return an Array of objects containing the data
    */
    private function getData() {
        if (!$this->db || !$this->app) {
            $this->db = JFactory::getDBO();
            $this->app = JFactory::getApplication();
        }

        // Lets load the data if it doesn't already exist
        if (empty($this->data)) {

            $limitstart = $this->app->input->getString($this->id . 'limitstart', 0);
            $limit = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));

            $query = $this->buildQuery();
            $this->db->setQuery($query, $limitstart, $limit);
            $this->data = $this->db->loadObjectList();

            // apply stored article order
            $itemOrder = $this->app->input->getString($this->id . 'Order', '');
            if ($itemOrder) {
                $itemOrder = explode(';', $itemOrder);
                // remove last entry (it's empty because $itemOrder string ends with a ";")
                unset($itemOrder[count($itemOrder)-1]);

                $itemsReordered = array();
                $i = 0;
                foreach ($itemOrder as $io) {
                    $x = 0;
                    for ($x = 0; $x < count($this->data); $x++) {
                        if (isset($this->data[$x]) && $io == $this->data[$x]->id) {
                            $itemsReordered[$i] = $this->data[$x];
                            unset( $this->data[$x] );
                        }
                    }
                    $i++;
                }
                $itemsReordered = array_merge($itemsReordered, $this->data);
                $this->data = $itemsReordered;
            }
        }

        return $this->data;
    }

    private function getTotal() {
        if (!$this->db) {
            $this->db = JFactory::getDBO();
        }

        return $this->db->setQuery('SELECT FOUND_ROWS();')->loadResult();
    }

    private function getPagination() {
        if (!$this->app) {
            $this->app = JFactory::getApplication();
        }

        jimport('joomla.html.pagination');
        $limitstart = $this->app->input->getString($this->id . 'limitstart', 0);
        $limit = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));
        return new JPagination($this->getTotal(), $limitstart, $limit, $this->id);
    }

    private function getJomsocialDiscussion($id = false) {
        if (!$this->db) {
            $this->db = JFactory::getDBO();
        }
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('d.id', 'd.groupid', 'd.creator', 'd.title', 'd.message', 'g.name')))
            ->from('#__community_groups_discuss AS d')
            ->join('LEFT', '#__community_groups AS g ON d.groupid = g.id');
        if ($id) {
            $query->where($this->db->qn('d.id') . ' = ' . $this->db->q($id));
        }
        $query->order('d.created DESC');
        $this->db->setQuery($query);

        return ($id) ? $this->db->loadObject() : $this->db->loadObjectList();
    }

    /**
    * Creates the table of articles/items.
    * @return a String containing the table as HTML.
    */
    private function renderTable($items) {
        if (!count($items)) {
            return '';
        }
        if (!$this->app) {
            $this->app = JFactory::getApplication();
        }

        $pagination = $this->getPagination();

        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, null, true);

        // CFactory
        require_once(JPATH_SITE . '/components/com_community/libraries/core.php');

        $items_selected = $this->app->input->get($this->id, array());
        $filter_cat	= $this->app->input->getInt($this->id . '_catid', -1);
        $search		= $this->app->getUserStateFromRequest('search_' . $this->id, 'search_' . $this->id, '', 'string');
        $search		= \Joomla\String\StringHelper::strtolower($search);
        $ttImage = '../media/com_joomailermailchimpintegration/backend/images/info.png';

        // specify the column titles.
        // Translations may be entered in the plugin's language files.
        $columns = array(
            JText::_('JM_INCLUDE'),
            JText::_('JM_JOMSOCIAL_GROUP'),
            JText::_('JM_TITLE'),
            JText::_('JM_FIRST_POST'),
            JText::_('JM_STARTER'),
            JText::_('JM_DATE')
        );
        // Specify an info tooltip, which will be available via the Info icon next to the column title.
        // Empty string means no tooltip will be created.
        $columnTooltips = array(
            '',
            '',
            '',
            '',
            '',
            ''
        );
        // specify the attributes for each column.
        //  class="sorttable_nosort"  disables the table to be sortable by clicking on this column title.
        $columnAttributes = array(
            'width="40" nowrap="nowrap" class="sorttable_nosort"',
            'width="150" nowrap="nowrap" align="left"',
            'width="200" nowrap="nowrap" align="left"',
            'nowrap="nowrap" align="left"',
            'width="60" nowrap="nowrap"',
            'width="60" nowrap="nowrap"'
        );

        // The following two lines are mandatory and will be used for the AJAX preview.
        // The first line adds $this->id to the includeComponents object. Leave it as it is.
        // The second line defines the available additional options (checkboxes, radio buttons).
        $table = '<script language="javascript" type="text/javascript">
        includeComponents[Object.keys(includeComponents).length] = "' . $this->id . '";
        includeComponentsOptions["' . $this->id . '"] = {};
        </script>';



        // assign class="adminlist" to the table to apply css-styling from the component.
        // assign class="sortable" to the table to allow sorting by clicking the column titles.
        $table .= '<table class="adminlist sortable" id="' . $this->id . '">
        <thead>
        <tr>';
        $columnCounter = 0;
        foreach ($columns as $cTitle) {
            $table .= '<th ' . $columnAttributes[$columnCounter] . '>' . $cTitle;
            if ($columnTooltips[$columnCounter] != '') {
                $table .= '&nbsp;' . JHTML::tooltip(JText::_($columnTooltips[$columnCounter]), JText::_($cTitle), $ttImage, '');
            }
            $table .= '</th>';
            $columnCounter++;
        }
        // for each item we have one checkbox with: name="'.$this->id.'[]"
        // the value of this checkbox is the item ID
        // If we have additional options their name must be as specified in the includeComponentsFields object (a few lines above) followed by the item ID.
        // For example name="'.$this->id.'_full_'.$item->id.'"  which would result in the HTML as name="com_k2_full_99".
        $table .= '</tr>
        </thead>
        <tbody>';

        $k = 0;
        $itemOrder = '';
        foreach ($items as $d) {
            $checked = (in_array($d->id, $items_selected)) ? 'checked="checked"' : '';
            $table .= '<tr class="row' . $k . '">';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '[]" value="' . $d->id . '" ' . $checked . '/></td>';
            $table .= '<td>' . $d->name . '</td>';
            $table .= '<td>' . $d->title . '</td>';
            $table .= '<td>' . $d->message . '</td>';
            $table .= '<td nowrap="nowrap">' . CFactory::getUser($d->creator)->name . '</td>';
            $table .= '<td nowrap="nowrap">' . $d->created . '</td>';
            $table .= '</tr>';
            $itemOrder .= $d->id . ';';
            $k = ($k) ? 0 : 1;
        }
        $table .= '</tbody>
        <tfoot>
        <tr>
        <td colspan="15">' . $pagination->getListFooter() . '</td>
        </tr>
        </tfoot>
        </table>
        <input type="hidden" name="' . $this->id . 'Order" id="' . $this->id . 'Order" value="' . $itemOrder . '" />';
        // this is a hidden input, which is used to store the item order when dragging'n'dropping.

        return $table;
    }

    /*
    * This function is called by the AJAX preview and it inserts the selected items into the template by replacing its placeholder.
    * If the "table of contents" option was selected it will also add the title to the article_titles object.
    *
    * @param String containing the complete template.
    * @param String path to the template folder. Used to create absolute image URLs.
    * @param Object containing the selected items and options.
    * @param Bool Table of content type. true = link, false = anchor.
    * @return Array containing template, article_titles and an optional (error) message (in this case if no placeholder was found in the template).
    */
    public function insert_jomsocial_discussions($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type) {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models');
        $createModel = JModelLegacy::getInstance('Create', 'joomailermailchimpintegrationModel');

        $response = array();
        // define abs paths for regex
        $abs_path  = '$1="' . JURI::root() . '$2$3';
        $imagepath = '$1="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $template_folder . '/$2$3';

        // extract the placeholders from the template
        $regex = '!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is';
        preg_match($regex, $template, $repeater);
        $regex = '!<#jomsocialdiscussions#[^>]*>(.*)<#/jomsocialdiscussions#>!is';
        preg_match($regex, $template, $jsRepeater);

        if (isset($repeater[0])) {
            $repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
        } else {
            $response['template'] = $template;
            $response['msg'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">' .
            '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>' .
            '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
            JTEXT::_('JM_NO_JOMSOCIAL_DISCUSSIONS_PLACEHOLDER') .
            '</div></div>';
            $repeater[0] = '';
            $error = true;
        }

        //If a placeholder was found start iterating through the items and insert them into
        if ($repeater[0]) {
            // convert relative to absolute image paths
            $repeater = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
            $repeater = str_ireplace(array('<#jomsocialdiscussionsrepeater#>','<#/jomsocialdiscussionsrepeater#>'), '', $repeater);

            $processedContent = '';
            if (count($componentsPostData)) {
                // CFactory
                require_once(JPATH_SITE . '/components/com_community/libraries/core.php');
                $jsDiscussions = '';

                $lang = JFactory::getLanguage();
                $lang->load('com_community', JPATH_SITE);

                $userLink = '<a href="%s">%s</a>';
                $langString = JText::_('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION');
                //var_dump($langString);die;

                foreach ($componentsPostData as $item) {
                    $itemId = $item['itemId'];
                    $discussion = $this->getJomsocialDiscussion($itemId);
                    $jsUser = CFactory::getUser($discussion->creator);

                    $profileLink = $createModel::getSefLink($discussion->creator, 'com_community', 'profile');
                    $discLink = $createModel::getSefLink(array($discussion->groupid,$discussion->id), 'com_community', 'discussion');

                    //$profileLink = JURI::root() . CRoute::_('index.php?option=com_community&view=profile&userid=' . $discussion->creator);
                    //$discLink = JURI::root() . CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
                    $thumb = '<a href="' . $profileLink . '"><img src="' . $jsUser->getThumbAvatar() . '" alt="'
                        . $jsUser->getDisplayName() . '" title="' . $jsUser->getDisplayName() . '" border="0" /></a>';

                    $discTmp = sprintf($userLink, $profileLink, $jsUser->getDisplayName())
                        . JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION', $discLink, $discussion->title)
                        . ':<br />' . $discussion->message;
                    $discTmp = str_ireplace('_QQQ_', '"', $discTmp);

                    $discTmp = '<table valign="top">
                    <tr>
                    <td valign="top">' . $thumb . '</td>
                    <td valign="top" style="padding: 0 0 0 10px;">' . $discTmp . '</td>
                    </tr>
                    </table>';

                    $discTmp = str_ireplace('<#jsDiscussionContent#>', $discTmp, $repeater);
                    $jsDiscussions .= $discTmp;
                }
                $jsDiscussions = str_ireplace(array('<#jomsocialdiscussionsrepeater#>', '<#/jomsocialdiscussionsrepeater#>'), '', $jsDiscussions);

                $processedContent = preg_replace('!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is', $jsDiscussions, $jsRepeater[0]);
                $processedContent = str_ireplace(array(
                    '<#jomsocialdiscussions#>',
                    '<#/jomsocialdiscussions#>',
                    '<#jomsocialdiscussionsrepeater#>',
                    '<#/jomsocialdiscussionsrepeater#>'), '', $processedContent);
            }

            // remove tiny mce stuff like mce_src="..."
            $processedContent = preg_replace('(mce_style=".*?")', '', $processedContent);
            $processedContent = preg_replace('(mce_src=".*?")',   '', $processedContent);
            $processedContent = preg_replace('(mce_href=".*?")',  '', $processedContent);
            $processedContent = preg_replace('(mce_bogus=".*?")', '', $processedContent);
            // convert relative to absolute paths
            $processedContent = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $processedContent);
            // escape dollar signs
            $processedContent = str_ireplace( '$' , '\$', $processedContent);

            // remove placeholders
            $processedContent = str_ireplace(array('<#jomsocialdiscussionsrepeater#>', '<#/jomsocialdiscussionsrepeater#>'), '', $processedContent);

            // insert profiles
            $processedContent = preg_replace('!<#jomsocialdiscussions#>(.*)<#/jomsocialdiscussions#>!is', $processedContent, $template);

            // remove placeholders
            $response['template'] = str_ireplace(array('<#jomsocialdiscussions#>', '<#/jomsocialdiscussions#>'), '', $processedContent);
        }

        return $response;
    }

    public function insert(&$template) {
        if (!$this->app) {
            $this->app = JFactory::getApplication();
        }
        $template_folder = $this->app->input->getString('template_folder');
        $componentsOptions = array();
        $componentsPostData = array();

        $items = $this->app->input->get($this->id, array());
        foreach ($items as $i) {
            $componentsPostData[] = array('itemId' => $i);
        }

        $result = $this->insert_jomsocial_discussions($template, $template_folder, $componentsOptions, $componentsPostData, 0);
        $template = $result['template'];
    }

    public function addPlaceholderToTemplateEditor() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_discussions', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#jomsocialdiscussions#>'
            . '<h3 class="mainColumnTitle">Discussions</h3><table width="100%"><#jomsocialdiscussionsrepeater#><tr>'
            . '<td><#jsDiscussionContent#></td></tr><#/jomsocialdiscussionsrepeater#></table><#/jomsocialdiscussions#>\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/>'
            . '<label for="' . $this->id . '">' . JText::_('JM_JOMSOCIAL_DISCUSSIONS') . '</label>';

        return $data;
    }
}
