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

JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.filesystem.folder');

class com_joomailermailchimpintegrationInstallerScript {

    public function preflight($type, $parent) {
        if ($type == 'uninstall') {
            return true;
        }

        if (version_compare(JVERSION, '2.5.6', 'lt') || (version_compare(JVERSION, '3', 'ge') && version_compare(JVERSION, '3.2', 'lt'))) {
            $msg = '<p>You need at least Joomla! 2.5.6 or 3.2 to install this component</p>';
            JFactory::getApplication()->enqueueMessage($msg, 'error');
            return false;
        }

        // Workarounds for JInstaller bugs
        if (in_array($type, array('install', 'discover_install'))) {
            //$this->bugfixDBFunctionReturnedNoError();
        } else {
            $this->bugfixCantBuildAdminMenus();
            $this->fixSchemaVersion();
        }

        // clear joomlamailer update cache
        if (JFolder::exists(JPATH_ADMINISTRATOR . '/cache/joomlamailerUpdate')) {
            JFolder::delete(JPATH_ADMINISTRATOR . '/cache/joomlamailerUpdate');
        }
    }

    public function postflight($type, $parent) {
        if ($type == 'uninstall') {
            return true;
        }

        $destination = JPATH_ROOT . '/administrator/components/com_joomailermailchimpintegration/';
        $content = 'run installer';

        if (!JFile::write($destination . 'installer.init.ini', $content)) {
            ob_start(); ?>
            <table width="100%" border="0">
                <tr>
                    <td>
                        There was an error while trying to create an installation file.
                        Please ensure that the path <strong><?php echo $destination; ?></strong> has correct permissions and try again.
                    </td>
                </tr>
            </table><?php
            $html = ob_get_contents();
            @ob_end_clean();
        } else {
            $link = rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_joomailermailchimpintegration&view=installer';
            ob_start(); ?>
            <style type="text/css">
                .adminform { width: 100%; text-align: left; }
                .adminform > tbody > tr > th { font-size: 20px; }
                #jmMessageContainer {
                    width: 100%;
                    margin: 10px 0 25px 0;
                    border: 1px solid #616161;
                    background: #ffffff;
                }
                #jmMessageContainer > div {
                    padding: 20px;
                }
                #button-start-installer {
                    background-color: rgb(207,104,0);
                    background-image: -webkit-gradient( linear,
                                    left bottom,
                                    left top,
                                    color-stop(0.47, rgb(207,104,0)),
                                    color-stop(0.87, rgb(251,151,0))
                                    );
                    background-image: -moz-linear-gradient(center bottom,rgb(207,104,0) 47%,rgb(251,151,0) 87%);
                    border: 1px solid rgb(207,104,0);
                    -webkit-border-radius: 5px;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
                    margin: 0;
                    padding: 15px 25px;
                    outline: none;
                    font-size: 17px;
                    color: #ffffff !important;
                    text-shadow: 0 1px 2px #616161;
                    text-align: center;
                    cursor: pointer;
                    -moz-box-shadow: 1px 1px 6px 0px #454545;
                    -webkit-box-shadow: 1px 1px 6px 0px #454545;
                    box-shadow: 1px 1px 6px 0px #454545;
                }

                #button-start-installer:hover {
                    background-color: rgb(251,151,0);
                    background-image: -webkit-gradient( linear,
                                    left bottom,
                                    left top,
                                    color-stop(0.47, rgb(251,151,0)),
                                    color-stop(0.87, rgb(207,104,0))
                                    );
                    background-image: -moz-linear-gradient( center bottom,
                                        rgb(251,151,0) 47%,
                                        rgb(207,104,0) 87%
                                    );
                }
                #button-start-installer:active {
                    position: relative;
                    top: 1px;
                }
            </style>
            <div id="jmMessageContainer">
                <div>
                    <table style="width: 100%;">
                        <tr>
                            <td align="left" style="vertical-align: top;">
                                <h3>Good job!</h3>
                                You are almost done. Please click the button below to start the joomlamailer
                                installer, which will finalize the installation.
                            </td>
                            <td align="right">
                                <a href="http://www.joomlamailer.com" target="_blank" title="www.joomlamailer.com">
                                    <img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/logo.png" alt="joomlamailer" title="www.joomlamailer.com" />
                                </a>
                            </td>
                        </tr>
                    </table>
                    <div style="clear: both;"></div>
                    <div style="text-align:center">
                        <center>
                            <table border="0" cellpadding="10" cellspacing="20">
                                <tr>
                                    <td align="center" valign="middle">
                                        <input type="button" id="button-start-installer" onclick="window.location='<?php echo $link; ?>'" value="Complete the installation" />
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </div>
                </div>
            </div>
            <br /><?php
            $html = ob_get_contents();
            @ob_end_clean();
        }

        echo $html;
    }

    public function uninstall() {
        $db = JFactory::getDBO();
        $errors = '';
        $imgError = '<img src="images/publish_r.png" alt="">';
        $br = '<br />';

        $dropTables = array(
            '#__joomailermailchimpintegration',
            '#__joomailermailchimpintegration_campaigns',
            '#__joomailermailchimpintegration_crm',
            '#__joomailermailchimpintegration_crm_users',
            '#__joomailermailchimpintegration_custom_fields',
            '#__joomailermailchimpintegration_misc',
            '#__joomailermailchimpintegration_signup'
        );
        foreach ($dropTables as $dropTable) {
            try {
                $query = 'DROP TABLE IF EXISTS ' . $db->qn($dropTable) . ';';
                $db->setQuery($query)->execute();
            } catch (Exception $e) {
                $errors .= $imgError . JText::_('Unable to remove table') . ' ' . $dropTable . $br;
                $errors .= $e->getMessage();
            }
        }

        // uninstall signup component
        $query = "SELECT extension_id AS id FROM #__extensions WHERE `element` = 'com_joomailermailchimpsignup'";
        $db->setQuery($query);
        $component = $db->loadObject();

        if ($component) {
            JFolder::delete(JPATH_SITE . '/components/com_joomailermailchimpsignup');
            JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpsignup');

            JFile::delete(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_joomailermailchimpsignup.ini');

            $query = "DELETE FROM #__extensions WHERE `element` = 'com_joomailermailchimpsignup'";
            $db->setQuery($query);
            $db->execute();

            $query = "DELETE FROM #__assets WHERE `name` = 'com_joomailermailchimpsignup'";
            $db->setQuery($query);
            $db->execute();

            $query = "DROP TABLE IF EXISTS `#__joomailermailchimpsignup`";
            $db->setQuery($query);
            $db->execute();
        }

        // uninstall modules
        $query = "SELECT extension_id AS id FROM #__extensions WHERE `element` = 'mod_mailchimpsignup' OR `element` = 'mod_mailchimpstats'";
        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        if ($extensions) {
            foreach($extensions as $ext) {
                $installer = new JInstaller();
                $installer->uninstall('module', $ext->id, 0);
            }
        }

        // uninstall plugins
        $query = "SELECT extension_id AS id FROM #__extensions WHERE `folder` = 'joomlamailer' OR `element` = 'joomailermailchimpsignup' OR `element` = 'fmsts' OR `element` = 'joomlamailer'";
        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        if ($extensions ){
            foreach($extensions as $ext ){
                $installer = new JInstaller();
                $installer->uninstall('plugin', $ext->id, 0);
            }
        }

        if ($errors) {
            echo $errors;
            return false;
        }

        return true;
    }

    /**
    * Joomla! 1.6+ bugfix for "DB function returned no error"
    */
    private function bugfixDBFunctionReturnedNoError() {
        $db = JFactory::getDbo();

        // Fix broken #__assets records
        $query = $db->getQuery(true)
            ->select($db->qn('id'))
            ->from($db->qn('#__assets'))
            ->where($db->qn('name') . ' = ' . $db->q('com_focontentuploader'));
        $db->setQuery($query);
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids = $db->loadColumn();
        } else {
            $ids = $db->loadResultArray();
        }
        if (!empty($ids)) {
            foreach($ids as $id) {
                $query = $db->getQuery(true)
                    ->delete('#__assets')
                    ->where($db->qn('id') . ' = ' . $db->q($id));
                $db->setQuery($query)->execute();
            }
        }

        // Fix broken #__extensions records
        $query = $db->getQuery(true)
            ->select($db->qn('extension_id'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('element') . ' = ' . $db->q('com_focontentuploader'));
        $db->setQuery($query);
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids = $db->loadColumn();
        } else {
            $ids = $db->loadResultArray();
        }
        if (!empty($ids)) {
            foreach($ids as $id) {
                $query = $db->getQuery(true);
                $query->delete('#__extensions')
                ->where($db->qn('extension_id') . ' = ' . $db->q($id));
                $db->setQuery($query);
                $db->execute();
            }
        }

        // Fix broken #__menu records
        $query = $db->getQuery(true)
            ->select($db->qn('id'))
            ->from($db->qn('#__menu'))
            ->where($db->qn('type') . ' = ' . $db->q('component'))
            ->where($db->qn('menutype') . ' = ' . $db->q('main'))
            ->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=com_focontentuploader'));
        $db->setQuery($query);

        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids = $db->loadColumn();
        } else {
            $ids = $db->loadResultArray();
        }
        if (!empty($ids)) {
            foreach($ids as $id) {
                $query = $db->getQuery(true);
                $query->delete('#__menu')
                ->where($db->qn('id').' = '.$db->q($id));
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
    * Joomla! 1.6+ bugfix for "Can not build admin menus"
    */
    private function bugfixCantBuildAdminMenus() {
        $db = JFactory::getDbo();

        // If there are multiple #__extensions record, keep one of them
        $query = $db->getQuery(true)
            ->select($db->qn('extension_id'))
            ->from('#__extensions')
            ->where($db->qn('element') . ' = ' . $db->q('com_focontentuploader'));
        $db->setQuery($query);
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids = $db->loadColumn();
        } else {
            $ids = $db->loadResultArray();
        }
        if (count($ids) > 1) {
            asort($ids);
            $extension_id = array_shift($ids); // Keep the oldest id

            foreach ($ids as $id) {
                $query = $db->getQuery(true);
                $query->delete('#__extensions')
                ->where($db->qn('extension_id') . ' = ' . $db->q($id));
                $db->setQuery($query);
                $db->execute();
            }
        }

        // If there are multiple assets records, delete all except the oldest one
        $query = $db->getQuery(true);
        $query->select('id')
        ->from('#__assets')
        ->where($db->qn('name') . ' = ' . $db->q('com_focontentuploader'));
        $db->setQuery($query);
        $ids = $db->loadObjectList();
        if (count($ids) > 1) {
            asort($ids);
            $asset_id = array_shift($ids); // Keep the oldest id

            foreach ($ids as $id) {
                $query = $db->getQuery(true)
                    ->delete('#__assets')
                    ->where($db->qn('id') . ' = ' . $db->q($id));
                $db->setQuery($query);
                $db->execute();
            }
        }

        // Remove #__menu records for good measure!
        $query = $db->getQuery(true)
            ->select($db->qn('id'))
            ->from($db->qn('#__menu'))
            ->where($db->qn('type') . ' = ' . $db->q('component'))
            ->where($db->qn('menutype') . ' = ' . $db->q('main'))
            ->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=com_focontentuploader'));
        $db->setQuery($query);
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids1 = $db->loadColumn();
        } else {
            $ids1 = $db->loadResultArray();
        }
        if (empty($ids1)) {
            $ids1 = array();
        }
        $query = $db->getQuery(true)
            ->select($db->qn('id'))
            ->from($db->qn('#__menu'))
            ->where($db->qn('type') . ' = ' . $db->q('component'))
            ->where($db->qn('menutype') . ' = ' . $db->q('main'))
            ->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=com_focontentuploader&%'));
        $db->setQuery($query);
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $ids2 = $db->loadColumn();
        } else {
            $ids2 = $db->loadResultArray();
        }
        if (empty($ids2)) {
            $ids2 = array();
        }
        $ids = array_merge($ids1, $ids2);
        if (!empty($ids)) {
            foreach($ids as $id) {
                $query = $db->getQuery(true);
                $query->delete('#__menu')
                ->where($db->qn('id') . ' = ' . $db->q($id));
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
    * When you are upgrading from an old version of the component or when your
    * site is upgraded from Joomla! 1.5 there is no "schema version" for our
    * component's tables. As a result Joomla! doesn't run the database queries
    * and you get a broken installation.
    *
    * This method detects this situation, forces a fake schema version "0.0.1"
    * and lets the crufty mess Joomla!'s extensions installer is to bloody work
    * as anyone would have expected it to do!
    */
    private function fixSchemaVersion() {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->qn('extension_id'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('element') . ' = ' . $db->q('com_focontentuploader'));
        $db->setQuery($query);
        $eid = $db->loadResult();
        if (!$eid) {
            return;
        }

        $query = $db->getQuery(true)
            ->select($db->qn('version_id'))
            ->from($db->qn('#__schemas'))
            ->where($db->qn('extension_id') . ' = ' . $db->q($eid));
        $db->setQuery($query);
        $version = $db->loadResult();

        if (!$version) {
            // No schema version found. Fix it.
            $o = (object)array(
                'version_id'   => '0.0.1-2007-08-15',
                'extension_id' => $eid,
            );
            $db->insertObject('#__schemas', $o);
        }
    }
}
