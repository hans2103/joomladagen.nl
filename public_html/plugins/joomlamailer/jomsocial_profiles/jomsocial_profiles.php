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

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');

class plgJoomlamailerJomsocial_profiles extends JPlugin {

    private $db, $app;
    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'jomsocial_profiles';
    private $data;

    // determine if Jomsocial is installed
    private function isInstalled() {
        if (!$this->db || !$this->app) {
            $this->db = JFactory::getDBO();
            $this->app = JFactory::getApplication();
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn('extension_id'))
            ->from('#__extensions')
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
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, null, true);

        $data = array();

        // specify the Tabslider's title
        $data['title'] = JText::_('JM_JOMSOCIAL_PROFILES');

        // set the article list
        $data['table'] = $this->renderTable($this->getData());

        return $data;
    }

    /**
    * Returns the query
    * @return string The query to be used to retrieve the data
    */
    private function buildQuery() {
        if (!$this->db) {
            $this->db = JFactory::getDBO();
        }
        $query = $this->db->getQuery(true)
            ->select('SQL_CALC_FOUND_ROWS *')
            ->from('#__users')
            ->where($this->db->qn('block') . ' = ' . $this->db->q(0))
            ->order($this->db->qn('id') . ' DESC');

        return $query;
    }

    /**
    * Retrieves the data
    * @return an Array of objects containing the data
    */
    private function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->data)) {
            if (!$this->db || !$this->app) {
                $this->db = JFactory::getDBO();
                $this->app = JFactory::getApplication();
            }
            $limitstart = $this->app->input->getString($this->id . 'limitstart', 0);
            $limit = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));

            $query = $this->buildQuery();
            $this->db->setQuery($query, $limitstart, $limit);
            $this->data = $this->db->loadObjectList();

            // apply stored article order
            $itemOrder = $this->app->input->getString($this->id . 'Order', 0);
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
                            unset($this->data[$x]);
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
        $this->db->setQuery('SELECT FOUND_ROWS();');

        return $this->db->loadResult();
    }

    private function getPagination() {
        if (!$this->app) {
            $this->app = JFactory::getApplication();
        }
        jimport('joomla.html.pagination');
        $limitstart = $this->app->input->getString($this->id . 'limitstart', 0 );
        $limit = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));
        return new JPagination($this->getTotal(), $limitstart, $limit, $this->id);
    }

    private function getProfileFields() {
        if (!$this->db) {
            $this->db = JFactory::getDBO();
        }
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__community_fields')
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' != ' . $this->db->q('group'));
        $this->db->setQuery($query);
        $fields = $this->db->loadObjectList();

        return $fields;
    }

    private function getFieldValues($uid, $fields) {
        if (!$this->db) {
            $this->db = JFactory::getDBO();
        }
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('f.name', 'f.type', 'v.value')))
            ->from('#__community_fields AS f')
            ->join('', '#__community_fields_values AS v ON (' . $this->db->qn('v.field_id') . ' = ' . $this->db->qn('f.id') . ')')
            ->where($this->db->qn('v.user_id') . ' = ' . $this->db->q($uid))
            ->where($this->db->qn('v.field_id') . ' IN ("' . implode('","',  $fields) . '")');
        $this->db->setQuery($query);
        $values = $this->db->loadObjectList();

        return $values;
    }

    /**
    * Creates the table of articles/items.
    * @return a String containing the table as HTML.
    */
    private function renderTable($items) {
        if (!$this->db || !$this->app) {
            $this->db = JFactory::getDBO();
            $this->app = JFactory::getApplication();
        }
        $pagination = $this->getPagination();
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, null, true);
        // CFactory
        require_once (JPATH_SITE . '/components/com_community/libraries/core.php');
        $items_selected = $this->app->input->get($this->id, array());
        $filter_cat	= $this->app->input->getInt($this->id . '_catid', -1);
        $search	= $this->app->getUserStateFromRequest('search_' . $this->id, 'search_' . $this->id, '', 'string');
        $search	= \Joomla\String\StringHelper::strtolower($search);
        $tt_image = '../../../administrator/components/com_joomailermailchimpintegration/assets/images/info.png';

        // The following two lines are mandatory and will be used for the AJAX preview.
        // The first line adds $this->id to the includeComponents object. Leave it as it is.
        // The second line defines the available additional options (checkboxes, radio buttons).
        $table = '<script language="javascript" type="text/javascript">
        includeComponents[Object.keys(includeComponents).length] = "' . $this->id . '";
        includeComponentsOptions["' . $this->id . '"] = {0: "' . $this->id . 'Fields"};
        </script>';

        if (!count($items)) {
            return $table;
        }

        // specify the column titles.
        // Translations may be entered in the plugin's language files.
        $columns = array(
            JText::_('ID'),
            JText::_('JM_INCLUDE'),
            '',
            JText::_('JM_NAME'),
            JText::_('JM_EMAIL_ADDRESS'),
            JText::_('JM_REGISTRATION'),
            JText::_('JM_LAST_VISIT'),
            JText::_('JM_PROFILE_PRIVACY')
        );
        // Specify an info tooltip, which will be available via the Info icon next to the column title.
        // Empty string means no tooltip will be created.
        $columnTooltips = array(
            '',
            '',
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
            'width="20" nowrap="nowrap"',
            'width="40" nowrap="nowrap" class="sorttable_nosort"',
            'width="40" nowrap="nowrap" class="sorttable_nosort"',
            'nowrap="nowrap"',
            'width="150" nowrap="nowrap" class="sorttable_nosort"',
            'width="150" nowrap="nowrap" class="sorttable_nosort"',
            'width="150" nowrap="nowrap" class="sorttable_nosort"',
            'width="60" nowrap="nowrap" class="sorttable_nosort"'
        );

        $fields_selected = $this->app->input->get($this->id . 'Fields', array());
        $table .= '<table>
        <tr>
        <td>' . JText::_('JM_SELECT_PROFILE_FIELDS') . ': </td>
        <td>
        <select name="' . $this->id . 'Fields[]" id="' . $this->id . 'Fields" multiple="multiple" size="5">';
        foreach ($this->getProfileFields() as $field) {
            $selected = (in_array($field->id, $fields_selected)) ? ' selected="selected"' : '';
            $table .= '<option value="' . $field->id . '"' . $selected . '>' . $field->name . '</option>';
        }
        $table .= '</select>
        </td>
        <td>' . JText::_('JM_RESPECT_PRIVACY') . '</td>
        </tr>
        </table>';


        // assign class="adminlist" to the table to apply css-styling from the component.
        // assign class="sortable" to the table to allow sorting by clicking the column titles.
        $table .= '<table class="adminlist sortable" id="' . $this->id . '">
        <thead>
        <tr>';
        $columnCounter = 0;
        foreach ($columns as $cTitle) {
            $table .= '<th ' . $columnAttributes[$columnCounter] . '>' . $cTitle;
            if ($columnTooltips[$columnCounter] != '') {
                $table .= '&nbsp;' . JHTML::tooltip(JText::_($columnTooltips[$columnCounter]), JText::_($cTitle), $tt_image, '');
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
        foreach ($items as $u) {
            $jsUser = CFactory::getUser($u->id);
            $params = $jsUser->getParams();
            switch($params->get('privacyProfileView', 0)) {
                case 0:
                default:
                    $privacy = 'public';
                    break;
                case 20:
                    $privacy = 'members only';
                    break;
                case 30:
                    $privacy = 'friends only';
                    break;
                case 40:
                    $privacy = 'self';
                    break;
            }

            $thumb = '<img src="' . $jsUser->getThumbAvatar() . '" alt="'.  $u->name . '" title="' . $u->name . '" />';
            $checked = (in_array($u->id, $items_selected)) ? ' checked="checked"' : '';
            $table .= '<tr class="row' . $k . '">';
            $table .= '<td align="center">' . $u->id . '</td>';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '[]" value="' . $u->id . '"' . $checked . '/></td>';
            $table .= '<td align="center">' . $thumb . '</td>';
            $table .= '<td>' . $u->name . '</td>';
            $table .= '<td align="center">' . $u->email . '</td>';
            $table .= '<td align="center">' . $u->registerDate . '</td>';
            $table .= '<td align="center">' . $u->lastvisitDate . '</td>';
            $table .= '<td align="center">' . $privacy . '</td>';
            $table .= '</tr>';
            $itemOrder .= $u->id . ';';
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
    public function insert_jomsocial_profiles($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type) {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models');
        $createModel = JModelLegacy::getInstance('Create', 'joomailermailchimpintegrationModel');

        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, null, true);

        $response = array();
        // define abs paths for regex
        $abs_path  = '$1="' . JURI::root() . '$2$3';
        $imagepath = '$1="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $template_folder . '/$2$3';

        // extract the placeholders from the template
        $regex = '!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is';
        preg_match($regex, $template, $repeater);
        $regex = '!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is';
        preg_match($regex, $template, $fieldsRepeater);
        $regex = '!<#jomsocialprofiles#[^>]*>(.*)<#/jomsocialprofiles#>!is';
        preg_match($regex, $template, $jsRepeater);

        if(isset($repeater[0])) {
            $repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
        } else {
            $response['template'] = $template;
            $response['msg'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">' .
            '<img src="'.JURI::root() . 'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>' .
            '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
            JTEXT::_('JM_NO_JOMSOCIAL_PROFILES_PLACEHOLDER') .
            '</div></div>';
            $repeater[0] = '';
            $error = true;
        }

        //If a placeholder was found start iterating through the items and insert them into
        if ($repeater[0]) {
            // convert relative to absolute image paths
            $repeater = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
            $repeater = str_ireplace( array('<#jomsocialprofilesrepeater#>','<#/jomsocialprofilesrepeater#>'),  '', $repeater);

            $processedContent = '';
            if (isset($componentsPostData) && count($componentsPostData)) {
                // CFactory
                require_once (JPATH_SITE . '/components/com_community/libraries/core.php');
                $jsProfiles = '';
                foreach ($componentsPostData as $item) {
                    $itemId = $item['itemId'];
                    $jsUser = CFactory::getUser($itemId);
                    $link = $createModel::getSefLink($itemId, 'com_community', 'profile');

                    $thumb = '<a href="' . $link . '"><img src="' . $jsUser->getThumbAvatar() . '" alt="'
                        . $jsUser->getDisplayName() . '" title="' . $jsUser->getDisplayName() . '" border="0" /></a>';
                    $profiles = str_ireplace('<#jsAvatar#>', $thumb, $repeater);
                    $profiles = str_ireplace('<#jsName#>', '<a href="' . $link . '">' . $jsUser->getDisplayName() . '</a>', $profiles);

                    $fieldValues = $this->getFieldValues($itemId, (array)$componentsOptions[$this->id . 'Fields']);
                    $fields = '';
                    foreach ($fieldValues as $f) {
                        if ($f->value) {
                            if ($f->type == 'date') {
                                $f->value = substr($f->value, 0, -9);
                            }
                            $fieldsTmp = str_ireplace('<#jsFieldTitle#>', $f->name, $fieldsRepeater[0]);
                            $fieldsTmp = str_ireplace('<#jsFieldValue#>', $f->value, $fieldsTmp);
                            $fields .= $fieldsTmp;
                        }
                    }
                    $profiles = preg_replace('!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is', $fields, $profiles);
                    $profiles = str_ireplace(array('<#jsfieldsrepeater#>','<#/jsfieldsrepeater#>'), '', $profiles);

                    $jsProfiles .= $profiles;
                }

                $processedContent = preg_replace('!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is', $jsProfiles, $jsRepeater[0]);
                $processedContent = str_ireplace(array(
                    '<#jomsocialprofiles#>',
                    '<#/jomsocialprofiles#>',
                    '<#jomsocialprofilesrepeater#>',
                    '<#/jomsocialprofilesrepeater#>'), '', $processedContent);
            }
            // remove tiny mce stuff like mce_src="..."
            $processedContent = preg_replace('(mce_style=".*?")', '', $processedContent);
            $processedContent = preg_replace('(mce_src=".*?")',   '', $processedContent);
            $processedContent = preg_replace('(mce_href=".*?")',  '', $processedContent);
            $processedContent = preg_replace('(mce_bogus=".*?")', '', $processedContent);
            // convert relative to absolute paths
            $processedContent = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $processedContent);
            // escape dollar signs
            $processedContent = str_ireplace('$' , '\$', $processedContent);

            // remove placeholders
            $processedContent = str_ireplace(array('<#jomsocialprofilesrepeater#>', '<#/jomsocialprofilesrepeater#>'), '', $processedContent);

            // insert profiles
            $processedContent = preg_replace('!<#jomsocialprofiles#>(.*)<#/jomsocialprofiles#>!is', $processedContent, $template);

            // remove placeholders
            $response['template'] = str_ireplace(array('<#jomsocialprofiles#>', '<#/jomsocialprofiles#>'), '', $processedContent);
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

        $componentsOptions[$this->id . 'Fields'] = $this->app->input->get($this->id . 'Fields', array());
        $toc_type = $this->app->input->getString('table_of_content_type', 0);

        $result = $this->insert_jomsocial_profiles($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type);

        $template = $result['template'];
    }

    public function addPlaceholderToTemplateEditor() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_jomsocial_profiles', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['js'] = 'placeholders["' . $this->id . '"] = \'<#jomsocialprofiles#><h3 class="mainColumnTitle">'
            . 'Featured Members</h3><table width="100%"><#jomsocialprofilesrepeater#><tr><td width="50"><#jsAvatar#>'
            . '</td><td><#jsName#></td><td><#jsfieldsrepeater#><#jsFieldTitle#>: <#jsFieldValue#><br />'
            . '<#/jsfieldsrepeater#></td></tr><#/jomsocialprofilesrepeater#></table><#/jomsocialprofiles#>\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/>'
            . '<label for="' . $this->id . '">' . JText::_('JM_JOMSOCIAL_PROFILES') . '</label>';

        return $data;
    }
}
