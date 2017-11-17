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

class plgJoomlamailerCom_virtuemart extends JPlugin {

    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'com_virtuemart';
    private $data;

    protected $db, $app;

    // determine if VM is installed
    private function isInstalled() {
        $this->db = JFactory::getDBO();
        $query = $this->db->getQuery(true);
        $query->select($this->db->qn('extension_id'))
            ->from('#__extensions')
            ->where($this->db->qn('type') . ' = ' . $this->db->q('component'))
            ->where($this->db->qn('element') . ' = ' . $this->db->q('com_virtuemart'));
        $this->db->setQuery($query);

        return ($this->db->loadResult() ? true : false);
    }

    // get VM product thumbnail
    private function getThumb($thumb, $full = '') {
        if (!$thumb && $full) {
            $thumb = $full;
        }

        return JURI::root() . $thumb;
    }

    /**
     * Use this function to load required language files
     */
    private function loadLanguageFiles() {
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_com_virtuemart', JPATH_ADMINISTRATOR);
        $jlang->load('com_virtuemart', JPATH_ADMINISTRATOR);
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

        $this->db = JFactory::getDBO();
        $this->app = JFactory::getApplication();

        $this->loadLanguageFiles();

        $data = array();

        // specify the Tabslider's title
        $data['title']= JText::_('JM_VIRTUEMART_PRODUCTS');

        // set the article list
        $data['table'] = $this->renderTable($this->getData());

        return $data;
    }

