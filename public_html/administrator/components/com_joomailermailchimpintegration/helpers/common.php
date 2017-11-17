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

/**
* EmailAddressValidator Class
* http://code.google.com/p/php-email-address-validation/
* Released under New BSD license
* http://www.opensource.org/licenses/bsd-license.php
**/
class EmailAddressValidator {

    /**
    * Check email address validity
    * @param   strEmailAddress     Email address to be checked
    * @return  True if email is valid, false if not
    */
    public function check_email_address($strEmailAddress) {
        
        // If magic quotes is "on", email addresses with quote marks will
        // fail validation because of added escape characters. Uncommenting
        // the next three lines will allow for this issue.
        if (get_magic_quotes_gpc()) {
            $strEmailAddress = stripslashes($strEmailAddress);
        }

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $strEmailAddress)) {
            return false;
        }

        // Check email length - min 3 (a@a), max 256
        if (!$this->check_text_length($strEmailAddress, 3, 256)) {
            return false;
        }

        // Split it into sections using last instance of "@"
        $intAtSymbol = strrpos($strEmailAddress, '@');
        if ($intAtSymbol === false) {
            // No "@" symbol in email.
            return false;
        }
        $arrEmailAddress[0] = substr($strEmailAddress, 0, $intAtSymbol);
        $arrEmailAddress[1] = substr($strEmailAddress, $intAtSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes. We also remove
        // characters escaped with backslash, and the backslash
        // character.
        $arrTempAddress[0] = preg_replace('/\./' ,'' ,$arrEmailAddress[0]);
        $arrTempAddress[0] = preg_replace('/"[^"]+"/' ,'' ,$arrTempAddress[0]);
        $arrTempAddress[1] = $arrEmailAddress[1];
        $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
        // Then check - should be no "@" symbols.
        if (strrpos($strTempAddress, '@') !== false) {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!$this->check_local_portion($arrEmailAddress[0])) {
            return false;
        }

        // Check domain portion
        if (!$this->check_domain_portion($arrEmailAddress[1])) {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;

    }

    /**
    * Checks email section before "@" symbol for validity
    * @param   strLocalPortion     Text to be checked
    * @return  True if local portion is valid, false if not
    */
    protected function check_local_portion($strLocalPortion) {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!$this->check_text_length($strLocalPortion, 1, 64)) {
            return false;
        }
        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $arrLocalPortion = explode('.', $strLocalPortion);
        for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
            if (!preg_match('.^('
            .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'
            .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
            .'|'
            .    '("[^\\\"]{0,62}")'
            .')$.'
            ,$arrLocalPortion[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
    * Checks email section after "@" symbol for validity
    * @param   strDomainPortion     Text to be checked
    * @return  True if domain portion is valid, false if not
    */
    protected function check_domain_portion($strDomainPortion) {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->check_text_length($strDomainPortion, 1, 255)) {
            return false;
        }
        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
            .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/'
            ,$strDomainPortion) ||
            preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
                .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/'
                ,$strDomainPortion)) {
                return true;
        } else {
            $arrDomainPortion = explode('.', $strDomainPortion);
            if (sizeof($arrDomainPortion) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0, $max = sizeof($arrDomainPortion); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->check_text_length($arrDomainPortion[$i], 1, 63)) {
                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                .'([A-Za-z0-9]+))$/', $arrDomainPortion[$i])) {
                    return false;
                }
                if ($i == $max - 1) { // TLD cannot be only numbers
                    if (strlen(preg_replace('/[0-9]/', '', $arrDomainPortion[$i])) <= 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
    * Check given text length is between defined bounds
    * @param   strText     Text to be checked
    * @param   intMinimum  Minimum acceptable length
    * @param   intMaximum  Maximum acceptable length
    * @return  True if string is within bounds (inclusive), false if not
    */
    protected function check_text_length($strText, $intMinimum, $intMaximum) {
        // Minimum and maximum are both inclusive
        $intTextLength = strlen($strText);
        if (($intTextLength < $intMinimum) || ($intTextLength > $intMaximum)) {
            return false;
        } else {
            return true;
        }
    }

}


/**
* xml2array() will convert the given XML text to an array in the XML structure.
* Link: http://www.bin-co.com/php/scripts/xml2array/
* Arguments : $contents - The XML text
*             $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
*             $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
* Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
* Examples: $array =  xml2array(file_get_contents('feed.xml'));
*              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
*/
class xml2array{

    function parse($contents, $get_attributes=1, $priority = 'tag') {
        if (!isset($contents) || !$contents) return array();

        if (!function_exists('xml_parser_create')) {
            //print "'xml_parser_create()' function not found!";
            return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values) return;//Hmm...

        //Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array; //Refference

        //Go through the tags.
        $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes,$value);//Remove existing values, or there will be trouble

            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data);//We could use the array by itself, but this cooler.

            $result = array();
            $attributes_data = array();

            if (isset($value)) {
                if ($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }

            //Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {
                    if ($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }

            //See tag status and do the needed.
            if ($type == "open") {//The starting of the tag '<tag>'
                $parent[$level-1] = &$current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag.'_'.$level] = 1;

                    $current = &$current[$tag];

                } else { //There was another element with the same tag name

                    if (isset($current[$tag][0])) {//If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag.'_'.$level] = 2;

                        if (isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                    $current = &$current[$tag][$last_item_index];
                }

            } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if ($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

                } else { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;

                    } else { //If it is not an array...
                        $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                    }
                }

            } elseif ($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level-1];
            }
        }

        return($xml_array);
    }

}


