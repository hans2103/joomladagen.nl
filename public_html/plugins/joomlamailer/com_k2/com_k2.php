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
defined('_JEXEC') or die('Restricted access');

class plgJoomlamailerCom_k2 extends JPlugin {

    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'com_k2';
    private $data;

    protected $db, $app;

    /**
     * Determine if K2 is installed and enabled
     *
     * @return bool
     */
    private function isInstalled() {
        $this->db = JFactory::getDBO();
        $query = $this->db->getQuery(true)
            ->select($this->db->qn('extension_id'))
            ->from($this->db->qn('#__extensions'))
            ->where($this->db->qn('type') . ' = ' . $this->db->q('component'))
            ->where($this->db->qn('element') . ' = ' . $this->db->q('com_k2'))
            ->where($this->db->qn('enabled') . ' = ' . $this->db->q(1));

        return ($this->db->setQuery($query)->loadResult()) ? true : false;
    }

    /**
    * This function is called by the joomlamailer create view and allows you to add a tabslider containing a list of articles.
    * @return an Array of objects containing the data
    */
    public function getArticleList() {
        $this->db = JFactory::getDBO();
        $this->app = JFactory::getApplication();

        // is component is not installed - don't do anything
        if (!$this->isInstalled()) {
            return false;
        }

        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, null, true);

        $data = array();

        // specify the Tabslider's title
        $data['title'] = JText::_('JM_K2_ITEMS');

        // set the article list
        $data['table'] = $this->renderTable($this->getData());

