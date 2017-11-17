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

defined('JPATH_PLATFORM') or die;

class JFormFieldBirthday extends JFormField {

	protected $type = 'Birthday';

	protected function getInput() {
        if (is_array($this->value)) {
            $values[0] = $this->value['month'];
            $values[1] = $this->value['day'];
        } else {
            $values = explode('/', $this->value);
        }
        $this->description = (empty($this->description)) ? 'DD/MM' : $this->description;
        $fields = array(
            'DD' => $this->getDropdown(31, $this->name . '[day]', @$values[1]),
            'MM' => $this->getDropdown(12, $this->name . '[month]', @$values[0])
        );
        $outputFields = explode('/', $this->description);

        $input = $fields[$outputFields[0]] . ' / ' . $fields[$outputFields[1]];

        if (!empty($this->description)) {
            $input .= ' <span class="description">(' . $this->description . ')</span>';
        }

        return $input;
	}

    private function getDropdown($max, $name, $selected = '') {
        $options = array();
        for ($i = 1; $i <= $max; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $options[] = array('value' => $i, 'label' => $i);
        }
        return JHtml::_('select.genericlist', $options, $name, 'style="width:55px;"', 'value', 'label', $selected);
    }
}
