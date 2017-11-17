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

JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.installer.helper');
JLoader::import('joomla.installer.installer');

class joomailermailchimpintegrationControllerInstaller extends joomailermailchimpintegrationController {

    private $dbQueries = array(
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `userid` int(11) unsigned NOT NULL ,
                `email` varchar(50) NOT NULL ,
                `listid` varchar(25) NOT NULL ,
                PRIMARY KEY (`id`),
                UNIQUE KEY `useridListidEmail` (`userid`,`listid`,`email`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_custom_fields` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `listID` varchar(25) NOT NULL,
                `name` varchar(255) NOT NULL,
                `framework` varchar(10) NOT NULL default '',
                `dbfield` varchar(255) NOT NULL default '',
                `grouping_id` varchar(25) NOT NULL default '',
                `type` varchar(5) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_campaigns` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `list_id` varchar(25) NOT NULL,
                `list_name` text NOT NULL,
                `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `subject` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `from_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `from_email` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `reply` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `confirmation` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                `creation_date` int(10) unsigned NOT NULL,
                `recipients` int(22) unsigned NOT NULL,
                `sent` tinyint(4) unsigned NOT NULL,
                `cid` varchar(25) NOT NULL,
                `cdata` text NOT NULL,
                `folder_id` varchar(11) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_signup` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `fname` varchar(100),
                `lname` varchar(100),
                `email` varchar(100) NOT NULL,
                `groupings` text NOT NULL,
                `merges` text NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_misc` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `listid` varchar(25) character set utf8 NOT NULL,
                `type` varchar(50) character set utf8 NOT NULL,
                `value` text character set utf8 NOT NULL,
                PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `crm` varchar(255) NOT NULL,
                `params` text NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;"),
        array(
            'task' => 'create',
            'data' => "CREATE TABLE IF NOT EXISTS `#__joomailermailchimpintegration_crm_users` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `crm` varchar(20) NOT NULL,
                `user_id` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;"),
        array('task' => 'updateColumns')
    );

    private $extensions = array(
        'plg_user_joomlamailer',
        'mod_mailchimpsignup',
        'mod_mailchimpstats',
        'plg_joomlamailer_com_content',
        'plg_joomlamailer_com_k2',
        'plg_joomlamailer_com_virtuemart',
        'plg_joomlamailer_table_of_content',
        'plg_joomlamailer_sidebar_editor',
        'plg_joomlamailer_facebook_icon',
        'plg_joomlamailer_twitter_icon',
        'plg_joomlamailer_instagram_icon',
        'plg_joomlamailer_myspace_icon',
        'plg_joomlamailer_JomSocial_discussions',
        'plg_joomlamailer_JomSocial_profiles',
        'plg_joomlamailer_JomSocial'
    );

    public function prepare() {
        // remove obsolete files
        $removeFiles = array(
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/admin.joomailermailchimpintegration.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/install.joomailermailchimpintegration.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/uninstall.joomailermailchimpintegration.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/version.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/controllers/archive.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/controllers/installation.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/controllers/joomailermailchimpintegration.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/controllers/joomailermailchimpintegrationinstall.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/controllers/suppression.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/cache_15.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/cache_16.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/footer.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/MCauth.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/helpers/update.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/MCAPI.class.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/archive.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/joomailermailchimpintegration.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/joomailermailchimpintegrations.php',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/suppression.php'
        );
        JFile::delete($removeFiles);

        // remove obsolete folders
        $removeFolders = array(
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/assets',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/joomailer',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/jsonwrapper',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/views/archive',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/views/joomailermailchimpintegrations',
            JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/views/suppression',
            JPATH_SITE . '/components/com_joomailermailchimpintegration/assets',
            JPATH_SITE . '/components/com_joomailermailchimpsignup/templates',
        );
        foreach($removeFolders as $rf){
            if (JFolder::exists($rf)) {
                JFolder::delete($rf);
            }
        }

        echo json_encode(array('success' => true));
    }

    public function updatedb() {
        $step = $this->input->getUint('step');

        if (!isset($this->dbQueries[$step - 1])) {
            echo json_encode(array('error' => "Invalid request (step = {$step})"));
            exit;
        }

        $queryData = $this->dbQueries[$step - 1];

        if ($queryData['task'] == 'create') {
            echo json_encode($this->createTable($queryData['data']));
        } else if ($queryData['task'] == 'updateColumns') {
            echo json_encode($this->updateColumns());
        }
    }

    public function installext() {
        $packagesPath = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/extensions/';

        if (!JFolder::exists($packagesPath)) {
            echo json_encode(array('error' => 'Packages directory does not exist. Please install again.'));
            exit;
        }

        $step = $this->input->getUint('step');

        if (!isset($this->extensions[$step - 1])) {
            echo json_encode(array('error' => "Invalid request (step = {$step})"));
            exit;
        }

        $db = JFactory::getDBO();
        $res = array('success' => true);

        $extension = $this->extensions[$step - 1] . '.zip';
        $package = JInstallerHelper::unpack($packagesPath . $extension);
        $installer = new JInstaller();

        if (!$installer->install($package['dir'])) {
            $res = array(
                'error' => 'Installation failed!'
            );
            echo json_encode($res);
            exit;
        } else {
            if (strpos($extension, 'mod_mailchimpstats') !== false) {
                // publish admin stats module and place it on cpanel
                $query = $db->getQuery(true)
                    ->update($db->qn('#__modules'))
                    ->set($db->qn('published') . ' = ' . $db->q(1))
                    ->set($db->qn('position') . ' = ' . $db->q('cpanel'))
                    ->set($db->qn('ordering') . ' = ' . $db->q('-1'))
                    ->set($db->qn('params') . ' = ' . $db->q('{"cache":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"0"}'))
                    ->where($db->qn('module') . ' = ' . $db->q('mod_mailchimpstats'));

                $db->setQuery($query)->execute();
                $query = $db->getQuery(true)
                    ->select($db->qn('id'))
                    ->from($db->qn('#__modules'))
                    ->where($db->qn('module') . ' = ' . $db->q('mod_mailchimpstats'));
                $db->setQuery($query);
                $moduleId = $db->loadResult();
                $query = $db->getQuery(true)
                    ->insert($db->qn('#__modules_menu'))
                    ->set($db->qn('moduleid') . ' = ' . $db->q($moduleId))
                    ->set($db->qn('menuid') . ' = ' . $db->q(0));
                try {
                    $db->setQuery($query)->execute();
                } catch (Exception $e) {}
            } else if (strpos($extension, 'plg_joomlamailer_table_of_content') !== false) {
                // make sure table of content plugin is executed at last after all content plugins
                $query = $db->getQuery(true)
                    ->update($db->qn('#__extensions'))
                    ->set($db->qn('ordering') . ' = ' . $db->q(99999))
                    ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                    ->where($db->qn('folder') . ' = ' . $db->q('joomlamailer'))
                    ->where($db->qn('element') . ' = ' . $db->q('joomlamailer_table_of_content'));
                $db->setQuery($query)->execute();
            }
        }

        // cleanup
        if (JFolder::exists($package['dir'])) {
            JFolder::delete($package['dir']);
        }

        if ($step == count($this->extensions)) {
            // enable plugins
            $query = $db->getQuery(true)
                ->update($db->qn('#__extensions'))
                ->set($db->qn('enabled') . ' = ' . $db->q(1))
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('folder') . ' = ' . $db->q('joomlamailer'));
            $db->setQuery($query)->execute();

            // set plugin order
            $query = $db->getQuery(true)
                ->select('MAX(' . $db->qn('ordering') . ')')
                ->from($db->qn('#__extensions'))
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('folder') . ' = ' . $db->q('joomlamailer'));
            $db->setQuery($query);
            try {
                $ordering = $db->loadResult();
            } catch (Exception $e) {
                $res = array(
                    'error' => $e->getMessage(),
                    'query' => $query
                );
                echo json_encode($res);
                exit;
            }

            $query = $db->getQuery(true)
                ->select($db->qn('extension_id') . ', IF(' . $db->qn('element') . ' IN ("com_content", "com_k2"), 1, 0) AS prio')
                ->from($db->qn('#__extensions'))
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('folder') . ' = ' . $db->q('joomlamailer'))
                ->where($db->qn('ordering') . ' = ' . $db->q(0))
                ->order('prio DESC, ' . $db->qn('extension_id') . ' ASC');
            $db->setQuery($query);
            $plugins = $db->loadObjectList();

            if ($plugins) {
                foreach ($plugins as $plugin) {
                    $query = $db->getQuery(true)
                        ->update($db->qn('#__extensions'))
                        ->set($db->qn('ordering') . ' = ' . $db->q(++$ordering))
                        ->where($db->qn('extension_id') . ' = ' . $db->q($plugin->extension_id));
                    $db->setQuery($query)->execute();
                }
            }
        }

