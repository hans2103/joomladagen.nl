<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
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

class McSignupHelper {

    public static function getCountryList($name, $title, $id = '', $class = '') {
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/campaigns.php');
        $options = joomailermailchimpintegrationModelCampaigns::iso2ToName();

        $result = '<select name="' . $name . '" id="' . $id . '" class="' . $class . '" title="' . $title . '">
            <option value="">' . $title . '</option>';
        foreach ($options as $key => $value) {
            $result .= '<option value="' . $key . '">' . ucwords(strtolower($value)) . '</option>';
        }
        $result .= '</select>';

        return $result;
    }

    public static function getFieldLabel($label) {
        JLoader::register('LanguagesHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/languages.php');
        $languageConstant = LanguagesHelper::filterKey($label);
        $res = JText::_($languageConstant);
        if ($res == $languageConstant) {
            $res = $label;
        }

        return $res;
    }
}
