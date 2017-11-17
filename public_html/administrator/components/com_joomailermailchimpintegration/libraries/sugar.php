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

require_once(JPATH_ADMINISTRATOR.'/components/com_joomailermailchimpintegration/libraries/nusoap/nusoap.php');

// Wrapper class for SugarCRM Web Services
class SugarCRMWebServices {
	// Let's define a place to store our access credentials
	var $username;
	var $password;
	var $uri;

	// We'll store the session ID here for later use
	var $session;

	// We'll initialize a brand new NuSOAP Client object into this one
	var $soap;

	// Constructor (PHP4-style)
	function SugarCRM($username, $password, $uri)
	{
		$this->username = $username;
		$this->password = $password;
		if (substr($uri, -1) == '/'){
		    $uri = substr($uri, 0, -1);
		}
		$this->soap = new nusoap_client($uri.'/soap.php');
	}

	// Login function which stores our session ID
	function login()
	{
		$result = $this->soap->call('login', array('user_auth' => array('user_name' => $this->username, 'password' => md5($this->password), 'version' => '.01'), 'application_name' => 'My Application'));
		$this->session = $result['id'];
	}

	// Create a brand new Lead, return the SOAP result
	function createLead($data)
	{
		// Parse the data and store it into a name/value catalog
		// which will then pe passed on to Sugar through SOAP
		$name_value_list = array();
		foreach($data as $key => $value)
			array_push($name_value_list, array('name' => $key, 'value' => $value));

		// Fire the set_entry call to the Leads module
		$result = $this->soap->call('set_entry', array(
			'session' => $this->session,
			'module_name' => 'Leads',
			'name_value_list' => $name_value_list
		));

		return $result;
	}
	// Create a new Contact, return the SOAP result
	function setContact($data)
	{
		$name_value_list = array();
		foreach($data as $key => $value) {
                    array_push($name_value_list, array('name' => $key, 'value' => $value));
                }

		$result = $this->soap->call('set_entry', array(
			'session' => $this->session,
			'module_name' => 'Contacts',
			'name_value_list' => $name_value_list
		));

		return $result;
	}

        // Create several new Contacts, return the SOAP result
	function setContactMulti($data)
	{
	    $name_value_list = array();
	    $i = 0;
	    foreach($data as $d) {
		$name_value_list[$i] = array();
		foreach($d as $key => $value) {
		    array_push($name_value_list[$i], array('name' => $key, 'value' => $value));
		}
	    $i++;
	    }

	    $result = $this->soap->call('set_entries', array(
					'session' => $this->session,
					'module_name' => 'Contacts',
					'name_value_list' => $name_value_list
					));

	    return $result;
	}

        function getContact($emails){

          //      $emails = implode("','", $emails);
                $result = $this->soap->call('get_entry_list', array(
			'session' => $this->session,
			'module_name' => 'Contacts',
			'query' => "contacts.id in (
SELECT eabr.bean_id
FROM email_addr_bean_rel eabr JOIN email_addresses ea
ON (ea.id = eabr.email_address_id)
 WHERE eabr.deleted=0 AND ea.email_address IN ('".$emails."'))",
'order_by' => '',
'offset' => 0,
'select_fields' => array('result_count'),
'max_results' => 10,
'deleted' => -1
));

                return $result;
        }

	function updateContact($data){

	    $name_value_list = array(array('name' => 'id', 'value' => '758a7b32-6f70-7803-e130-4d3d5881fedf'));
	    foreach($data as $key => $value) {
		array_push($name_value_list, array('name' => $key, 'value' => $value));
	    }

	    $result = $this->soap->call('set_entry', array(
		    'session' => $this->session,
		    'module_name' => 'Contacts',
		    'name_value_list' => $name_value_list
	   ));

	    return $result;

	}

	function findUserByEmail($emails) {

	    $found = array();
	    if (!is_array($emails)) {
		$temp = $emails;
		$emails = array();
		$emails[] = $temp;
	    }

	    $emailsStr = implode("','", $emails);

	    $info = $this->soap->call('get_entry_list',
					array(
					    'session' => $this->session,
					    'module_name' => 'Contacts',
					    'query' => "contacts.id in (
							    SELECT eabr.bean_id
							    FROM email_addr_bean_rel eabr
							    JOIN email_addresses ea
							    ON (ea.id = eabr.email_address_id)
							    WHERE eabr.deleted = 0
							    AND ea.email_address IN ('$emailsStr')
							)",
					    'order_by' => '',
					    'offset' => 0,
					    'select_fields' => array(),
					    'max_results' => 25,
					    'deleted' => -1
					)
				    );
	    if ($info){
		foreach ($info['entry_list'] as $entry) {
		    foreach ($entry as $ent) {
			if (is_array($ent)) {
			    foreach ($ent as $e) {
				if (isset($e['name']) && $e['name'] == 'email1') {
				    if (in_array($e['value'], $emails)){
					$found[$e['value']] = $entry['id'];
				    }
				}
			    }
			}
		    }
		}
	    }
	    return $found;
	}


	function getModuleFields($module){
	    $fields = $this->soap->call('get_module_fields',
					array(
					    'session' => $this->session,
					    'module_name' => $module
					)
				    );
	    return $fields["module_fields"];
	}
}
