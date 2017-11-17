<?php
/**
 *  @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

class Push_Highrise{

	var $highrise_url;
	var $api_token;
	var $task_assignee_user_id = ''; // user id of the highrise user who gets the task assigned
	var $category = ''; // the category where deals will be assigned to

	var $errorMsg = "";

	function Push_Highrise($highrise_url, $api_token){
	    $this->highrise_url = $highrise_url;
	    $this->api_token = $api_token;
	}

	function loginCheck(){
	    $curl = curl_init($this->highrise_url.'/account.xml');
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');
	    $xml = curl_exec($curl);
	    curl_close($curl);
	    $xml = $this->parseXML2Array($xml);
	    return (isset($xml['account']['id'])) ? true : false;
	}

	function pushDeal($request){
		$curl = curl_init($this->highrise_url.'/deals.xml');
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');

		//Setup XML to POST
		curl_setopt($curl,CURLOPT_HTTPHEADER,Array("Content-Type: application/xml"));
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,'<deal>
			<name>'.htmlspecialchars($request['sSubject']).'</name>
			<price-type>fixed</price-type>
			<category-id type="integer">'.$category.'</category-id>
			<responsible-party-id type="integer">'.$this->task_assignee_user_id.'</responsible-party-id>
			<background>'.htmlspecialchars($request['sNotes']).'</background>
			<visible-to>Everyone</visible-to>
			<party-id type="integer">'.$this->_person_in_highrise($request).'</party-id>
		</deal>');

		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		$xml = curl_exec($curl);
		curl_close($curl);
		return '';
	}

	function pushNote($request){
		$curl = curl_init($this->highrise_url.'/notes.xml');
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');

		$bodyPrefix = "Contact request submitted from website";

		//Setup XML to POST
		curl_setopt($curl,CURLOPT_HTTPHEADER,Array("Content-Type: application/xml"));
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,'<note>
			<subject-id type="integer">'.$this->_person_in_highrise($request).'</subject-id>
			<subject-type>Party</subject-type>
			<body>'.$bodyPrefix.' '.htmlspecialchars($request['sSubject']).' - '.htmlspecialchars($request['sNotes']).'</body>
		</note>');

		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		$xml = curl_exec($curl);
		curl_close($curl);
		return '';
	}

	function pushTask($request){
		$curl = curl_init($this->highrise_url.'/tasks.xml');
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');

		$bodyPrefix = "Task subject"; // set the subject

		//Setup XML to POST
		curl_setopt($curl,CURLOPT_HTTPHEADER,Array("Content-Type: application/xml"));
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,'<task>
			<subject-id type="integer">'.$this->_person_in_highrise($request).'</subject-id>
			<subject-type>Party</subject-type>
			<body>'.$bodyPrefix.' '.htmlspecialchars($request['sSubject']).' - '.htmlspecialchars($request['sNotes']).'</body>
			<frame>today</frame>
			<public type="boolean">true</public>
			<owner-id type="integer">'.$this->task_assignee_user_id.'</owner-id>
		</task>');

		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		$xml = curl_exec($curl);
		curl_close($curl);
		return '';
	}

	function pushContact($request){

	    $result = array();

	    //Check that person doesn't already exist
	//    $userdata = $this->person_in_highrise($request);
	    if ((int)$request['id'] < 0){
		$curl = curl_init($this->highrise_url.'/people.xml');
		$result['new'] = 1;
		$result['updated'] = 0;
	    } else {
		$curl = curl_init($this->highrise_url.'/people/'.(int)$request['id'].'.xml');
		$result['new'] = 0;
		$result['updated'] = 1;
	    }

	    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');
	    curl_setopt($curl,CURLOPT_HTTPHEADER,Array("Content-Type: application/xml"));

	    if ((int)$request['id'] < 0){			// add new contact
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $request['xml']);
	    } else {						// update existing
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
		$putData = tmpfile();
		fwrite($putData, $request['xml']);
		fseek($putData, 0);
		curl_setopt($curl, CURLOPT_PUT, 1);
		curl_setopt($curl, CURLOPT_INFILE, $putData);
		curl_setopt($curl, CURLOPT_INFILESIZE, strlen($request['xml']));
	    }

	    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

	    $result['xml'] = curl_exec($curl);
	    $result['headers'] = curl_getinfo($curl);
	    $result['status']  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	    curl_close($curl);

	    return $result;
	}

	//Search for a person in Highrise
	function person_in_highrise($person){
	    $curl = curl_init($this->highrise_url.'/people/search.xml?term='.urlencode($person['first-name'].' '.$person['last-name']));
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($curl,CURLOPT_USERPWD,$this->api_token.':x');
	    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

	    $xml = curl_exec($curl);
	    curl_close($curl);

	    //Parse XML
	    $people = simplexml_load_string($xml);
	    $result = new stdClass();
	    $result->id = '-1';
	    foreach ($people->person as $person) {
		if ($person != null) {
		    $result = $person;
		}
	    }
	    return $result;
	}


	function parseXML2Array($xml){
	    $xml2array = new xml2array;
	    $data = $xml2array->parse($xml);

	    return $data;
	}
	function parseArray2XML($root, $xml){
	    $SimpleXMLArrayHelper = new SimpleXMLArrayHelper($root);
	    $data = $SimpleXMLArrayHelper->setArray($xml)->asXml();

	    return $data;
	}

}

?>
