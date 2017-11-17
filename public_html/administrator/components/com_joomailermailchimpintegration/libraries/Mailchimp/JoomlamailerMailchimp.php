<?php
require_once(__DIR__ . '/MailchimpException.php');

// no direct access
defined('_JEXEC') or die('Restricted Access');

class JoomlamailerMailchimp {

    protected $ch;

    private $version = '3.0';
    private $timeout = 30;
    private $apiKey;
    private $apiUrl;
    private $options;

    public $debug = true;

    public function __construct($apiKey, $options = array()) {
        $this->apiKey = $apiKey;
        $this->options = $options;

        if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array('joomlamailer.loc', 'joomla.loc'))) {
            $this->debug = true;
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }

    private function getCurlInstance() {
        if (defined('OPENSSL_VERSION_NUMBER')) {
            $this->secure = true;
            $protocol = 'https';
        } else {
            $this->secure = false;
            $protocol = 'http';
        }

        $dc = $this->getDc();
        $this->apiUrl = "{$protocol}://{$dc}.api.mailchimp.com/{$this->version}/";

        if (isset($this->options['timeout']) && (int)$this->options['timeout'] > 0) {
            $this->setTimeout((int)$this->options['timeout']);
        }
        if (isset($this->options['debug']) && $this->options['debug']) {
            $this->debug = true;
        }

        $this->ch = curl_init();

        if (isset($this->options['CURLOPT_FOLLOWLOCATION']) && $this->options['CURLOPT_FOLLOWLOCATION'] === true) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Joomlamailer/' . $this->version);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($this->ch, CURLOPT_VERBOSE, $this->debug);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
    }

    private function closeConnection() {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    public function setTimeout($seconds) {
        if (is_int($seconds)) {
            $this->timeout = $seconds;
        }
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function getDc() {
        $dc = 'us1';
        if (strstr($this->apiKey, '-')) {
            list(, $dc) = explode('-', $this->apiKey);
        }

        return $dc;
    }

    private function callServer($endpoint, $params = array(), $method = 'GET') {
        //if (@$_GET['format'] != 'raw') {echo $endpoint . ' * ';}

        // initiate curl instance
        $this->getCurlInstance();

        switch ($method) {
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                if (count($params)) {
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
                }
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                if (count($params)) {
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
                }
                break;
            case 'GET':
            default:
                $params = $this->httpBuildQuery($params);
                $endpoint .= ($params ? '?' . $params : '');
                break;
        }

        // set authorization header
        $header = array('Authorization: apikey ' . $this->apiKey);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);

        if (!in_array($method, array('GET', 'POST'))) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->apiUrl . $endpoint);

        $start = microtime(true);
        $this->log('[' . date('Y-m-d H:i:s') . '] ' . $method . ' ' . $this->apiUrl . $endpoint . ($params ? ': ' . json_encode($params) : ''));
        if ($this->debug) {
            $curlBuffer = fopen('php://memory', 'w+');
            @curl_setopt($this->ch, CURLOPT_STDERR, $curlBuffer);
        }

        $response = curl_exec($this->ch);

        $info = curl_getinfo($this->ch);
        $time = microtime(true) - $start;
        if ($this->debug) {
            rewind($curlBuffer);
            $this->log(stream_get_contents($curlBuffer));
            fclose($curlBuffer);
        }
        $this->log("\n.... Completed in " . number_format($time * 1000, 2) . 'ms');
        $this->log("\n.... Response: {$response}");

        if (curl_error($this->ch)) {
            $errorMsg = "API call to {$endpoint} failed: " . curl_error($this->ch);
            $this->log('[' . date('Y-m-d H:i:s') . '] ' . $errorMsg);
            throw new MailchimpException($errorMsg);
        }

        $this->closeConnection();

        $result = json_decode($response, true);

        if (floor($info['http_code'] / 100) >= 4 || (isset($result['status']) && floor($result['status'] / 100) >= 4)) {
            throw new MailchimpException($result['title'], $result['status'], $result['detail']);
        }

        // remove _link elements from response
        $this->recursiveUnset($result, '_links');

        return $result;
    }

    private function httpBuildQuery($params, $key = null) {
        $ret = array();

        foreach((array)$params as $name => $val) {
            $name = urlencode($name);
            if ($key !== null) {
                $name = $key . '[' . $name . ']';
            }

            if (is_array($val) || is_object($val)) {
                $ret[] = $this->httpBuildQuery($val, $name);
            } elseif ($val !== null) {
                $ret[] = $name . '=' . urlencode($val);
            }
        }

        return implode('&', $ret);
    }

    private function log($msg) {
        if ($this->debug && trim($msg)) {
            //error_log($msg);
            file_put_contents(__DIR__ . '/mc_api.log', trim($msg) . "\n", FILE_APPEND);
        }
    }

    /**
     * API ROOT
     */
    public function getAccountDetails() {
        return $this->callServer('/');
    }

    /**
     * CAMPAIGNS
     */
    public function campaigns($params = array(), $method = 'GET') {
        $endpoint = 'campaigns';
        if (isset($params['campaign_id'])) {
            $endpoint .= "/{$params['campaign_id']}";
        }

        return $this->callServer($endpoint, $params, $method);
    }

    public function campaignsContent($campaignId, $params = array(), $method = 'GET') {
        return $this->callServer("campaigns/{$campaignId}/content", $params, $method);
    }

    public function campaignsActions($campaignId, $action, $params = array()) {
        $endpoint = 'campaigns/' . $campaignId . '/actions/' . $action;

        return $this->callServer($endpoint, $params, 'POST');
    }

    /**
     * Campaign-Folders
     *
     * @param string $method
     * @param string $folderId
     * @param string $name
     * @return json API response
     * @throws MailchimpException
     */
    public function campaignFolders($method = 'GET', $folderId = null, $name = '') {
        $params = array();

        $endpoint = 'campaign-folders';
        if ($folderId) {
            $endpoint .= '/' . $folderId;
        }

        if (in_array($method, array('POST', 'PATCH'))) {
            $name = trim($name);
            if (empty($name)) {
                throw new MailchimpException('Folder name can not be empty!');
            }

            $params['name'] = $name;
        } else if ($method == 'DELETE' && !$folderId) {
            throw new MailchimpException('Folder Id is required!');
        }

        return $this->callServer($endpoint, $params, $method);
    }

    /**
     * LISTS
     */
    public function lists($params = array()) {
        return $this->callServer('lists', $params, 'GET');
    }

    public function listMergeFields($listId, $count = 200) {
        return $this->callServer('lists/' . $listId . '/merge-fields', array('count' => $count));
    }

    public function listMergeField($listId, $params, $method = 'GET') {
        $endpoint = 'lists/' . $listId . '/merge-fields/';
        if (isset($params['merge_id'])) {
            $endpoint .= $params['merge_id'];
        }

        return $this->callServer($endpoint, $params, $method);
    }

    public function listInterestCategories($listId, $categoryId = '', $params = array(), $method = 'GET') {
        $endpoint = 'lists/' . $listId . '/interest-categories/';
        if ($categoryId) {
            $endpoint .= $categoryId;
            if ($method != 'DELETE') {
                $endpoint .= '/interests';
            }
        }

        return $this->callServer($endpoint, $params, $method);
    }

    public function listSegments($listId, $segmentId = false, $method = 'GET', $params = array()) {
        $endpoint = 'lists/' . $listId . '/segments/';
        if ($segmentId) {
            $endpoint .= $segmentId;
        }

        return $this->callServer($endpoint, $params, $method);
    }

    public function listMembers($listId, $status = 'subscribed', $count = 100, $offset = 0, $since = '') {
        $params = array(
            'status'              => $status,
            'count'               => $count,
            'offset'              => $offset,
            'since_timestamp_opt' => $since
        );

        return $this->callServer('lists/' . $listId . '/members', $params);
    }

    public function listMember($listId, $email) {
        $endpoint = 'lists/' . $listId . '/members/' . md5(\Joomla\String\StringHelper::strtolower($email));

        return $this->callServer($endpoint);
    }

    public function listMemberSubscribe($listId, $params) {
        if (!empty($params['email_address_old'])) {
            $email = $params['email_address_old'];
            unset($params['email_address_old']);
            $method = 'PATCH';
        } else {
            $method = 'PUT';
            $email = $params['email_address'];
        }
        $endpoint = 'lists/' . $listId . '/members/' . md5(\Joomla\String\StringHelper::strtolower($email));

        return $this->callServer($endpoint, $params, $method);
    }

    public function listMemberUnsubscribe($listId, $email) {
        $endpoint = 'lists/' . $listId . '/members/' . md5(\Joomla\String\StringHelper::strtolower($email));
        $params = array(
            'status' => 'unsubscribed'
        );

        return $this->callServer($endpoint, $params, 'PATCH');
    }

    public function listMemberDelete($listId, $email) {
        $endpoint = 'lists/' . $listId . '/members/' . md5(\Joomla\String\StringHelper::strtolower($email));

        return $this->callServer($endpoint, array(), 'DELETE');
    }


    /**
     * REPORTS
     */
    public function reports($campaignId) {
        return $this->callServer('reports/' . $campaignId);
    }

    public function reportsEmailActivity($campaignId, $email) {
        $endpoint = 'reports/' . $campaignId . '/email-activity/' . md5(\Joomla\String\StringHelper::strtolower($email));

        return $this->callServer($endpoint);
    }

    public function reportsAbuseReports($campaignId, $count = 25, $offset = 0) {
        $endpoint = 'reports/' . $campaignId . '/abuse-reports';
        $params = array(
            'count'  => $count,
            'offset' => $offset
        );

        return $this->callServer($endpoint, $params);
    }

    public function reportsSentTo($campaignId, $count = 25, $offset = 0) {
        $endpoint = 'reports/' . $campaignId . '/sent-to';
        $params = array(
            'count'  => $count,
            'offset' => $offset
        );

        return $this->callServer($endpoint, $params);
    }

    public function reportsClickDetails($campaignId, $linkId = null, $count = 25, $offset = 0, $getMembers = false) {
        $endpoint = 'reports/' . $campaignId . '/click-details';
        if ($linkId) {
            $endpoint .= '/' . $linkId;
            if ($getMembers) {
                $endpoint .= '/members';
                $params = array(
                    'count'  => $count,
                    'offset' => $offset
                );
            } else {
                $params = array();
            }
        } else {
            $params = array(
                'count'  => $count,
                'offset' => $offset
            );
        }

        return $this->callServer($endpoint, $params);
    }

    public function reportsUnsubscribes($campaignId, $count = 25, $offset = 0) {
        $endpoint = 'reports/' . $campaignId . '/unsubscribed';
        $params = array(
            'count'  => $count,
            'offset' => $offset
        );

        return $this->callServer($endpoint, $params);
    }

    public function reportsAdvice($campaignId) {
        $endpoint = 'reports/' . $campaignId . '/advice';

        return $this->callServer($endpoint);
    }

    public function reportsEepUrl($campaignId) {
        $endpoint = 'reports/' . $campaignId . '/eepurl';
        $params = array(
            'count'  => 1000,
            'offset' => 0
        );

        return $this->callServer($endpoint, $params);
    }

    function reportsLocations($campaignId) {
        $endpoint = 'reports/' . $campaignId . '/locations';
        $params = array(
            'count'  => 1000,
            'offset' => 0
        );
        $res = $this->callServer($endpoint, $params);

        // group locations by country rather than region
        if ($res['total_items'] > 0) {
            $data = array();
            foreach ($res['locations'] as $location) {
                $location['country_code'] = ($location['country_code'] == 'UK') ? 'GB' : $location['country_code'];
                if (!isset($data[$location['country_code']])) {
                    $data[$location['country_code']] = 0;
                }
                $data[$location['country_code']] += $location['opens'];
            }

            asort($data);

            $res['locations'] = array_reverse($data);
        }

        return $res;
    }

    /**
     * BATCHES
     */
    public function batches($method = 'GET', $batchId = false, $operations = array()) {
        $endpoint = 'batches';
        if ($batchId) {
            $endpoint .= '/' . $batchId;
        }

        return $this->callServer($endpoint, $operations, $method);
    }


    /**
     * Remove all array elements with a given key recursively
     * @param $array
     * @param $unwanted_key
     */
    private function recursiveUnset(&$array, $unwanted_key) {
        if (!is_array($array)) {
            return;
        }
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveUnset($value, $unwanted_key);
            }
        }
    }
}
