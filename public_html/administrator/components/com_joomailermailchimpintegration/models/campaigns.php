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

class joomailermailchimpintegrationModelCampaigns extends jmModel {

    public $cacheGroup = 'joomlamailerReports';

    public function getClientDetails() {
        return $this->getModel('main')->getClientDetails();
    }

    public function getCampaigns($filters = array(), $count = 25, $offset = 0, $sort_field = '', $sort_dir = 'DESC') {
        $cacheID = 'Campaigns_' . implode('_', $filters) . '_' . $count . '_' . $offset;

        if ($sort_field) {
            $cacheID .= '_' . $sort_field . '_' . $sort_dir;
        }

        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $params = array_merge($filters, array(
                'count'  => $count,
                'offset' => $offset
            ));
            if ($sort_field) {
                $params['sort_field'] = $sort_field;
                $params['sort_dir'] = $sort_dir;
            }
            $data = $this->getMcObject()->campaigns($params);

            // fix send time according to Joomla offset
            $Jconfig = JFactory::getConfig();
            $tzoffset = $Jconfig->get('offset');
            if ($tzoffset != 'UTC') {
                foreach ($data['campaigns'] as $index => $campaign) {
                    date_default_timezone_set('Europe/London');
                    $datetime = new DateTime($campaign['send_time']);
                    $timeZone = new DateTimeZone($tzoffset);
                    $datetime->setTimezone($timeZone);
                    $data['campaigns'][$index]['send_time'] = $datetime->format('Y-m-d H:i:s');
                }
            }
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup), true);
    }

    public function getReport($campaignId) {
        $cacheID = 'Reports_' . $campaignId;
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = $this->getMcObject()->reports($campaignId);
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup), true);
    }

    public function getClicks($cid, $count = 25, $offset = 0) {
        return $this->getMcObject()->reportsClickDetails($cid, null, $count, $offset);
    }

    public function getClickDetails($cid, $id) {
        return $this->getMcObject()->reportsClickDetails($cid, $id);
    }

    public function getClickDetailsMembers($cid, $id, $count = 25, $offset = 0) {
        return $this->getMcObject()->reportsClickDetails($cid, $id, $count, $offset, true);
    }

    public function getCampaignData($cid) {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__joomailermailchimpintegration_campaigns')
            ->where($this->db->qn('cid') . ' = ' . $this->db->q($cid));
        $result = $this->db->setQuery($query)->loadObjectList();

        if (!$result) {
            $result = $this->getMcObject()->campaigns(array('campaign_id' => $cid));
        }

        return $result;
    }

    public function getUserDetails($email) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn('id'))
            ->from('#__users')
            ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
        $id = $this->db->setQuery($query)->loadResult();

        return ($id) ? JFactory::getUser($id) : false;
    }

    public function getAbuse($cid, $count = 25, $offset = 0) {
        return $this->getMcObject()->reportsAbuseReports($cid, $count, $offset);
    }

    public function getRecipients($cid, $count = 25, $offset = 0) {
        return $this->getMcObject()->reportsSentTo($cid, $count, $offset);
    }

    public function getUnsubscribes($cid, $count = 25, $offset = 0) {
        return $this->getMcObject()->reportsUnsubscribes($cid, $count, $offset);
    }

    public function getAdvice($campaignId) {
        $cacheID = 'ReportsAdvice_' . $campaignId;
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = $this->getMcObject()->reportsAdvice($campaignId);
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup), true);
    }

    public function getEepUrlStats($campaignId) {
        $cacheID = 'ReportsEepUrl_' . $campaignId;
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = $this->getMcObject()->reportsEepUrl($campaignId);
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup), true);
    }

    public function getLocationStats($campaignId) {
        $cacheID = 'ReportsLocations_' . $campaignId;
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = $this->getMcObject()->reportsLocations($campaignId);
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup), true);
    }

    /**
     * Get an array of iso2 country codes and their names
     * Source: http://country.io/names.json
     *
     * @return array
     */
    public static function getIso2Countrylist() {
        return json_decode('{"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique"}', true);
    }

    /**
     * Convert ISO2 country code to name
     */
    public static function iso2ToName($iso2) {
        $iso2 = ($iso2 == 'UK') ? 'GB' : $iso2;

        return strtr($iso2, self::getIso2Countrylist());
    }
}
