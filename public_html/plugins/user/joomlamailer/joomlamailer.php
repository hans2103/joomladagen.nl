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

defined('_JEXEC') or die('Restricted Access');

class PlgUserJoomlamailer extends JPlugin {

    private static $MC = null;
    private static $oldEmail = null;
    protected $app;
    protected $db;
    protected $api;
    protected $debug;
    protected $listId;
	protected $autoloadLanguage = true;

	public function __construct(&$subject, $config) {
        // Determine if the joomlamailer component is installed and enabled
        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
            || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
            return;
        }

		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/fields');
	}

	public function onContentPrepareData($context, $data) {
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile'))) {
			return true;
		}

		if (is_object($data)) {
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->joomlamailer) && $userId > 0) {
                $this->db = JFactory::getDBO();
                $user = JFactory::getUser($userId);

                // check if user is subscribed
                $query = $this->db->getQuery(true)
                    ->select(1)
                    ->from($this->db->qn('#__joomailermailchimpintegration'))
                    ->where($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
                try {
                    $isSubscribed = ($this->db->setQuery($query)->loadResult() ? 1 : 0);
                } catch (Exception $e) {
                    $isSubscribed = false;
                }

                $data->joomlamailer['subscribe'] = $isSubscribed;
                if (!JHtml::isRegistered('users.subscribe')) {
                    JHtml::register('users.subscribe', array(__CLASS__, 'subscribe'));
                }

                if (!$isSubscribed) {
                    return;
                }

                // load user data from MailChimp in order to populate user profile fields
                try {
                    $userData = $this->getApi()->listMember($this->params->get('listid'), $user->email);
                    // merge fields
                    if (!empty($userData['merge_fields'])) {
                        foreach ($userData['merge_fields'] as $key => $value) {
                            $data->joomlamailer_merges[$key] = $value;
                        }
                    }

                    // interest categories
                    if (!empty($userData['interests'])) {
                        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
                        $groupsModel = new joomailermailchimpintegrationModelGroups();
                        $interestCategories = $groupsModel->getListInterestCategories($this->params->get('listid'));

                        $data->joomlamailer_interests = array();
                        if (!empty($interestCategories['total_items'])) {
                            foreach ($interestCategories['categories'] as $category) {

                                $interests = $groupsModel->getListInterestCategories($this->params->get('listid'), $category['id']);
                                if (empty($interests['total_items'])) {
                                    continue;
                                }

                                foreach ($interests['interests'] as $interest) {
                                    if (isset($userData['interests'][$interest['id']]) && $userData['interests'][$interest['id']]) {
                                        $data->joomlamailer_interests[$category['id']][] = $interest['id'];
                                    }
                                }
                            }
                        }
                    }
                } catch (MailchimpException $e) {}

                if (!JHtml::isRegistered('users.birthday')) {
                    JHtml::register('users.birthday', array(__CLASS__, 'birthday'));
                }
                if (!JHtml::isRegistered('users.address')) {
                    JHtml::register('users.address', array(__CLASS__, 'address'));
                }
			}
		}

		return true;
	}

    public static function subscribe($value) {
        return JText::_(($value ? 'JYES' : 'JNO'));
    }

    public static function birthday($value) {
        jimport('joomla.plugin.helper');
        $plugin = JPluginHelper::getPlugin('user', 'joomlamailer');
        $pluginParams = new JRegistry($plugin->params);
        $dateFormat = $pluginParams->get('dateFormat');
        if ($dateFormat == 'DD/MM') {
            $value = explode('/', $value);
            $value = array_reverse($value);
            $value = implode('/', $value);
        }

        return $value;
    }

    public static function address($value) {
        return (is_array($value) ? implode(', ', $value) : '-');
    }

	/**
	 * adds additional fields to the user editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data) {
		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

        if (!$this->params->get('listid')) {
            if (JFactory::getConfig()->get('debug')) {
                $this->_subject->setError('No list selected in joomlamailer user plugin config!');
                return false;
            }

            return;
        }

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('profile', false);

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/fields.php');
        $fieldsModel = new joomailermailchimpintegrationModelFields();
        $mergeFields = $fieldsModel->getMergeFields($this->params->get('listid'));

        if (!empty($mergeFields['total_items'])) {
            $mergeFieldsConfig = $this->params->get('fields', array());

            $elements = array();
            foreach ($mergeFields['merge_fields'] as $field) {
                if (in_array($field['tag'], array('EMAIL', 'FNAME', 'LNAME', 'SIGNUPAPI'))
                    || !in_array($field['tag'], $mergeFieldsConfig) || $field['public'] === false) {
                    continue;
                }

                $attr = $options = '';
                switch ($field['type']) {
                    case 'url':
                        $type = 'url';
                        break;
                    case 'date':
                        $format = str_replace(array('DD', 'MM', 'YYYY'), array('%d', '%m', '%Y'), $field['options']['date_format']);
                        $type = 'calendar';
                        $attr = 'format="' . $format . '"';
                        $field['help_text'] = $field['options']['date_format'];
                        break;
                    case 'birthday':
                        $type = 'birthday';
                        $attr = 'format="' . $this->params->get('dateFormat') . '"';
                        $field['help_text'] = $this->params->get('dateFormat');
                        break;
                    case 'address':
                        $type = 'address';
                        break;
                    case 'phone':
                        $type = 'tel';
                        break;
                    case 'number':
                        $type = 'number';
                        break;
                    case 'radio':
                        $type = 'radio';
                        if (isset($field['options']['choices'])) {
                            foreach ($field['options']['choices'] as $choice) {
                                $options .= '<option value="' . $choice . '">' . $choice . '</option>';
                            }
                        }
                        break;
                    case 'dropdown':
                        $type = 'list';
                        if (isset($field['options']['choices'])) {
                            $options = '<option value=""></option>';
                            foreach ($field['options']['choices'] as $choice) {
                                $options .= '<option value="' . $choice . '">' . $choice . '</option>';
                            }
                        }
                        $attr = 'multiple="false"';
                        break;
                    default:
                        $type = 'text';
                }

                $elements[] = new SimpleXMLElement('<fieldset name="joomlamailer_merges" label="PLG_USER_JOOMLAMAILER_MERGE_FIELDS">
                    <field name="' . $field['tag'] . '"
                        type="' . $type . '"
                        label="' . $field['name'] . '"
                        description="' . $field['help_text'] . '"
                        class=""
                        size=""
                        required="' . $field['required'] . '" ' .
                        $attr . '>' .
                        $options . '
                        </field>
                </fieldset>');
            }

            if (count($elements)) {
                $form->setFields($elements, 'joomlamailer_merges');
            }
        }

        // interest categories
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
        $groupsModel = new joomailermailchimpintegrationModelGroups();
        $interestCategories = $groupsModel->getListInterestCategories($this->params->get('listid'));

        if (!empty($interestCategories['total_items'])) {
            $interestsConfig = $this->params->get('interests', array());

            $elements = array();
            foreach ($interestCategories['categories'] as $category) {
                if (!in_array($category['id'], $interestsConfig)) {
                    continue;
                }

                switch ($category['type']) {
                    case 'dropdown':
                        $type = 'list';
                        break;
                    case 'radio':
                        $type = 'radio';
                        break;
                    case 'checkboxes':
                        $type = 'checkboxes';
                        break;
                }

                $interests = $groupsModel->getListInterestCategories($this->params->get('listid'), $category['id']);
                if (empty($interests['total_items'])) {
                    continue;
                }
                $options = '';
                foreach ($interests['interests'] as $interest) {
                    $options .= '<option value="' . $interest['id'] . '">' . $interest['name'] . '</option>';
                }

                $elements[] = new SimpleXMLElement('<fieldset name="joomlamailer_interests" label="PLG_USER_JOOMLAMAILER_INTERESTS">
                    <field name="' . $category['id'] . '"
                        type="' . $type . '"
                        label="' . $category['title'] . '">' .
                        $options . '
                        </field>
                </fieldset>');
            }

            if (count($elements)) {
                $form->setFields($elements, 'joomlamailer_interests');
            }
        }

		return true;
	}

    /**
     * Method is called before user data is stored in the database
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isnew  True if a new user is stored.
     * @param   array    $data   Holds the new user data.
     *
     * @return    boolean
     */
    public function onUserBeforeSave($oldUser, $isNew, $newUser) {
        /*file_put_contents(__DIR__ . '/oldUser' . microtime(true) . '.txt', print_r($oldUser, true) . "\n" . print_r($isNew, true)
            . "\n" . print_r($newUser, true) . "\n" . print_r($_POST, true));*/
        self::$oldEmail = $oldUser['email'];
    }

	/**
	 * saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $success true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return bool
	 */
	public function onUserAfterSave($data, $isNew, $success, $error) {
        if (!$this->params->get('listid') || !$success) {
            return;
        }

        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDBO();

        $option = $this->app->input->getCmd('option');
        $task = $this->app->input->getCmd('task');

        $userId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');
        $email = $data['email'];

        // avoid doubleexecution of community builder during registration
        if ($option == 'com_comprofiler' && $task == 'saveregisters' && $isNew && !$data['activation']) {
            return;
        }

        /*file_put_contents(__DIR__ . '/register' . microtime(true) . '.txt', print_r($data, true) . "\nisNew: " . print_r($isNew, true)
          . "\noption: " . print_r($option, true) . "\ntask: " . print_r($task, true) . "\n" . print_r($_POST, true));*/

        if (($option == 'com_users' && $task == 'activate') || ($option == 'com_comprofiler' && $task == 'confirm')
            || ($option == 'com_community' && $task == 'activate' && $data['activation'] == '')) {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('fname', 'lname', 'email', 'groupings', 'merges')))
                ->from($this->db->qn('#__joomailermailchimpintegration_signup'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
            try {
                $res = $this->db->setQuery($query)->loadObject();
            } catch (Exception $e) {}
            if (!$res) {
                return;
            }

            // create hidden signup date merge var if it doesn't exist
            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/sync.php');
            $syncModel = new joomailermailchimpintegrationModelSync();
            $syncModel->createSignupApiMergeField($this->params->get('listid'));

            // build API data object
            $params = array(
                'email_address' => $res->email,
                'email_type'    => 'html',
                'status'        => 'subscribed',
                'ip_signup'     => $this->getIpAddress(),
                'ip_opt'        => $this->getIpAddress(),
                'interests'     => json_decode($res->groupings, true)
            );

            $params['merge_fields'] = array_merge(array(
                'FNAME'     => $res->fname,
                'LNAME'     => $res->lname,
                'SIGNUPAPI' => date('Y-m-d')
            ), json_decode($res->merges, true));

            // subscribe the user
            try {
                $this->getApi()->listMemberSubscribe($this->params->get('listid'), $params);

                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__joomailermailchimpintegration_signup'))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
                $this->db->setQuery($query)->execute();

                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__joomailermailchimpintegration'))
                    ->set($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
                $this->db->setQuery($query)->execute();

            } catch (MailchimpException $e) {
                $this->_subject->setError("Unable to subscribe to the newsletter list!\n\tCode=" . $e->getCode()
                                          . "\n\tMsg=" . $e->getMessage() . "\n");

                return;
            }

            return;
        }

        // process registration / profile form
        if ($option == 'com_community') {
            if (!$isNew || !in_array($task, array('registerUpdateProfile', 'save'))) {
                return;
            }

            $query = $this->db->getQuery(true)
                ->select($this->db->qn('id'))
                ->from($this->db->qn('#__community_fields'))
                ->where($this->db->qn('fieldcode') . ' = ' . $this->db->q('newsletter'));
            try {
                $fieldId = $this->db->setQuery($query)->loadResult();
            } catch (Exception $e) {}
            if (!$fieldId) {
                return;
            }

            $subscribe = ($this->app->input->getCmd('field' . $fieldId, false) ? 1 : 0);
            $name = $data['name'];
        } else  if ($option == 'com_comprofiler') {
            $subscribe = $this->app->input->getInt('cb_newsletter', false);
            $name = $this->app->input->getString('name');
        } else  if ($option == 'com_virtuemart') {
            $subscribe = $this->app->input->getInt('newsletter', false);
            $name = $this->app->input->getString('name');
        } else {
            $jform = $this->app->input->get('jform', array(), 'RAW');
            /*if ($this->app->isSite()) {
                var_dump($jform);die;
            }*/
            if (!isset($jform['joomlamailer'])) {
                return;
            }
            $subscribe = $jform['joomlamailer']['subscribe'];
            $name = $jform['name'];
        }

        // Check if the user is already activated and is subscribed
        $isSubscribed = false;
        if (!$data['activation'] && $data['email'] && !empty(self::$oldEmail)) {
            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/subscriber.php');
            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/lists.php');
            $subscriberModel = new joomailermailchimpintegrationModelSubscriber();
            $userLists = $subscriberModel->getListsForEmail(self::$oldEmail);
            if (!empty($userLists['total_items'])) {
                foreach ($userLists['lists'] as $list) {
                    if ($list['id'] == $this->params->get('listid')) {
                        $isSubscribed = true;
                        break;
                    }
                }
            }
        }

        // split name into first and last name
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0];
        unset($nameParts[0]);
        $lastName = implode(' ', $nameParts);

        // User wishes to subscribe/update interests
        if ($subscribe == 1) {
            // Get merge fields from API
            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/fields.php');
            $fieldsModel = new joomailermailchimpintegrationModelFields();
            $mergeFields = $fieldsModel->getMergeFields($this->params->get('listid'));
            $mergeFieldsConfig = $this->params->get('fields');

            // Get interest groups from API
            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
            $groupsModel = new joomailermailchimpintegrationModelGroups();
            $interestCategories = $groupsModel->getListInterestCategories($this->params->get('listid'));
            $interestsConfig = $this->params->get('interests');

            $merges = $interests = array();
            if ($option == 'com_users') {
                if (!empty($mergeFields['total_items']) && $mergeFieldsConfig) {
                    foreach ($mergeFields['merge_fields'] as $field) {
                        if (in_array($field['tag'], array('EMAIL', 'FNAME', 'LNAME', 'SIGNUPAPI'))) {
                            continue;
                        }

                        if (isset($jform['joomlamailer_merges'][$field['tag']])) {
                            $value = $jform['joomlamailer_merges'][$field['tag']];

                            if ($field['type'] == 'birthday') {
                                $value = $value['month'] . '/' . $value['day'];
                            }

                            $merges[$field['tag']] = $value;
                        }
                    }
                }

                if (!empty($interestCategories['total_items'])) {
                    foreach ($interestCategories['categories'] as $category) {
                        $interests = $groupsModel->getListInterestCategories($this->params->get('listid'), $category['id']);
                        if (empty($interests['total_items'])) {
                            continue;
                        }

                        foreach ($interests['interests'] as $interest) {
                            if (!in_array($interest['category_id'], $interestsConfig)
                                || !isset($jform['joomlamailer_interests'][$interest['category_id']])) {
                                $groupings[$interest['id']] = false;
                            } else {
                                if ((is_array($jform['joomlamailer_interests'][$category['id']])
                                    && in_array($interest['id'], $jform['joomlamailer_interests'][$interest['category_id']]))
                                    || (!is_array($jform['joomlamailer_interests'][$interest['category_id']])
                                    && $jform['joomlamailer_interests'][$interest['category_id']] == $interest['id'])) {
                                    $groupings[$interest['id']] = true;
                                } else {
                                    $groupings[$interest['id']] = false;
                                }
                            }
                        }
                    }
                }
            } else if (in_array($option, array('com_comprofiler', 'com_community', 'com_virtuemart'))) {
                // Get custom fields
                $query = $this->db->getQuery(true)
                    ->select($this->db->qn(array('dbfield', 'grouping_id', 'type', 'framework'), array('dbfield', 'gid', 'type', 'framework')))
                    ->from($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                    ->where($this->db->qn('listID') . ' = ' . $this->db->q($this->params->get('listid')));
                $this->db->setQuery($query);
                $customFields = $this->db->loadAssocList();

                if ($customFields) {
                    // loop over merge vars
                    if (!empty($mergeFields['total_items']) && $mergeFieldsConfig) {
                        foreach ($mergeFields['merge_fields'] as $field) {
                            foreach ($customFields as $cf) {
                                if ($cf['type'] !== 'field') {
                                    continue;
                                }
                                if ($field['tag'] == strtoupper($cf['gid'])) {
                                    if (($option == 'com_comprofiler' && $cf['framework'] == 'CB')
                                        || ($option == 'com_virtuemart' && $cf['framework'] == 'VM')) {
                                        if ($field['type'] == 'date') {
                                            if ($option == 'com_virtuemart') {
                                                $valDay = $this->app->input->getString('birthday_selector_day');
                                                $valMonth = $this->app->input->getString('birthday_selector_month');
                                                $valYear = $this->app->input->getString('birthday_selector_year');
                                                $val = $valMonth . '/' . $valDay . '/' . $valYear;
                                            } else {
                                                $val = $this->app->input->getString($cf['dbfield']);
                                            }
                                            if ($val) {
                                                $merges[$field['tag']] = substr($val, 3, 2) . '-' . substr($val, 0, 2) .
                                                    '-' . substr($val, 6, 4);
                                            }
                                        } else {
                                            $val = $this->app->input->getString($cf['dbfield']);
                                            $merges[$field['tag']] = $val;
                                        }
                                    } else {
                                        $val = $this->app->input->get('field' . $cf['dbfield'], null, 'RAW');
                                        if ($val) {
                                            // convert community builder field values
                                            if ($option == 'com_community') {
                                                $query = $this->db->getQuery(true)
                                                    ->select($this->db->qn('type'))
                                                    ->from($this->db->qn('#__community_fields'))
                                                    ->where($this->db->qn('id') . ' = ' . $this->db->q($cf['dbfield']));
                                                $fieldType = $this->db->setQuery($query)->loadResult();

                                                if (in_array($fieldType, array('checkbox', 'multicheckbox')) && is_array($val)) {
                                                    $val = $val[0];
                                                } else if ($fieldType == 'country') {
                                                    $jlang = JFactory::getLanguage();
                                                    $jlang->load('com_community.country', JPATH_SITE, 'en-GB', true);
                                                    $val = JText::_($val);
                                                } else if ($fieldType == 'birthdate') {
                                                    $dateFormat = 'DD/MM';
                                                    if ($field['type'] == 'birthday') {
                                                        $dateFormat = $field['options']['date_format'];
                                                    }
                                                    $val = strtr($dateFormat, array('DD' => $val[0], 'MM' => $val[1]));
                                                } else if ($fieldType == 'url') {
                                                    $val = implode('', $val);
                                                } else if ($fieldType == 'gender') {
                                                    $val = \Joomla\String\StringHelper::ucfirst($val);
                                                }
                                            }

                                            if ($field['type'] == 'date' && is_array($val)) {
                                                $merges[$field['tag']] = $val[2] . '-' . $val[1] . '-' . $val[0];
                                            } else {
                                                $merges[$field['tag']] = $val;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // loop over groupings
                    if (!empty($interestCategories['total_items'])) {
                        foreach ($interestCategories['categories'] as $category) {
                            foreach ($customFields as $cf) {
                                if ($cf['type'] == 'group') {
                                    if ($category['id'] == $cf['gid']) {
                                        $interests = $groupsModel->getListInterestCategories($this->params->get('listid'), $category['id']);
                                        if (empty($interests['total_items'])) {
                                            continue;
                                        }

                                        if (($option == 'com_comprofiler' && $cf['framework'] == 'CB')
                                            || ($option == 'com_virtuemart' && $cf['framework'] == 'VM')){
                                            $field = $this->app->input->getString($cf['dbfield']);
                                        } else {
                                            if ($this->app->input->getString('field' . $cf['dbfield'], 0)) {
                                                $field = $this->app->input->getString('field' . $cf['dbfield']);
                                            }
                                        }
                                        if (isset($field) && is_array($field)) {
                                            foreach ($field as $g) {
                                                foreach ($interests['interests'] as $interest) {
                                                    if ($g == $interest['name']) {
                                                        $groupings[$interest['id']] = true;
                                                    } else {
                                                        $groupings[$interest['id']] = false;
                                                    }
                                                }
                                            }
                                        } else {
                                            foreach ($interests['interests'] as $interest) {
                                                if (isset($field) && $field == $interest['name']) {
                                                    $groupings[$interest['id']] = true;
                                                } else {
                                                    $groupings[$interest['id']] = false;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // If this is a new user then just store details now and subscribe the user later at activation
            if ($data['activation']) {
                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__joomailermailchimpintegration_signup'))
                    ->set(array(
                        $this->db->qn('fname') . ' = ' . $this->db->q($firstName),
                        $this->db->qn('lname') . ' = ' . $this->db->q($lastName),
                        $this->db->qn('email') . ' = ' . $this->db->q($email),
                        $this->db->qn('merges') . ' = ' . $this->db->q(json_encode($merges)),
                        $this->db->qn('groupings') . ' = ' . $this->db->q(json_encode($groupings))
                    ));
                try {
                    $this->db->setQuery($query)->execute();
                } catch (Exception $e) {}

            } else if ($task != 'saveregisters') {
                $params = array(
                    'email_address' => $email,
                    'email_type'    => 'html',
                    'status'        => 'subscribed',
                    'interests'     => $groupings
                );

                $params['merge_fields'] = array_merge(array(
                    'FNAME' => $firstName,
                    'LNAME' => $lastName
                ), $merges);

                // Get the users ip address unless the admin is saving his profile in backend
                if ($this->app->isSite()) {
                    $params['ip_signup'] = $params['ip_opt'] = $this->getIpAddress();
                }

                if ($isSubscribed === false) {
                    // subscribe the user
                    $this->getApi()->listMemberSubscribe($this->params->get('listid'), $params);

                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__joomailermailchimpintegration'))
                        ->set(array(
                            $this->db->qn('userid') . ' = ' . $this->db->q($userId),
                            $this->db->qn('email') . ' = ' . $this->db->q($email),
                            $this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid'))
                        ));
                    try {
                        $this->db->setQuery($query)->execute();
                    } catch (Exception $e) {}
                } else {
                    // update the users subscription
                    if ($email != self::$oldEmail) {
                        // update local database entry
                        $query = $this->db->getQuery(true)
                            ->update($this->db->qn('#__joomailermailchimpintegration'))
                            ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                            ->where($this->db->qn('email') . ' = ' . $this->db->q(self::$oldEmail))
                            ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
                        try {
                            $this->db->setQuery($query)->execute();
                        } catch (Exception $e) {}

                        $params['email_address_old'] = self::$oldEmail;
                    }

                    $this->getApi()->listMemberSubscribe($this->params->get('listid'), $params);
                }
            }

        // user wishes to unsubscribe
        } else if (!$subscribe && $isSubscribed) {
            $this->getApi()->listMemberUnsubscribe($this->params->get('listid'), $email);

            // remove local database entry
            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__joomailermailchimpintegration'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
            try {
                $this->db->setQuery($query)->execute();
            } catch (Exception $e) {}
        }

		return true;
	}

    /**
     * Unsubscribe the user when his account is deleted and if this option is set in the plugin configuration
     */
    public function onUserAfterDelete($user, $success, $msg) {
        $userId = Joomla\Utilities\ArrayHelper::getValue($user, 'id', 0, 'int');
        $unsubscribe = $this->params->get('unsubscribe', 0);

        if (!$success || !$userId || !$this->params->get('listid') || !$unsubscribe) {
            return;
        }

        // unsubscribe the user
        try {
            $this->getApi()->listMemberUnsubscribe($this->params->get('listid'), $user['email']);
        } catch (MailchimpException $e) {}

        // delete traces from database
        try {
            $this->db = JFactory::getDBO();
            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__joomailermailchimpintegration'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($user['email']))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
            $this->db->setQuery($query)->execute();

            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__joomailermailchimpintegration_signup'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($user['email']));
            $this->db->setQuery($query)->execute();
        } catch (Exception $e) {}
    }

    private function getApi() {
        if (!PlgUserJoomlamailer::$MC) {
            $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
            $MCapi = $params->get('params.MCapi');

            require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/Mailchimp/JoomlamailerMailchimp.php');
            PlgUserJoomlamailer::$MC = new JoomlamailerMailchimp($MCapi);
        }

        return PlgUserJoomlamailer::$MC;
    }

    private function getIpAddress() {
        $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return '';
    }
}