final class SimpleXMLArrayHelper {
    /**
    * @uses SimpleXMLElement
    * @see http://php.net/manual/en/class.simplexmlelement.php
    * @author Shvakov Kirill shvakov@gmail.com
    * @category utility
    * @version 0.1 (11.03.2011)
    */
    private
    $rootNode   = 'root',
    $attributes = null,
    $simpleXml  = null;
    /**
    * @param string $rootNode
    * @param array $attributes
    * @return void
    */
    public function __construct($rootNode = 'root', array $attributes = array()) {
        $this->rootNode   = $rootNode;
        $this->attributes = $attributes;
    }
    /**
    * @param array $arrayData
    * @return object
    */
    public function setArray(array $arrayData) {
        $this->simpleXml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?>\n<" . $this->rootNode . ">\n</" . $this->rootNode . ">");
        if (is_array($this->attributes) && count($this->attributes)) {
            foreach ($this->attributes as $attribute => $attributeValue) {
                $this->simpleXml->addAttribute($attribute, (string) $attributeValue);
            }
        }
        $this->createXml($this->simpleXml, $arrayData);
        return $this;
    }
    /**
    * @param SimpleXMLElement $node
    * @param array $arrayData
    * @return void
    */
    private function createXml(SimpleXMLElement $node, array $arrayData) {
        foreach($arrayData as $nodeName => $data) {
            $value       = !empty($data['value']) ? $data['value'] : '';
            $currentNode = $node->addChild($nodeName, $value);
            if (!empty($data) && is_array($data)) {
                foreach ($data as $attribute => $attributeValue) {
                    if (!empty($attributeValue) && is_array($attributeValue)) {
                        foreach ($attributeValue as $child) {
                            if (!is_array($child)) {
                                continue;
                            }
                            $this->createXml($currentNode, $child);
                        }
                        continue;
                    }
                    if (substr($attribute, 0, 1) != '@') {
                        continue;
                    }
                    $currentNode->addAttribute(substr($attribute, 1), $attributeValue);
                }
            }
        }
    }
    /**
    * @param string $xmlString
    * @return object
    */
    public function setXml($xmlString) {
        if (file_exists($xmlString)) {
            $xmlString = file_get_contents($xmlString);
        }
        $this->simpleXml = new SimpleXMLElement($xmlString);
        return $this;
    }
    /**
    * @param undefined
    * @return string
    */
    public function asXml() {
        return $this->simpleXml->asXML();
    }
    /**
    * @param undefined
    * @return array
    */
    public function asArray() {
        return self :: toArray($this->simpleXml);
    }
    /**
    * @param mixed array || SimpleXMLElement
    * @return array
    */
    private function toArray($objects) {
        $array   = array();
        $objects = (array) $objects;
        foreach ($objects as $key => $object) {
            if ($object instanceof SimpleXMLElement || is_array($object)) {
                $object = self :: toArray($object);
            }
            $array = array_merge($array, array($key => $object));
        }
        return $array;
    }
    /**
    * @param undefined
    * @return string
    */
    public function __toString() {
        return __CLASS__ . ' :: __toString()';
    }
}


function removeSpecialCharacters($string) {
    // Replace other special chars
    $specialCharacters = array(
        '!' => '',
        '"' => '',
        "'" => '',
        "^" => '',
        '#' => '',
        '$' => '',
        '%' => '',
        '&' => '',
        '*' => '',
        '@' => '',
        '.' => '',
        '€' => '',
        '£' => '',
        '+' => '',
        '=' => '',
        '§' => '',
        '(' => '',
        ')' => '',
        '\\' => '',
        "/" => ''
    );

    while (list($character, $replacement) = each($specialCharacters)) {
        $string = str_replace($character, '-' . $replacement . '-', $string);
    }

    $string = strtr($string,
        "ÀÂÃÄÅáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÎìíîïÙÚÛÜùúûüÿÑñ-",
        "AAAAAaaaaaOOOOOOooooooEEEEeeeeCcIIiiiiUUUUuuuuyNn_"
    );

    // Remove all remaining other unknown characters
    $string = preg_replace('/[^a-zA-Z0-9-]/', '', $string);
    $string = preg_replace('/^[-]+/', '', $string);
    $string = preg_replace('/[-]+$/', '', $string);
    $string = preg_replace('/[-]{2,}/', '', $string);

    return $string;
}