        echo json_encode($res);
    }

    public function migrate() {
        $step = $this->input->getUint('step');

        if (!$step) {
            echo json_encode(array('error' => "Invalid request (step = {$step})"));
            exit;
        }

        $db = JFactory::getDBO();
        $res = array('success' => true);

        switch($step) {
            // migrate plugin configuration
            case 1:
                $query = $db->getQuery(true)
                    ->select($db->qn(array('params', 'enabled')))
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                    ->where($db->qn('folder') . ' = ' . $db->q('system'))
                    ->where($db->qn('element') . ' = ' . $db->q('joomailermailchimpsignup'));
                $db->setQuery($query);
                try {
                    $pluginData = $db->loadObject();
                } catch (Exception $e) {
                    $res = array(
                        'error' => $e->getMessage(),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                if (!$pluginData) {
                    $res = array(
                        'notRequired' => true
                    );
                    break;
                }

                $query = $db->getQuery(true)
                    ->update($db->qn('#__extensions'))
                    ->set($db->qn('enabled') . ' = ' . $db->q($pluginData->enabled))
                    ->set($db->qn('params') . ' = ' . $db->q($pluginData->params))
                    ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                    ->where($db->qn('folder') . ' = ' . $db->q('user'))
                    ->where($db->qn('element') . ' = ' . $db->q('joomlamailer'));
                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    $res = array(
                        'error' => $e->getMessage(),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                break;

            // uninstall system plugin
            case 2:
                $query = $db->getQuery(true)
                    ->select($db->qn('extension_id'))
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                    ->where($db->qn('folder') . ' = ' . $db->q('system'))
                    ->where($db->qn('element') . ' = ' . $db->q('joomailermailchimpsignup'));
                $db->setQuery($query);
                try {
                    $extension_id = $db->loadResult();
                } catch (Exception $e) {
                    $res = array(
                        'error' => $e->getMessage(),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                if ($extension_id) {
                    jimport('joomla.installer.installer');
                    $installer = new JInstaller();
                    $installer->uninstall('plugin', $extension_id, 0);
                } else {
                    $res = array(
                        'notRequired' => true
                    );
                }

                break;

            // uninstall signup component
            case 3:
                $query = $db->getQuery(true)
                    ->select($db->qn('extension_id'))
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('type') . ' = ' . $db->q('component'))
                    ->where($db->qn('element') . ' = ' . $db->q('com_joomailermailchimpsignup'));
                $db->setQuery($query);
                try {
                    $extension_id = $db->loadResult();
                } catch (Exception $e) {
                    $res = array(
                        'error' => $e->getMessage(),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                if ($extension_id) {
                    jimport('joomla.installer.installer');
                    $installer = new JInstaller();
                    $installer->uninstall('component', $extension_id, 0);

                    try {
                        $db->dropTable('#__joomailermailchimpsignup');
                    } catch (Exception $e) {
                        $res = array(
                            'error' => $e->getMessage(),
                            'query' => $query
                        );
                        echo json_encode($res);
                        exit;
                    }
                } else {
                    $res = array(
                        'notRequired' => true
                    );
                }

                break;

            // uninstall community builder plugin
            case 4:
                if (!JFile::exists(JPATH_ROOT . '/libraries/CBLib/CB/Application/CBApplication.php')
                    || !JFile::exists(JPATH_ROOT . '/libraries/CBLib/CB/Legacy/LegacyComprofilerFunctions.php')
                    || !JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.class.php')) {
                    $res = array(
                        'notRequired' => true
                    );
                    echo json_encode($res);
                    exit;
                }

                $query = $db->getQuery(true)
                    ->select($db->qn('id'))
                    ->from($db->qn('#__comprofiler_plugin'))
                    ->where($db->qn('element') . ' = ' . $db->q('cb.joomlamailer'))
                    ->where($db->qn('folder') . ' = ' . $db->q('plug_joomlamailercbsignup'));
                $db->setQuery($query);
                try {
                    $extension_id = $db->loadResult();
                } catch (Exception $e) {
                    $res = array(
                        'error' => $e->getMessage(),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                if ($extension_id) {
                    require_once(JPATH_ROOT . '/libraries/CBLib/CB/Application/CBApplication.php');
                    require_once(JPATH_ROOT . '/libraries/CBLib/CB/Legacy/LegacyComprofilerFunctions.php');
                    CB\Application\CBApplication::init()->getDI()->get('\CB\Legacy\LegacyFoundationFunctions');
                    require_once(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.class.php');
                    $cbInstallerPlugin = new cbInstallerPlugin();
                    $cbInstallerPlugin->uninstall($extension_id, 'com_joomailermailchimpintegration');
                } else {
                    $res = array(
                        'notRequired' => true
                    );
                }

                break;

            // migrate signup module parameters to json format
            case 5:
                $query = $db->getQuery(true)
                    ->select(array($db->qn('id'), $db->qn('title'), $db->qn('params')))
                    ->from($db->qn('#__modules'))
                    ->where($db->qn('module') . ' = ' . $db->q('mod_mailchimpsignup'))
                    ->where('((' . $db->qn('params') . ' LIKE ' . $db->q('%"fields":["%')
                        . ' AND ' . $db->qn('params') . ' NOT LIKE ' . $db->q('%"fields":["{%')
                        . ') OR (' . $db->qn('params') . ' LIKE ' . $db->q('%"interests":["%') . ' AND '
                        . $db->qn('params') . ' NOT LIKE ' . $db->q('%"interests":["{%') . '))');
                $db->setQuery($query);
                $modules = $db->loadObjectList();
                if (!$modules) {
                    $res = array(
                        'notRequired' => true
                    );
                    break;
                }

                $errors = array();
                foreach ($modules as $module) {
                    $params = $this->migrateModuleParams($module->params);

                    if ($params === false) {
                        $errors[] = 'Migration of module "' . $module->title . '" failed.';
                        continue;
                    }

                    $query = $db->getQuery(true)
                        ->update($db->qn('#__modules'))
                        ->set($db->qn('params') . ' = ' . $db->q($params))
                        ->where($db->qn('id') . ' = ' . $db->q($module->id));
                    $db->setQuery($query)->execute();
                }

                if (count($errors)) {
                    $res = array(
                        'error' => implode('; ', $errors),
                        'query' => $query
                    );
                    echo json_encode($res);
                    exit;
                }

                break;

            default:
                echo json_encode(array('error' => "Invalid request (step = {$step})"));
                exit;
        }

        echo json_encode($res);
    }

    /**
     * Migrate module parameter format from pre-3.x format
     */
    private function migrateModuleParams($params) {
        $params = json_decode($params, true);

        // get interest groups for this list
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/jmModel.php');
        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/groups.php');
        $groupsModel = new joomailermailchimpintegrationModelGroups();

        // fields
        if (!empty($params['fields']) && is_array($params['fields'])) {
            $fields = array();
            foreach ($params['fields'] as $field) {
                list($tag, $type, $name, $required, $choices) = explode(';', $field);
                $fieldArr = array(
                    'type'     => $type,
                    'tag'      => $tag,
                    'name'     => $name,
                    'required' => (int)$required
                );
                if (!empty($choices)) {
                    $choices = explode('##', $choices);
                    $fieldArr['choices'] = $choices;
                }

                $fields[] = json_encode($fieldArr);
            }
            $params['fields'] = $fields;
        }

        // interests
        if (!empty($params['interests']) && is_array($params['interests'])) {
            $interests = array();

            try {
                $listInterestCategories = $groupsModel->getListInterestCategories($params['listid']);
                $interestCategoryIdArr = array();
                foreach ($listInterestCategories['categories'] as $cat) {
                    $interestCategoryIdArr[$cat['title']] = $cat['id'];
                }

                foreach ($params['interests'] as $interest) {
                    list(, $type, $title, $groupNames) = explode(';', $interest);

                    $interestCategoryId = $interestCategoryIdArr[$title];
                    if (!$interestCategoryId) {
                        continue;
                    }

                    $listInterests = $groupsModel->getListInterestCategories($params['listid'], $interestCategoryId);
                    if (empty($listInterests['total_items'])) {
                        continue;
                    }
                    $interestsIdArr = array();
                    foreach ($listInterests['interests'] as $int) {
                        $interestsIdArr[$int['name']] = $int['id'];
                    }

                    $groups = array();
                    $groupNames = explode('####', $groupNames);
                    foreach ($groupNames as $groupName) {
                        if (!isset($interestsIdArr[$groupName])) {
                            continue;
                        }
                        $groups[] = array(
                            'id'   => $interestsIdArr[$groupName],
                            'name' => $groupName
                        );
                    }

                    if (!count($groups)) {
                        continue;
                    }

                    $interests[] = array(
                        'id'     => $interestCategoryId,
                        'type'   => $type,
                        'title'  => $title,
                        'groups' => $groups
                    );
                }
            } catch (Exception $e) {
                return false;
            }

            $params['interests'] = json_encode($interests);
        }

        return json_encode($params);
    }

    public function cleanup() {
        // remove extensions folder
        $path = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/extensions';
        if (JFolder::exists($path)) {
            JFolder::delete($path);
        }

        // remove installation init file
        $path = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/installer.init.ini';
        if (JFile::exists($path)) {
            JFile::delete($path);
        }

        return;
    }

    public function sendreport() {
        parse_str($this->input->getString('formData'), $formData);
        $errors = json_decode($this->input->getString('errors', '{}'));

        $config = JFactory::getConfig();
        $mailer = JFactory::getMailer();

        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );
        $mailer->setSender($sender);
        $mailer->addRecipient('errors@joomlamailer.com');
        $mailer->setSubject('joomlamailer installation error');

        $body = '';
        foreach ($formData as $index => $value) {
            if ($value) {
                $body .= "{$index} = {$value}\n";
            }
        }
        $body .= "\n" . print_r($errors, true);
        $mailer->setBody($body);

        $res = $mailer->Send();

        if ($res !== true) {
            echo 'Error sending email: ' .  $res->__toString();
            exit;
        }

        return true;
    }

    private function createTable($query) {
        $db = JFactory::getDBO();
        $db->setQuery($query);

        try {
            $db->execute();
            $res = array('success' => true);
        } catch (Exception $e) {
            $res = array(
                'error' => $e->getMessage(),
                'query' => $query
            );
        }

        return $res;
    }

    private function updateColumns() {
        $errors = array();

        // add userid field to joomailermailchimpintegration table
        $res = $this->AddColumnIfNotExists('#__joomailermailchimpintegration', 'userid', 'int(11) unsigned NOT NULL', 'id');
        if ($res !== true) {
            $errors[] = $res;
        }

        // add list_name column
        $res = $this->AddColumnIfNotExists('#__joomailermailchimpintegration_campaigns', 'list_name','TEXT NOT NULL', 'list_id');
        if ($res !== true) {
            $errors[] = $res;
        }

        // change data type of folder_id or create column if it does not exist
        try {
            $column = $this->getColumnData('#__joomailermailchimpintegration_campaigns', 'folder_id');
            if ($column) {
                if ($column->Type !== 'varchar(10)') {
                    $res = $this->alterTableColumn('#__joomailermailchimpintegration_campaigns', 'folder_id', 'VARCHAR(10) NOT NULL');
                    if ($res !== true) {
                        $errors[] = $res;
                    }
                }
            } else {
                // add folder_id column
                $res = $this->AddColumnIfNotExists('#__joomailermailchimpintegration_campaigns', 'folder_id', 'VARCHAR(10) NOT NULL', 'cdata');
                if ($res !== true) {
                    $errors[] = $res;
                }
            }
        } catch(Exception $e) {}

        // add type column
        $res = $this->AddColumnIfNotExists('#__joomailermailchimpintegration_custom_fields', 'type', "varchar(5) NOT NULL default 'group'", 'grouping_id');
        if ($res !== true) {
            $errors[] = $res;
        }

        // add merges column to signup table
        $res = $this->AddColumnIfNotExists('#__joomailermailchimpintegration_signup', 'merges', "text NOT NULL");
        if ($res !== true) {
            $errors[] = $res;
        }

        if (count($errors)) {
            $res = array(
                'error' => $errors
            );
        } else {
            $res = array('success' => true);
        }

        return $res;
    }

    private function AddColumnIfNotExists($table, $column, $attributes = "INT(11) NOT NULL DEFAULT '0'", $after = '') {
        if ($this->getColumnData($table, $column) === null) {
            try {
                $db = JFactory::getDBO();
                if ($after != '') {
                    $query = "ALTER TABLE `{$table}` ADD `{$column}` {$attributes} AFTER `{$after}`";
                } else {
                    $query = "ALTER TABLE `{$table}` ADD `{$column}` {$attributes}";
                }
                $db->setQuery($query)->execute();
            } catch (Exception $e) {
                return array(
                    'error' => $e->getMessage(),
                    'query' => $query
                );
            }
        }

        return true;
    }

    private function alterTableColumn($table, $oldName, $attributes, $newName = '') {
        if ($this->getColumnData($table, $oldName)) {
            try {
                $db = JFactory::getDBO();
                $newName = ($newName) ? $newName : $oldName;
                $query = "ALTER TABLE `{$table}` CHANGE `{$oldName}` `{$newName}` {$attributes}";
                $db->setQuery($query)->execute();
            } catch (Exception $e) {
                return array(
                    'error' => $e->getMessage(),
                    'query' => $query
                );
            }
        }

        return true;
    }

    private function getColumnData($table, $column) {
        try {
            $db = JFactory::getDBO();
            $query = "DESCRIBE `{$table}` `{$column}`";
            $columnData = $db->setQuery($query)->loadObject();
        } catch(Exception $e) {}

        return (empty($columnData) ? null : $columnData);
    }
}
