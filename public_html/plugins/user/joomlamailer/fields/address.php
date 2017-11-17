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

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/campaigns.php');

class JFormFieldAddress extends JFormField {
	protected $type = 'Address';

	protected function getInput() {
        $input = '<style type="text/css">.addressfield { margin-bottom: 1em !important; }</style>';
        $this->class = trim($this->class . ' addressfield');
        $class = 'class="' . $this->class . '" ';

        $input .= '<input type="text" name="' . $this->name . '[addr1]" ' . $class .
            'placeholder="' . JText::_('PLG_USER_JOOMLAMAILER_STREET_ADDRESS') . '" value="' . @$this->value['addr1'] . '" /><br />';

        $input .= '<input type="text" name="' . $this->name . '[addr2]" ' . $class .
            'placeholder="' . JText::_('PLG_USER_JOOMLAMAILER_ADDRESS_LINE_2') . '" value="' . @$this->value['addr2'] . '" /><br />';

        $input .= '<input type="text" name="' . $this->name . '[city]" ' . $class .
            'placeholder="' . JText::_('PLG_USER_JOOMLAMAILER_CITY') . '" value="' . @$this->value['city'] . '" /><br />';

        $input .= '<input type="text" name="' . $this->name . '[state]" ' . $class .
            'placeholder="' . JText::_('PLG_USER_JOOMLAMAILER_STATE_PROVINCE_REGION') . '"  value="' . @$this->value['state'] . '" /><br />';

        $input .= '<input type="text" name="' . $this->name . '[zip]" ' . $class .
            'placeholder="' . JText::_('PLG_USER_JOOMLAMAILER_ZIP_POSTAL') . '" value="' . @$this->value['zip'] . '" /><br />';

        $options = array(
            array('value' => '', 'label' => JText::_('PLG_USER_JOOMLAMAILER_COUNTRY'))
        );

        $countries = joomailermailchimpintegrationModelCampaigns::getIso2Countrylist();
        foreach ($countries as $iso2 => $label) {
            $countries[] = array('value' => $iso2, 'label' => $label);
        }

        var_dump($options);die;

        $input .= JHtml::_('select.genericlist', $options, @$this->name . '[country]', '', 'value', 'label', $this->value);

		return $input;
	}
}
