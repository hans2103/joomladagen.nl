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

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/gapi.class.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/fileuploader.php');

class joomailermailchimpintegrationControllerCampaigns extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

	public function opened() {
		$this->input->set('view', 'campaigns');
		$this->input->set('layout', 'opened');
		$this->input->set('hidemainmenu', 1);
		parent::display();
	}

	public function clicked() {
		$this->input->set('view', 'campaigns');
		$this->input->set('layout', 'clicked');
		$this->input->set('hidemainmenu', 1);
		parent::display();
	}

	public function a360_request_handler() {
		if (!$this->input->getString('a360_action')) {
            return;
        }

        header('Content-type: application/json');

        switch ($this->input->getString('a360_action')) {
            case 'get_mc_data':
                switch ($this->input->getString('data_type')) {
                    case 'campaigns':
                        $results = $this->getModel()->getMcObject()->campaigns(array(
                        'sendtime_start' => $this->input->getString('start_date'),
                        'end_start' => $this->input->getString('end_date')
                        ));
                        if ($results) {
                            die(json_encode(array(
                                'success' => true,
                                'data'    => $results,
                                'cached'  => false
                            )));
                        } else if (empty($this->getModel()->getMcObject()->errorCode)) {
                            die(json_encode(array(
                                'success' => true,
                                'data'    => $results,
                                'cached'  => false
                            )));
                        } else {
                            die(json_encode(array(
                                'success' => false,
                                'error'   => $this->getModel()->getMcObject()->errorMessage
                            )));
                        }
                        break;
                    case 'list_growth':
                        $results = $this->getModel()->getMcObject()->listGrowthHistory($this->input->getString('list_id'));
                        if ($results) {
                            die(json_encode(array(
                                'success' => true,
                                'data'    => $results,
                                'cached'  => false
                            )));
                        } else {
                            die(json_encode(array(
                                'success' => false,
                                'error'   => $this->getModel()->getMcObject()->errorMessage
                            )));
                        }
                        break;
                }
                break;
            case 'get_ga_data':
                $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
                $report_id = $params->get('params.gprofileid');
                $parameters = array(
                    'start-date' => $this->input->getString('start_date'),
                    'end-date'   => $this->input->getString('end_date'),
                    'sort'       => 'date',
                    'ids'        => 'ga:' . $report_id
                );

                // split up top referrals by filtering on each medium in turn
                if ($this->input->getString('data_type') == 'top_referrals') {
                    $parameters['dimensions'] = array('medium', 'source');
                    $parameters['metrics'] = array('visits', 'timeOnSite', 'pageviews');
                    $parameters['sort'] = '-visits';

                    $all_results = array();

                    $filters = array('referral', 'organic', 'email', 'cpc', '*');
                    foreach ($filters as $f) {
                        $parameters['filter'] = 'medium==' . $f;
                        $all_results[$f] = $this->requestReportData($report_id, $parameters['dimensions'],
                            $parameters['metrics'], $parameters['sort'], $parameters['filters'],
                            $parameters['start-date'], $parameters['end-date'], 1, 30);
                    }

                    die(json_encode(array(
                        'success' => true,
                        'data'    => $all_results,
                        'cached'  => false
                    )));
                    break;
                } else {
                    switch ($this->input->getString('data_type')) {
                        case 'visits':
                            $parameters['dimensions'] = array('date','medium');
                            $parameters['metrics'] = array('visits','bounces','entrances','pageviews','newVisits','timeOnSite');
                            //$parameters['filters'] = 'ga:medium==referral,ga:medium==organic,ga:medium==email,ga:medium==cpc';
                            //$parameters['sort'] = '-ga:visits';
                            break;
                        case 'geo':
                            $parameters['dimensions'] = array('country');
                            $parameters['metrics'] = array('visits');
                            $parameters['sort'] = array('-visits');
                            break;
                        case 'top_referrals':
                            $parameters['dimensions'] = array('medium','source');
                            $parameters['metrics'] = array('visits','timeOnSite','pageviews');
                            $parameters['sort'] = array('-visits');
                            $parameters['filters'] = 'medium==referral || medium==organic || medium==email || medium==cpc';
                            break;
                        case 'referral_media':
                            $parameters['dimensions'] = array('medium');
                            $parameters['metrics'] = array('visits');
                            $parameters['sort'] = array('-visits');
                            break;
                        case 'top_content':
                            $parameters['dimensions'] = array('pagePath');
                            $parameters['metrics'] = array('pageviews','uniquePageviews','timeOnPage','exits');
                            $parameters['sort'] = array('-pageviews');
                            break;
                        case 'keywords':
                            $parameters['dimensions'] = array('keyword');
                            $parameters['metrics'] = array('pageviews','uniquePageviews','timeOnPage','exits');
                            $parameters['sort'] = array('-pageviews');
                            $parameters['filters'] = 'source==' . $this->input->getString('source_name');
                            break;
                        case 'referral_paths':
                            $parameters['dimensions'] = array('source,referralPath');
                            $parameters['metrics'] = array('pageviews','uniquePageviews','timeOnPage','exits');
                            $parameters['sort'] = array('-pageviews');
                            $parameters['filters'] = 'source==' . $this->input->getString('source_name');
                            break;
                        case 'email_referrals':
                            $parameters['dimensions'] = array('campaign');
                            $parameters['metrics'] = array('pageviews','uniquePageviews','timeOnPage','exits');
                            $parameters['sort'] = array('-pageviews');
                            $parameters['filters'] = 'medium==email';
                            break;
                        default:
                            break;
                    }
                }

                $result = $this->requestReportData($report_id, $parameters['dimensions'], $parameters['metrics'],
                    $parameters['sort'], $parameters['filters'], $parameters['start-date'], $parameters['end-date'],
                    1, 3000);

                die(json_encode(array(
                    'success' => true,
                    'data'    => $result,
                    'cached'  => false
                )));
                break;
        }
	}

	public function a360_fetch_posts() {
		$start = $this->input->getString('start_date');
		$end = $this->input->getString('end_date');
		$db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select($db->qn(array('id', 'publish_up', 'modified')), array('ID', 'post_date_gmt', 'post_modified_gmt'))
            ->from('#__content')
            ->where($db->qn('publish_up') . ' BETWEEN ' . $db->q($start) . ' AND ' . $db->q($end));
		$db->setQuery($query);
		$result = $db->loadAssocList();

		//get offset for local time
		$tzoffset = JFactory::getConfig()->getValue('config.offset');

		for ($i = 0; $i < count($result); $i++) {
			$result[$i]['post_author'] = '';
			$result[$i]['post_date'] = date('Y-m-d', strtotime($result[$i]['post_date_gmt']) + $tzoffset * 60 * 60);
			$result[$i]['post_content'] = '';
			$result[$i]['post_title'] = '';
			$result[$i]['post_excerpt'] = '';
			$result[$i]['post_status'] = '';
			$result[$i]['comment_status'] = '';
			$result[$i]['ping_us'] = '';
			$result[$i]['post_password'] = '';
			$result[$i]['post_name'] = '';
			$result[$i]['to_ping'] = '';
			$result[$i]['post_modified'] = date('Y-m-d',strtotime($result[$i]['post_modified_gmt']) + $tzoffset * 60 * 60);
			$result[$i]['post_content_filtered'] = '';
			$result[$i]['post_parent'] = '';
			$result[$i]['guid'] = '';
			$result[$i]['menu_order'] = '';
			$result[$i]['post_type'] = '';
			$result[$i]['post_mime_type'] = '';
			$result[$i]['comment_count'] = '';
			$result[$i]['cached'] = '';
		}

        header('Content-type: application/json');
        echo json_encode(array(
            'success' => true,
            'data'    => $result,
            'cached'  => false
        ));
	}

	/**
	 * Perform http request
	 *
	 *
	 * @param Array $get_variables
	 * @param Array $post_variables
	 * @param Array $headers
	 */
	public function httpRequest($url, $get_variables = null, $post_variables = null, $headers = null)	{
		$interface = gapi::http_interface;

		if (gapi::http_interface =='auto') {
			if (function_exists('curl_exec')) {
				$interface = 'curl';
			} else {
				$interface = 'fopen';
			}
		}

		if ($interface == 'curl') {
			return $this->curlRequest($url, $get_variables, $post_variables, $headers);
		} else if ($interface == 'fopen') {
			return $this->fopenRequest($url, $get_variables, $post_variables, $headers);
		} else {
			throw new Exception('Invalid http interface defined. No such interface "' . gapi::http_interface . '"');
		}
	}

	/**
	 * HTTP request using PHP CURL functions
	 * Requires curl library installed and configured for PHP
	 *
	 * @param Array $get_variables
	 * @param Array $post_variables
	 * @param Array $headers
	 */
	private function curlRequest($url, $get_variables = null, $post_variables = null, $headers = null)	{
		$ch = curl_init();

		if (is_array($get_variables)) {
			$get_variables = '?' . str_replace('&amp;','&',urldecode(http_build_query($get_variables)));
		} else {
			$get_variables = null;
		}

		curl_setopt($ch, CURLOPT_URL, $url . $get_variables);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //CURL doesn't like google's cert

		if (is_array($post_variables)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_variables);
		}

		if (is_array($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		}

		$response = curl_exec($ch);
		$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

		curl_close($ch);

		return array('body' => $response, 'code' => $code);
	}

	/**
	 * Request report data from Google Analytics
	 *
	 * $report_id is the Google report ID for the selected account
	 *
	 * $parameters should be in key => value format
	 *
	 * @param String $report_id
	 * @param Array $dimensions Google Analytics dimensions e.g. array('browser')
	 * @param Array $metrics Google Analytics metrics e.g. array('pageviews')
	 * @param Array $sort_metric OPTIONAL: Dimension or dimensions to sort by e.g.('-visits')
	 * @param String $filter OPTIONAL: Filter logic for filtering results
	 * @param String $start_date OPTIONAL: Start of reporting period
	 * @param String $end_date OPTIONAL: End of reporting period
	 * @param Int $start_index OPTIONAL: Start index of results
	 * @param Int $max_results OPTIONAL: Max results returned
	 */
	private function requestReportData($report_id, $dimensions, $metrics, $sort_metric=null, $filter=null, $start_date=null, $end_date=null, $start_index=1, $max_results=3000) {
		$parameters = array('ids' => 'ga:' . $report_id);

		if (is_array($dimensions)) {
			$dimensions_string = '';
			foreach($dimensions as $dimesion) {
				$dimensions_string .= ',ga:' . $dimesion;
			}
			$parameters['dimensions'] = substr($dimensions_string,1);
		} else {
			$parameters['dimensions'] = 'ga:'.$dimensions;
		}

		if (is_array($metrics)) {
			$metrics_string = '';
			foreach($metrics as $metric) {
				$metrics_string .= ',ga:' . $metric;
			}
			$parameters['metrics'] = substr($metrics_string,1);
		} else {
			$parameters['metrics'] = 'ga:' . $metrics;
		}

		if ($sort_metric==null&&isset($parameters['metrics'])) {
			$parameters['sort'] = $parameters['metrics'];
		} else if (is_array($sort_metric)) {
			$sort_metric_string = '';

			foreach($sort_metric as $sort_metric_value) {
				//Reverse sort - Thanks Nick Sullivan
				if (substr($sort_metric_value, 0, 1) == "-") {
					$sort_metric_string .= ',-ga:' . substr($sort_metric_value, 1); // Descending
				} else {
					$sort_metric_string .= ',ga:' . $sort_metric_value; // Ascending
				}
			}

			$parameters['sort'] = substr($sort_metric_string, 1);
		} else {
			if (substr($sort_metric, 0, 1) == "-") {
				$parameters['sort'] = '-ga:' . substr($sort_metric, 1);
			} else {
				$parameters['sort'] = 'ga:' . $sort_metric;
			}
		}

		if ($filter != null) {
			$filter = $this->processFilter($filter);
			if ($filter !== false) {
				$parameters['filters'] = $filter;
			}
		}

		if ($start_date == null) {
			$start_date=date('Y-m-d', strtotime('1 month ago'));
		}

		$parameters['start-date'] = $start_date;

		if ($end_date == null) {
			$end_date = date('Y-m-d');
		}

		$parameters['end-date'] = $end_date;
		$parameters['start-index'] = $start_index;
		$parameters['max-results'] = $max_results;

		$parameters['prettyprint'] = 'false';
		$token = $this->session->get('gtoken');
		$header = array('Authorization: GoogleLogin auth=' . $token);
		$report_data_url = 'https://www.google.com/analytics/feeds/data';


		$response = $this->httpRequest($report_data_url, $parameters, null, $header);

		//HTTP 2xx
		if (substr($response['code'],0,1) == '2') {
			return $this->reportObjectMapper($response['body']);
		} else {
			throw new Exception('GAPI: Failed to request report data. Error: "' . strip_tags($response['body']) . '"');
		}
	}

	/**
	 * Report Object Mapper to convert the XML to array of useful PHP objects
	 *
	 * @param String $xml_string
	 * @return Array of gapiReportEntry objects
	 */
	private function reportObjectMapper($xml_string) {
		$xml = simplexml_load_string($xml_string);

		$this->results = null;
		$results = array();

		$report_root_parameters = array();
		$report_aggregate_metrics = array();

		//Load root parameters
		$report_root_parameters['updated'] = strval($xml->updated);
		$report_root_parameters['generator'] = strval($xml->generator);
		$report_root_parameters['generatorVersion'] = strval($xml->generator->attributes());

		$open_search_results = $xml->children('http://a9.com/-/spec/opensearchrss/1.0/');

		foreach($open_search_results as $key => $open_search_result) {
			$report_root_parameters[$key] = intval($open_search_result);
		}

		$google_results = $xml->children('http://schemas.google.com/analytics/2009');

		foreach($google_results->dataSource->property as $property_attributes) {
			$report_root_parameters[str_replace('ga:','',$property_attributes->attributes()->name)] = strval($property_attributes->attributes()->value);
		}

		$report_root_parameters['startDate'] = strval($google_results->startDate);
		$report_root_parameters['endDate'] = strval($google_results->endDate);

		//Load result aggregate metrics

		foreach ($google_results->aggregates->metric as $aggregate_metric) {
			$metric_value = strval($aggregate_metric->attributes()->value);

			//Check for float, or value with scientific notation
			if (preg_match('/^(\d+\.\d+)|(\d+E\d+)|(\d+.\d+E\d+)$/',$metric_value)) {
				$report_aggregate_metrics[str_replace('ga:','',$aggregate_metric->attributes()->name)] = floatval($metric_value);
			} else {
				$report_aggregate_metrics[str_replace('ga:','',$aggregate_metric->attributes()->name)] = intval($metric_value);
			}
		}

		//Load result entries
		foreach ($xml->entry as $entry) {
			$metrics = array();
			foreach($entry->children('http://schemas.google.com/analytics/2009')->metric as $metric) {
				$metric_value = strval($metric->attributes()->value);

				//Check for float, or value with scientific notation
				if (preg_match('/^(\d+\.\d+)|(\d+E\d+)|(\d+.\d+E\d+)$/',$metric_value)) {
					$metrics[str_replace('ga:','',$metric->attributes()->name)] = floatval($metric_value);
				} else {
					$metrics[str_replace('ga:','',$metric->attributes()->name)] = intval($metric_value);
				}
			}

			$dimensions = array();
			foreach($entry->children('http://schemas.google.com/analytics/2009')->dimension as $dimension) {
				$dimensions[str_replace('ga:','',$dimension->attributes()->name)] = strval($dimension->attributes()->value);
			}

			//$results[] = new gapiReportEntry($metrics,$dimensions);
			$results[] = array('metrics' => $metrics, 'dimensions' => $dimensions);
		}

		$this->report_root_parameters = $report_root_parameters;
		$this->report_aggregate_metrics = $report_aggregate_metrics;
		$this->results = $results;

		return $results;
	}

    public function goToCampaigns() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=campaigns');
    }

	public function analytics() {
		$this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=analytics360');
	}

	public function clearReportsCache() {
        $this->getModel('main')->cache('joomlamailerReports')->clean('joomlamailerReports');
	}

    public function create() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=create');
    }
}