        return $data;
    }

    /**
    * Returns the query
    * @return string The query to be used to retrieve the data
    */
    private function buildQuery() {
        $where = 'i.published = 1';

        $w = array();

        $filter_k2cat = $this->app->getUserStateFromRequest($this->id . '_catid', $this->id . '_catid', 0, 'int');
        if (!$filter_k2cat) {
            $filter_k2cat = $this->app->input->getInt($this->id . '_catid', 0);
        }
        if ($filter_k2cat > 0) {
            $w[] = 'i.catid = ' . $this->db->q($filter_k2cat);
        }

        $search	= \Joomla\String\StringHelper::strtolower($this->app->input->getString('search_' . $this->id));
        if ($search != '') {
            $searchEscaped = $this->db->q('%' . $search . '%');
            $w[] = 'i.title LIKE ' . $searchEscaped;
        }

        if (count($w)) {
            $where .= ' AND ((' . implode(' AND ', $w) . ')';

            // include previously selected articles
            $selected = $this->app->input->get($this->id, array());
            if (count($selected)) {
                $where .= ' OR i.id IN (' . implode(',', $selected) . ')';
            }

            $where .= ')';
        }

        $query = $this->db->getQuery(true);
        $fields = $this->db->qn(
                array('i.id', 'i.title', 'i.created', 'u.name', 'c.name'),
                array('id', 'title', 'created', 'name', 'category'));
        $query->select('SQL_CALC_FOUND_ROWS ' . implode(', ', $fields))
            ->from('#__k2_items AS i')
            ->join('LEFT', '#__users as u ON (i.created_by = u.id)')
            ->join('LEFT', '#__k2_categories AS c ON (i.catid = c.id)')
            ->where($where);

        return $query;
    }

    /**
    * Retrieves the data
    * @return an Array of objects containing the data
    */
    private function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->data)) {
            $limitstart = $this->app->input->getString($this->id . 'limitstart', 0);
            $limit      = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));

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
                foreach($itemOrder as $index => $io){
                    for ($x = 0; $x < count($this->data); $x++) {
                        if (isset($this->data[$x]) && $io == $this->data[$x]->id) {
                            $itemsReordered[$index] = $this->data[$x];
                            unset($this->data[$x]);
                        }
                    }
                }
                $itemsReordered = array_merge($itemsReordered, $this->data);
                $this->data = $itemsReordered;
            }
        }

        return $this->data;
    }

    private function getTotal() {
        $this->db->setQuery('SELECT FOUND_ROWS();');
        return $this->db->loadResult();
    }

    private function getPagination(){
        jimport('joomla.html.pagination');
        $limitstart = $this->app->input->getString($this->id . 'limitstart', 0);
        $limit = $this->app->input->getString($this->id . 'limit', $this->app->getCfg('list_limit'));
        return new JPagination($this->getTotal(), $limitstart, $limit, $this->id);
    }

    /**
    * Creates the table of articles/items.
    * @return a String containing the table as HTML.
    */
    private function renderTable($items) {
        // The following two lines are mandatory and will be used for the AJAX preview.
        // The first line adds $this->id to the includeComponents object. Leave it as it is.
        // The second line defines the available additional options (checkboxes, radio buttons).
        // In this case: 0: show intro or fulltext, 1: show readmore link, 2: add item to table of contents
        $table = '<script language="javascript" type="text/javascript">
        includeComponents[Object.keys(includeComponents).length] = "' . $this->id . '";
        includeComponentsFields["' . $this->id . '"] = { 0 : "' . $this->id . '_full_", 1 : "' . $this->id . '_readmore_", 2: "' . $this->id . '_toc_" };
        </script>';

        if (!count($items)) {
            return $table;
        }

        $pagination = $this->getPagination();
        $items_selected = $this->app->input->get($this->id, array());
        $filter_cat	= $this->app->input->getInt($this->id . '_catid', -1);
        $search	= \Joomla\String\StringHelper::strtolower($this->app->getUserStateFromRequest('search_' . $this->id, 'search_' . $this->id, '', 'string'));

        // specify the column titles.
        // Translations may be entered in the plugin's language files.
        $columns = array(
            JText::_('ID'),
            JText::_('JM_INCLUDE'),
            JText::_('JM_TEXT'),
            JText::_('JM_READ_MORE'),
            JText::_('JM_ADD_TO_TABLE_OF_CONTENT'),
            JText::_('JM_TITLE'),
            JText::_('JM_CATEGORY'),
            JText::_('JM_AUTHOR'),
            JText::_('JM_DATE' )
        );
        // Specify an info tooltip, which will be available via the Info icon next to the column title.
        // Empty string means no tooltip will be created.
        $columnTooltips = array(
            '',
            '',
            JText::_('JM_TOOLTIP_TEXT'),
            JText::_('JM_TOOLTIP_READMORE'),
            JText::_('JM_TOOLTIP_ADD_TO_TOC'),
            JText::_('JM_TOOLTIP_TITLE'),
            '',
            '',
            ''
        );
        // specify the attributes for each column.
        //  class="sorttable_nosort"  disables the table to be sortable by clicking on this column title.
        $columnAttributes = array(
            'width="20" nowrap="nowrap"',
            'width="40" nowrap="nowrap" class="sorttable_nosort"',
            'width="70" nowrap="nowrap" class="sorttable_nosort"',
            'width="80" nowrap="nowrap" class="sorttable_nosort"',
            'width="60" nowrap="nowrap" class="sorttable_nosort"',
            'nowrap="nowrap"',
            'width="80" nowrap="nowrap"',
            'width="120" nowrap="nowrap"',
            'width="80" nowrap="nowrap"'
        );

        // whenever you submit the form, for example to apply a filter, do: document.getElementById('offset').value='1';
        // this will open the second tab ('content') after the page reloaded.
        $table .= '<table>
        <tr>
        <td nowrap="nowrap">' . JText::_('JM_FILTERS') . ':
        <input type="text" name="search_' . $this->id . '" id="search_' . $this->id . '" value="' . $search .'" class="text_area" onchange="document.getElementById(\'offset\').value=\'1\';this.form.submit();" />
        <button onclick="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';this.form.submit();">' . JText::_('JM_GO') . '</button>
        <button onclick="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';document.getElementById(\'search_' . $this->id . '\').value=\'\';this.form.getElementById(\'filter_type\').value=\'0\';this.form.getElementById(\'filter_logged\').value=\'0\';this.form.submit();">' . JText::_('JM_RESET') . '</button>
        </td>
        <td align="center" width="100%">
        </td>
        <td nowrap="nowrap">' . $this->catDropDown($filter_cat) . '</td>
        </tr>
        </table>';

        // assign class="adminlist" to the table to apply css-styling from the component.
        // assign class="sortable" to the table to allow sorting by clicking the column titles.
        $table .= '<table class="adminlist sortable" id="' . $this->id . '">
        <thead>
        <tr>';

        $ttImage = '../media/com_joomailermailchimpintegration/backend/images/info.png';
        $columnCounter = 0;
        foreach ($columns as $cTitle) {
            $table .= '<th ' . $columnAttributes[$columnCounter] . '>' . $cTitle;
            if ($columnTooltips[$columnCounter] != ''){
                $table .= '&nbsp;<img src="' . $ttImage . '" class="hasTip" title="' . JText::_($columnTooltips[$columnCounter]) . '" />';
            }
            $table .= '</th>';
            $columnCounter++;
        }
        // for each item we have one checkbox with: name="'.$this->id.'[]"
        // the value of this checkbox is the item ID
        // If we have additional options their name must be as specified in the includeComponentsFields object (a few lines above) followed by the item ID.
        // For example name="'.$this->id.'_full_'.$item->id.'"  which would result in the HTML as name="com_k2_full_99".
        $table .=	'</tr>
        </thead>
        <tbody>';
        $k = 0;
        $itemOrder = '';
        foreach ($items as $item) {
            $checked = (in_array($item->id, $items_selected)) ? 'checked="checked"' : '';
            $table .= '<tr class="row' . ($k++ % 2) . '">';
            $table .= '<td align="center">' . $item->id . '</td>';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '[]" value="' . $item->id . '" ' . $checked . '/></td>';
            $table .= '<td>';
            $table .= '<label for="' . $this->id . '_full_' . $item->id . '_0">' . JText::_('JM_INTRO') . '</label>';
            $table .= '<input type="radio" name="' . $this->id . '_full_' . $item->id . '" id="' . $this->id . '_full_' . $item->id . '_0" value="0" checked="checked">';
            $table .= '<br />';
            $table .= '<label for="' . $this->id . '_full_' . $item->id . '_1">' . JText::_('JM_FULL') . '</label>';
            $table .= '<input type="radio" name="' . $this->id . '_full_' . $item->id . '" id="' . $this->id . '_full_' . $item->id . '_1" value="1">';
            $table .= '</td>';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '_readmore_' . $item->id . '" id="' . $this->id . '_readmore_' . $item->id . '" value="1" checked="checked"></td>';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '_toc_' . $item->id . '" id="' . $this->id . '_toc_' . $item->id . '" value="1" checked="checked"></td>';
            $table .= '<td>' . $item->title . '</td>';
            $table .= '<td align="center">' . $item->category . '</td>';
            $table .= '<td align="center">' . $item->name . '</td>';
            $table .= '<td align="center">' . substr($item->created, 0, -9) . '</td>';
            $table .= '</tr>';
            $itemOrder .= $item->id . ';';
        }
        $table .= '</tbody>
        <tfoot>
        <tr>
        <td colspan="99">' . $pagination->getLimitBox() . $pagination->getListFooter() . '</td>
        </tr>
        </tfoot>
        </table>
        <input type="hidden" name="' . $this->id . 'Order" id="' . $this->id . 'Order" value="' . $itemOrder . '" />';
        // this is a hidden input, which is used to store the item order when dragging'n'dropping.

        return $table;
    }

    /*
    * Creates the category dropdown filter.
    * @return String containing a HMTL select box.
    */
    private function catDropDown($active = '') {
        require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
        $categoriesModel = K2Model::getInstance('Categories', 'K2Model');
        $options = $categoriesModel->categoriesTree();
        $options = array_merge(array(JHtml::_('select.option', '-1', '- ' . JText::_('Select Category').' -')), $options);
        $attribs = 'class="inputbox" size="1" onchange="joomlamailerJS.functions.preloader()'
            . ';document.getElementById(\'offset\').value=\'1\';document.adminForm.submit();"';

        return JHtml::_('select.genericlist', $options, $this->id . '_catid', $attribs, 'value', 'text', $active);
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
    public function insert_com_k2($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type) {
        $this->db = JFactory::getDBO();
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models');
        $createModel = JModelLegacy::getInstance('Create', 'joomailermailchimpintegrationModel');

        $response = array();
        $article_titles = array();
        // define abs paths for regex
        $abs_path  = '$1="' . JURI::root() . '$2$3';
        $imagepath = '$1="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $template_folder . '/$2$3';

        // extract the placeholder from the template
        $regex = '!<#K2repeater#[^>]*>(.*)<#/K2repeater#>!is';
        preg_match($regex, $template, $repeater);
        if (isset($repeater[0])) {
            $repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
        } else {
            $response['template'] = $template;
            $response['article_titles'] = array();
            $response['msg'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">'.
            '<img src="../media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>'.
            '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
                JTEXT::_('JM_NO_K2_PLACEHOLDER') .
            '</div></div>';
            $repeater[0] = '';
            $error = true;
        }

        // If a placeholder was found start iterating through the items and insert them into the template
        if ($repeater[0]) {
            // convert relative to absolute image paths
            $repeater = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);

            $processedContent = '';
            if (isset($componentsPostData) && count($componentsPostData)) {
                foreach ($componentsPostData as $v) {
                    $itemId   = $v['itemId'];
                    $full     = $v[$this->id . '_full_' .$itemId];
                    $readmore = $v[$this->id . '_readmore_' . $itemId];
                    $add2toc  = $v[$this->id . '_toc_' . $itemId];

                    $query = $this->db->getQuery(true);
                    $query->select($this->db->qn(array('title', 'introtext', 'fulltext')))
                        ->from('#__k2_items')
                        ->where($this->db->qn('id') . ' = ' . $this->db->q($itemId));
                    $this->db->setQuery($query);
                    $article = $this->db->loadObjectList();

                    foreach ($article as $art) {
                        $link = $createModel::getSefLink($itemId, 'com_k2');

                        if ($add2toc) {
                            if ($toc_type) {
                                $article_titles[] = '<a href="' . $link . '">' . $art->title . '</a>';
                            } else {
                                $article_titles[] = '<a href="#' . urlencode($art->title . '_' . $itemId) . '">' . $art->title . '</a>';
                            }
                        }

                        $html_title = ($readmore) ? '<a href="' . $link . '">' . $art->title . '</a>' : $art->title;
                        $html_title .= '<a name="' . urlencode($art->title . '_' . $itemId) . '"></a>';

                        $html_content  = $art->introtext;
                        if ($full == 1) {
                            $html_content .= ' ' . $art->fulltext;
                        }
                        // Read more link
                        if ($readmore) {
                            $html_content .= '<p><a href="' . $link . '">' . JTEXT::_('JM_READ_MORE') . '</a></p>';
                        }

                        $content = str_ireplace('<#title#>', $html_title, $repeater);
                        $content = str_ireplace('<#content#>', $html_content, $content);
                        $processedContent .= $content;
                    }
                }
            }

            $response['article_titles'] = $article_titles;

            // remove tiny mce stuff like mce_src="..."
            $processedContent = preg_replace('#((mce_style|mce_src|mce_href|mce_bogus)="[^"]+")#', '', $processedContent);

            // convert relative to absolute paths
            $processedContent = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $processedContent);

            // remove placeholders
            $processedContent = str_ireplace(array('<#K2repeater#>','<#/K2repeater#>'),  '', $processedContent);

            // escape dollar signs
            $processedContent = str_ireplace('$' , '\$', $processedContent);

            // insert articles
            $template = preg_replace('!<#K2repeater#>(.*)<#/K2repeater#>!is', $processedContent, $template);

            // remove placeholders
            $response['template'] = str_ireplace(array('<#K2repeater#>','<#/K2repeater#>'), '', $template);

        }

        return $response;
    }

    /*
    * This function is called when the created campaign is saved.
    * It creates an object and passes it on to the AJAX function insert_com_k2
    *
    * @param String containing the complete template as reference.
    */
    public function insert (&$template) {
        $template_folder = $this->app->input->getString('template_folder');
        $componentsOptions = '';
        $componentsPostData = array();
        $articles = $this->app->input->get($this->id, array());
        foreach ($articles as $a) {
            $full     = $this->app->input->getString($this->id . '_full_' . $a);
            $readmore = $this->app->input->getString($this->id . '_readmore_' . $a);
            $add2toc  = $this->app->input->getString($this->id . '_toc_' . $a);

            $componentsPostData[] = array(
                'itemId' => $a,
                $this->id . '_full_' . $a => $full,
                $this->id . '_readmore_' . $a => $readmore,
                $this->id . '_toc_' . $a => $add2toc
            );
        }
        $toc_type = $this->app->input->getString('table_of_content_type', 0);

        $result = $this->insert_com_k2($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type);

        // merge article titles with titles passed from other plugins
        $article_titles = json_decode($this->app->input->get('article_titles', '', 'RAW'));
        if (!is_array($article_titles)) {
            $article_titles = array();
        }
        if (isset($result['article_titles'])) {
            $article_titles = array_merge($article_titles, $result['article_titles']);
        }
        $this->app->input->set('article_titles', json_encode($article_titles));

        $template = $result['template'];
    }

    /*
    * This function adds the placeholder to the template editor.
    *
    * @return Array containing javascript and a checkbox + label.
    */
    public function addPlaceholderToTemplateEditor() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_com_k2', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#K2repeater#><br /><h2 class="mainColumnTitle"><#title#></h2><#content#><br /><#/K2repeater#>\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/><label for="' . $this->id . '">' . JText::_('JM_K2_ITEMS') . '</label>';

        return $data;
    }
}