    /**
    * Returns the query
    * @return string The query to be used to retrieve the data
    */
    private function buildQuery() {
        $where = array($this->db->qn('p.published') . ' = ' . $this->db->q(1));
        $filter_cat	= $this->app->getUserStateFromRequest($this->id . '_catid', $this->id . '_catid',	0, 'int');

        if (!$filter_cat) {
            $filter_cat = $this->app->input->getString($this->id . '_catid', 0, '', 'int');
        }

        if ($filter_cat > 0) {
            $where[] = $this->db->qn('pc.virtuemart_category_id') . ' = ' . $this->db->Quote($filter_cat);
        }

        $search	= $this->app->input->getString('search_' . $this->id, '');
        $search	= \Joomla\String\StringHelper::strtolower($search);

        if (isset($search) && $search!= '') {
            $searchEscaped = $this->db->q('%' . $this->db->escape($search, true) . '%', false);
            $where[] = $this->db->qn('pl.product_name') . ' LIKE ' . $searchEscaped;
        }

        // include previously selected articles
        $selected = $this->app->input->getString($this->id, null);
        if (count($selected)) {
            foreach ($selected as $sel) {
                $ex = explode('|', $sel);
                $selectedId[] = $ex[0];
                $selectedPrice[] = $ex[1];
                $selectedCat[] = $ex[2];
            }

            for ($i = 0; $i < count($selected); $i++ ) {
                $selectedCond[] = $this->db->qn('p.virtuemart_product_id') . ' = ' . $this->db->q($selectedId[$i]) .
                    ' AND ' . $this->db->qn('prc.product_price') . ' = ' . $this->db->q($selectedPrice[$i]) .
                    ' AND ' . $this->db->qn('pc.virtuemart_category_id') . ' = ' . $this->db->q($selectedCat[$i]);
            }
            $where[max(array_keys($where))] .= ' OR ((' . implode(') OR (', $selectedCond) . '))';
        }

        $fields = $this->db->qn(
            array('p.virtuemart_product_id' , 'pl.product_name', 'prc.product_price', 'cur.currency_code_3',
                'med.file_url_thumb', 'med.file_url'),
            array('product_id', 'product_name', 'product_price', 'product_currency', 'product_thumb_image', 'product_image')
        );
        $query = $this->db->getQuery(true)
            ->select('SQL_CALC_FOUND_ROWS ' . implode(', ', $fields))
            ->select('GROUP_CONCAT(' . $this->db->qn('pc.virtuemart_category_id') . ' ORDER BY c.category_name SEPARATOR "<br />") AS category_id')
            ->select('GROUP_CONCAT(' . $this->db->qn('c.category_name') . ' ORDER BY c.category_name SEPARATOR "<br />") AS category_name')
            ->from('#__virtuemart_products AS p')
            ->join('INNER', '#__virtuemart_products_' . $this->activeLanguage() . ' AS pl ON (pl.virtuemart_product_id = p.virtuemart_product_id)')
            ->join('INNER', '#__virtuemart_product_prices AS prc ON (prc.virtuemart_product_id = p.virtuemart_product_id AND prc.virtuemart_shoppergroup_id = "0")')
            ->join('INNER', '#__virtuemart_currencies AS cur ON (cur.virtuemart_currency_id = prc.product_currency)')
            ->join('INNER', '#__virtuemart_product_medias AS m ON (m.virtuemart_product_id = p.virtuemart_product_id)')
            ->join('INNER', '#__virtuemart_medias AS med ON (med.virtuemart_media_id = m.virtuemart_media_id)')
            ->join('LEFT', '#__virtuemart_product_categories AS pc ON (pc.virtuemart_product_id = p.virtuemart_product_id)')
            ->join('LEFT', '#__virtuemart_categories_' . $this->activeLanguage() . ' AS c ON (c.virtuemart_category_id = pc.virtuemart_category_id)')
            ->where($where)
            ->order($this->db->qn(array('category_name', 'pl.product_name', 'prc.product_price')))
            ->group('pc.virtuemart_product_id');

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
                        if (isset($this->data[$x]) && $io == $this->data[$x]->product_id . '***' .
                            $this->data[$x]->product_price . '***' . $this->data[$x]->category_id) {
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
        $this->db->setQuery('SELECT FOUND_ROWS();');
        return $this->db->loadResult();
    }

    private function getPagination() {
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
        $table = '<script language="javascript" type="text/javascript">
            includeComponents[Object.keys(includeComponents).length] = "' . $this->id . '";
           includeComponentsOptions["' . $this->id . '"] = { 0: "vm_order", 1: "vm_price", 2: "vm_curr_first", 3: "vm_curr_symbol", 4: "vm_img", 5: "vm_link", 6: "vm_short_desc", 7: "vm_desc", 8: "' . $this->id . '_language" };
        </script>';

        if (!count($items)) {
            return $table;
        }

        $this->loadLanguageFiles();

        $items_selected = $this->app->input->get($this->id, array());
        $filter_cat	= $this->app->input->getInt($this->id . '_catid', -1);
        $search	= $this->app->getUserStateFromRequest('search_' . $this->id, 'search_' . $this->id, '', 'string');
        $search	= \Joomla\String\StringHelper::strtolower($search);
        $ttImage = '../media/com_joomailermailchimpintegration/backend/images/info.png';

        // specify the column titles.
        // Translations may be entered in the plugin's language files.
        $columns = array(
            JText::_('ID'),
            JText::_('JM_INCLUDE'),
            JText::_('JM_NAME'),
            JText::_('JM_THUMBNAIL'),
            JText::_('JM_CATEGORY'),
            JText::_('JM_PRICE')
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
            'width="20" nowrap="nowrap"',
            'width="40" nowrap="nowrap" class="sorttable_nosort"',
            'nowrap="nowrap"',
            'width="70" nowrap="nowrap" class="sorttable_nosort"',
            'width="80" nowrap="nowrap"',
            'width="80" nowrap="nowrap"'
        );

        // whenever you submit the form, for example to apply a filter, do: document.getElementById('offset').value='1';
        // this will open the second tab ('content') after the page reloaded.
        $table .= '<table>
        <tr>
        <td nowrap="nowrap">
        ' . JText::_('JM_FILTERS') . ':
        <input type="text" name="search_' . $this->id . '" id="search_' . $this->id . '" value="' . $this->app->getUserStateFromRequest('search_' . $this->id, 'search_' . $this->id, '', 'string' ) . '" class="text_area" onchange="document.getElementById(\'offset\').value=\'1\';this.form.submit();" style="margin:0;" />
        <button onclick="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';this.form.submit();">' . JText::_('JM_GO') . '</button>
        <button onclick="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';document.getElementById(\'search_' . $this->id . '\').value=\'\';document.getElementById(\'' . $this->id . '_catid\').value=\'-1\';this.form.submit();">' . JText::_('JM_RESET') . '</button>
        </td>
        <td align="center" width="100%"></td>
        <td nowrap="nowrap">' . $this->getLanguageDropDown() . '</td>
        <td nowrap="nowrap">' . $this->getShopperGroupsDropdown() . '</td>
        <td nowrap="nowrap">' . $this->catDropDown($filter_cat) . '</td>
        </tr>
        </table>';

        if (!count($items)) {
            return $table;
        }

        $vmor = $this->app->input->getString('vmor', false);
        if (!$vmor) {
            $vmor = $this->app->input->getString('vm_order', '');
        }
        $table .= '<table width="100%" class="componentsOptionsTable">
        <tr>
        <td>
        <label for="vm_order">'.  JText::_('JM_ORDER_BY') . '
        <select name="vm_order" id="vm_order" style="width: 170px;">';
        $orderOptions = array(
            'name_asc' => 'JM_NAME_ASC',
            'name_desc' => 'JM_NAME_DESC',
            'price_asc' => 'JM_PRICE_ASC',
            'price_desc' => 'JM_PRICE_DESC',
            'cat_asc' => 'JM_CATEGORY_ASC',
            'cat_desc' => 'JM_CATEGORY_DESC',
            'random' => 'JM_RANDOM' );
        foreach ($orderOptions as $k => $v) {
            $selected = ($k == $vmor) ? 'selected="selected"' : '';
            $table .= '<option value="' . $k . '" ' . $selected . '>' . JText::_($v)."</option>\n";
        }

        $table .= '</select></label></td>';


        $vmpr = $this->app->input->getString('vm_price', $this->app->input->getString('vmpr', true));
        $checked = ($vmpr) ? ' checked="checked"' : '';
        $table .= '<td><label for="vm_price">' . JText::_('JM_VM_DISPLAY_PRICE')
            . '<input class="checkbox" type="checkbox" name="vm_price" id="vm_price" value="1"' . $checked . ' /></label></td>';

        $vm_curr_symbol = $this->app->input->getString('vm_curr_symbol', true);
        $checked = ($vm_curr_symbol) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="' . JText::_('JM_VM_CURRENCY_SYMBOL_INFO') . '"><label for="vm_curr_symbol">'
            . JText::_('JM_VM_CURRENCY_SYMBOL')
            . '<input class="checkbox" type="checkbox" name="vm_curr_symbol" id="vm_curr_symbol" value="1"' . $checked . ' /></label></td>';

        $vmcf = $this->app->input->getString('vm_curr_first', $this->app->input->getString('vmcf', false));
        $checked = ($vmcf) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="' . JText::_('JM_VM_CURRENCY_FIRST_INFO') . '"><label for="vm_curr_first">'
            . JText::_('JM_VM_CURRENCY_FIRST')
            . '<input class="checkbox" type="checkbox" name="vm_curr_first" id="vm_curr_first" value="1"' . $checked . ' /></label></td>';

        $vmimg = $this->app->input->getString('vm_img', $this->app->input->getString('vmimg', true));
        $checked = ($vmimg) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="' . JText::_('JM_VM_DISPLAY_IMAGE_INFO') . '"><label for="vm_img">'
            . JText::_('JM_VM_DISPLAY_IMAGE')
            . '<input class="checkbox" type="checkbox" name="vm_img" id="vm_img" value="1"' . $checked . ' /></label></td>';

        $vmlnk = $this->app->input->getString('vm_link', $this->app->input->getString('vmlnk', true));
        $checked = ($vmlnk) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="'.JText::_( 'JM_VM_LINK_INFO' ).'"><label for="vm_link">'
            . JText::_('JM_VM_LINK')
            . '<input class="checkbox" type="checkbox" name="vm_link" id="vm_link" value="1"' . $checked . ' /></label></td>';

        $vmsdesc = $this->app->input->getString('vm_short_desc', $this->app->input->getString('vmsdesc', true));
        $checked = ($vmsdesc) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="' . JText::_('JM_VM_SHOW_SHORT_DESCRIPTION_INFO') . '"><label for="vm_short_desc">'
            . JText::_('JM_VM_SHOW_SHORT_DESCRIPTION')
            . '<input class="checkbox" type="checkbox" name="vm_short_desc" id="vm_short_desc" value="1"' . $checked . ' /></label></td>';

        $vmdesc = $this->app->input->getString('vm_desc', $this->app->input->getString('vmdesc', false));
        $checked = ($vmdesc) ? ' checked="checked"' : '';
        $table .= '<td class="hasTip" title="' . JText::_('JM_VM_SHOW_PRODUCT_DESCRIPTION_INFO') . '"><label for="vm_desc">'
            . JText::_('JM_VM_SHOW_PRODUCT_DESCRIPTION')
            . '<input class="checkbox" type="checkbox" name="vm_desc" id="vm_desc" value="1"' . $checked . ' /></label></td>';

        $table .= '</tr></table>';

        // assign class="adminlist" to the table to apply css-styling from the component.
        // assign class="sortable" to the table to allow sorting by clicking the column titles.
        $table .= '<table class="adminlist sortable" id="' . $this->id . '"><thead><tr>';
        $columnCounter = 0;
        foreach ($columns as $cTitle) {
            $table .= '<th ' . $columnAttributes[$columnCounter] . '>'.  $cTitle;
            if ($columnTooltips[$columnCounter] != '') {
                $table .= '&nbsp;<img src="' . $ttImage . '" class="hasTip" title="' . JText::_($columnTooltips[$columnCounter]) . '" />';
            }
            $table .= '</th>';
            $columnCounter++;
        }
        // for each item we have one checkbox with: name="'.$this->id.'[]"
        // the value of this checkbox is the item ID
        // If we have additional options their name must be as specified in the includeComponentsFields object (a few lines above) followed by the item ID.
        // For example name="'.$this->id.'_full_'.$item->id.'"  which would result in the HTML as name="com_k2_full_99".
        $table .= '</tr></thead><tbody>';
        $k = 0;
        $itemOrder = '';
        foreach ($items as $item) {
            if (in_array($item->product_id . '|' . $item->product_price . '|' . $item->category_id, $items_selected)) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            $table .= '<tr class="row' . $k . '">';
            $table .= '<td align="center">' . $item->product_id . '</td>';
            $table .= '<td align="center"><input type="checkbox" name="' . $this->id . '[]" value="' . $item->product_id . '|' . $item->product_price . '|' . $item->category_id . '" ' . $checked . '/></td>';
            $table .= '<td>' . $item->product_name . '</td>';
            $table .= '<td align="center"><a href="' . $this->getThumb($item->product_thumb_image, $item->product_image) . '" class="modal">';
            $table .= '<img src="' . $this->getThumb($item->product_thumb_image, $item->product_image) . '" height="30"/>';
            $table .= '</a></td>';
            $table .= '<td align="center">' . $item->category_name . '</td>';
            $table .= '<td align="center">' . number_format($item->product_price, 2) . ' ' . $item->product_currency . '</td>';
            $table .= '</tr>';
            $itemOrder .= $item->product_id . '|' . $item->product_price . '|' . $item->category_id . ';';
        }

        $pagination = $this->getPagination();
        $table .= '</tbody>
        <tfoot><tr><td colspan="15">' . $pagination->getListFooter() . '</td></tr></tfoot>
        </table>
        <input type="hidden" name="' . $this->id . 'Order" id="' . $this->id . 'Order" value="' . $itemOrder . '" />';
        // this is a hidden input, which is used to store the item order when dragging'n'dropping.

        return $table;
    }

    /*
    * Creates the category dropdown filter.
    * @return String containing a HMTL select box.
    */
    private function catDropDown($active = NULL) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('cl.virtuemart_category_id', 'cl.category_name'), array('value', 'text')))
            ->from('#__virtuemart_categories AS c')
            ->join('INNER', '#__virtuemart_categories_' . $this->activeLanguage() . ' AS cl USING(virtuemart_category_id)')
            ->order('cl.category_name');
        $this->db->setQuery($query);

        $categories = array_merge(
            array(JHTML::_('select.option', '-1', '- ' . JText::_('JM_CATEGORY') . ' -')),
            $this->db->loadObjectList()
        );

        $attr = 'class="inputbox" size="1" onchange="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';document.adminForm.submit();"';
        return JHTML::_('select.genericlist', $categories, $this->id . '_catid', $attr, 'value', 'text', $active);
    }

    private function getLanguageDropDown() {
        $activeLangs = array() ;
        $language = JFactory::getLanguage();
        $jLangs = $language->getKnownLanguages(JPATH_BASE);

        foreach ($jLangs as $jLang) {
            $jLang['tag'] = strtolower(str_replace('-', '_', $jLang['tag']));
            $activeLangs[] = JHtml::_('select.option', $jLang['tag'] , $jLang['name']) ;
        }

        $attr = 'class="inputbox" size="1" onchange="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';document.adminForm.submit();"';
        return JHtml::_('select.genericlist', $activeLangs, $this->id . '_language', $attr, 'value', 'text', $this->activeLanguage());
    }

    private function activeLanguage() {
        return strtolower(str_replace('-', '_', $this->app->input->getString($this->id . '_language', 'en_gb')));
    }

    private function getShopperGroupsDropdown() {
        $active = $this->app->input->getString($this->id . '_shoppergroup', -1);
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('virtuemart_shoppergroup_id', 'shopper_group_name'), array('value', 'text')))
            ->from('#__virtuemart_shoppergroups')
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->order('ordering, virtuemart_shoppergroup_id');
        $this->db->setQuery($query);
        $groups = $this->db->loadObjectList();

        foreach ($groups as $index => $group) {
            $groups[$index]->text = JText::_($group->text);
        }

        $categories = array_merge(
            array(JHTML::_('select.option', '-1', '- ' . JText::_('JM_VM_SHOPPERGROUP') . ' -')),
            $groups
        );

        $attr = 'class="inputbox" size="1" onchange="joomlamailerJS.functions.preloader();document.getElementById(\'offset\').value=\'1\';document.adminForm.submit();"';
        return JHTML::_('select.genericlist', $categories, $this->id . '_shoppergroup', $attr, 'value', 'text', $active);
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
    public function insert_com_virtuemart($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type) {
        $this->db = JFactory::getDBO();
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models');
        $createModel = JModelLegacy::getInstance('Create', 'joomailermailchimpintegrationModel');

        $this->loadLanguageFiles();

        $response = array();
        // define abs paths for regex
        $abs_path  = '$1="' . JURI::root() . '$2$3';
        $imagepath = '$1="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $template_folder . '/$2$3';

        // extract the placeholder from the template
        $regex = '!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is';
        preg_match($regex, $template, $repeater);
        $regex = '!<#vm_products#[^>]*>(.*)<#/vm_products#>!is';
        preg_match($regex, $template, $container);
        if (isset($repeater[0])) {
            $repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
        } else {
            $response['template'] = $template;
            $response['msg'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">' .
            '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>' .
            '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
            JTEXT::_('JM_NO_VIRTUEMART_PLACEHOLDER') . '</div></div>';
            $repeater[0] = '';
            $error = true;
        }

        //If a placeholder was found start iterating through the items and insert them into
        if ($repeater[0]) {
            // convert relative to absolute image paths
            $repeater = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
            $repeater = str_ireplace( array('<#vm_repeater#>','<#/vm_repeater#>'),  '', $repeater);

            $selected = $this->app->input->getString($this->id, null);
            if (count($selected)) {
                $vm_productids	  = array();
                $vm_productprices = array();
                $vm_productcats	  = array();
                for ($i = 0; $i < count($selected); $i++) {
                    $selected[$i] = explode('|', $selected[$i]);
                    $vm_productids[$i] = $selected[$i][0];
                    $vm_productprices[$i] = $selected[$i][1];
                    $vm_productcats[$i] = $selected[$i][2];
                }
            }

            $processedContent = '';
            if (count($componentsPostData)) {
                $where = '';
                foreach ($componentsPostData as $v) {
                    list($prodId, $price, $catId) = explode('|', $v['itemId']);

                    $where .= (!$where) ? ' AND ' : ' OR ';
                    $where .= '(' . $this->db->qn('p.virtuemart_product_id') . ' = ' . $this->db->q($prodId) .
                        ' AND ' . $this->db->qn('pp.product_price') . ' = ' . $this->db->q($price) .
                        ' AND ' . $this->db->qn('c.virtuemart_category_id') . ' = ' . $this->db->q($catId) . ')';
                }

                $query = $this->db->getQuery(true)
                    ->select($this->db->qn(
                        array('p.virtuemart_product_id', 'pl.product_name', 'pl.product_s_desc', 'pl.product_desc',
                            'pp.product_price', 'cur.currency_code_3', 'pc.virtuemart_category_id', 'cl.category_name',
                            'med.file_url_thumb', 'med.file_url', 'p.virtuemart_product_id'),
                        array('product_id', 'product_name', 'product_s_desc', 'product_desc', 'product_price',
                            'product_currency', 'category_id', 'category_name', 'product_thumb_image', 'product_image',
                            'tax_rate')))
                    ->from('#__virtuemart_products AS p')
                    ->join('INNER', '#__virtuemart_product_prices AS pp ' .
                        'ON (' . $this->db->qn('pp.virtuemart_product_id') . ' = ' . $this->db->qn('p.virtuemart_product_id') . ')')
                    ->join('INNER', '#__virtuemart_products_' . $this->activeLanguage() . ' AS pl ' .
                        'ON (' . $this->db->qn('pl.virtuemart_product_id') . ' = ' . $this->db->qn('p.virtuemart_product_id') . ')')
                    ->join('INNER', '#__virtuemart_product_categories AS pc ' .
                        'ON (' . $this->db->qn('pc.virtuemart_product_id') . ' = ' . $this->db->qn('p.virtuemart_product_id') . ')')

                    ->join('INNER', '#__virtuemart_categories AS c ' .
                        'ON (' . $this->db->qn('c.virtuemart_category_id') . ' = ' . $this->db->qn('pc.virtuemart_category_id') . ')')
                    ->join('INNER', '#__virtuemart_categories_' . $this->activeLanguage() . ' AS cl ' .
                        'ON (' . $this->db->qn('cl.virtuemart_category_id') . ' = ' . $this->db->qn('c.virtuemart_category_id') . ')')

                    ->join('INNER', '#__virtuemart_currencies AS cur ' .
                        'ON (' . $this->db->qn('cur.virtuemart_currency_id') . ' = ' . $this->db->qn('pp.product_currency') . ')')
                    ->join('LEFT', '#__virtuemart_product_medias AS m ' .
                        'ON (' . $this->db->qn('m.virtuemart_product_id') . ' = ' . $this->db->qn('p.virtuemart_product_id') . ')')
                    ->join('LEFT', '#__virtuemart_medias AS med ' .
                        'ON (' . $this->db->qn('med.virtuemart_media_id') . ' = ' . $this->db->qn('m.virtuemart_media_id') . ')')
                    ->where($this->db->qn('p.published') . ' = ' . $this->db->q(1) . $where);

                switch ($componentsOptions['vm_order']) {
                    case 'price_desc':
                        $query->order($this->db->qn('p.product_price') . ' DESC');
                        break;
                    case 'price_asc':
                        $query->order($this->db->qn('p.product_price') . ' ASC');
                        break;
                    case 'name_desc':
                        $query->order($this->db->qn('pl.product_name') . ' DESC');
                        break;
                    case 'name_asc':
                        $query->order($this->db->qn('pl.product_name') . ' ASC');
                        break;
                    case 'cat_desc':
                        $query->order($this->db->qn('cl.category_name') . ' DESC');
                        break;
                    case 'cat_asc':
                        $query->order($this->db->qn('cl.category_name') . ' ASC');
                        break;
                    case 'random':
                        $query->order('RAND()');
                        break;
                }
                /*
                $query = 'SELECT DISTINCT a.virtuemart_product_id as product_id,a.product_name,a.product_s_desc,a.product_desc,
                b.product_price, cur.currency_code_3 as product_currency,
                c.virtuemart_category_id as category_id,
                d.category_name,
                med.file_url_thumb as product_thumb_image, med.file_url as product_image,
                a.virtuemart_product_id as tax_rate
                FROM #__virtuemart_product_prices as b
                INNER JOIN #__virtuemart_products as a ON a.virtuemart_product_id = b.virtuemart_product_id
                INNER JOIN #__virtuemart_product_categories as c ON a.virtuemart_product_id = c.virtuemart_product_id
                INNER JOIN #__virtuemart_categories as d ON c.virtuemart_category_id = d.virtuemart_category_id
                INNER JOIN #__virtuemart_currencies as cur ON b.product_currency = cur.virtuemart_currency_id
                INNER JOIN #__virtuemart_product_medias as m ON a.virtuemart_product_id = m.virtuemart_product_id
                INNER JOIN #__virtuemart_medias as med ON m.virtuemart_media_id = med.virtuemart_media_id
                WHERE a.published = 1 '
                .$where
                .$order;
                */

                $this->db->setQuery($query);
                $products = $this->db->loadObjectList();

                if (count($products)) {
                    require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
                    VmConfig::loadConfig();
                    VmConfig::setdbLanguageTag();
                    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models');
                    $productModel = JModelLegacy::getInstance('Product', 'VirtueMartModel');

                    foreach ($products as $prod) {
                        $product_content = '';
                        $prod->tax_rate = 0;

                        $product_content .= $prod->product_name . '<br />';
                        if ($componentsOptions['vm_price']) {

                            $prices = $productModel->getPrice($prod->product_id, 1);
                            $price = $prices['salesPrice'];

                            $currency = VirtueMartModelVendor::getVendorCurrency(1);
                            if ($componentsOptions['vm_curr_symbol']) {
                                $prod->product_currency = $currency->currency_symbol;
                            } else {
                                $prod->product_currency = $currency->currency_code_3;
                            }
                            if ($componentsOptions['vm_curr_first']) {
                                $product_content .= $prod->product_currency . ' ' . number_format($price, 2) . '<br />';
                            } else {
                                $product_content .= number_format($price, 2) . ' ' . $prod->product_currency . '<br />';
                            }
                        }
                        if ($componentsOptions['vm_img']) {
                            $product_content .= '<img src="' . $this->getThumb($prod->product_thumb_image, $prod->product_image) . '" border="0" />';
                        }

                        if ($componentsOptions['vm_link']) {
                            $link = $createModel::getSefLink(array($prod->product_id, $prod->category_id), 'com_virtuemart');
                            $product_content = '<a href="' . $link . '">' . $product_content . '</a>';
                        }

                        if ($componentsOptions['vm_short_desc']) {
                            $product_content .= '<p>' . $prod->product_s_desc . '</p>';
                        }

                        if ($componentsOptions['vm_desc']) {
                            $product_content .= '<p>' . $prod->product_desc . '</p>';
                        }

                        $processedContent .= str_ireplace('<#vm_content#>', $product_content, $repeater);
                    }

                    $processedContent = preg_replace('!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is', $processedContent, $container[0]);
                    $processedContent = str_ireplace(array(
                        '<#vm_repeater#>',
                        '<#/vm_repeater#>',
                        '<#vm_products#>',
                        '<#/vm_products#>'), '', $processedContent);
                }
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

            // insert articles
            $template = preg_replace('!<#vm_products#>(.*)<#/vm_products#>!is', $processedContent, $template);

            // remove placeholders
            $response['template'] = str_ireplace(array('<#vm_products#>', '<#/vm_products#>'), '', $template);
        }

        return $response;
    }

    public function insert (&$template) {
        $template_folder = $this->app->input->getString('template_folder');
        $componentsOptions = array();
        $componentsPostData = array();
        $items = $this->app->input->get($this->id, array());
        foreach ($items as $i) {
            $componentsPostData[$i] = 1;
        }

        $componentsOptions['vm_order'] = $this->app->input->getString('vm_order', 'name_asc');
        $componentsOptions['m_link'] = $this->app->input->getString('vm_link', 0);
        $componentsOptions['vm_price'] = $this->app->input->getString('vm_price', 0);
        $componentsOptions['vm_tax'] = $this->app->input->getString('vm_tax', 0);
        $componentsOptions['vm_curr_first'] = $this->app->input->getString('vm_curr_first', 0);
        $componentsOptions['vm_img'] = $this->app->input->getString('vm_img', 0);
        $componentsOptions['vm_short_desc'] = $this->app->input->getString('vm_short_desc', 0);
        $componentsOptions['vm_desc'] = $this->app->input->getString('vm_desc', 0);

        $toc_type = $this->app->input->getString('table_of_content_type', 0);

        $result = $this->insert_com_virtuemart($template, $template_folder, $componentsOptions, $componentsPostData, $toc_type);

        $template = $result['template'];
    }

    public function addPlaceholderToTemplateEditor() {
        $this->loadLanguageFiles();

        $data = array();
        $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#vm_products#><br /><span class="sideColumnTitle">Top Products</span><table><#vm_repeater#><tr><td align="center"><p><#vm_content#></p></td></tr><#/vm_repeater#></table><br /><#/vm_products#><br />\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/><label for="' . $this->id . '">' . JText::_('JM_VIRTUEMART_PRODUCTS') . '</label>';

        return $data;
    }
}
