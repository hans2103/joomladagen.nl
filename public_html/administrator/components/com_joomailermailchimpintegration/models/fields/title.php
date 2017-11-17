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
*/

// no direct access
defined('_JEXEC') or die('Restricted Access');


class JFormFieldTitle extends JFormField {

    public function getLabel() {
        $title = (isset($this->element['label']) && $this->element['label'] != '') ? JText::_($this->element['label']) : ' ';

        return '<label>' . $title . '</label>';
    }

    public function getInput() {
        $description = (isset($this->element['description']) && $this->element['description'] != '') ?
            JText::_($this->element['description']) : ' ';

        return '<div style="margin-top: 8px; float: left;">' . $description . '</div>';
    }
}
