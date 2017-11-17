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
*
* This file is based on AdminTools' update.php from Nicholas K. Dionysopoulos
* @copyright Copyright (c)2010 Nicholas K. Dionysopoulos
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

class joomailermailchimpintegrationControllerUpdate extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function update() {
        // Make sure there are updates available
        $updates = $this->getModel('update')->getUpdates(false);
        if (!$updates->update_available) {
            $this->app->enqueueMessage(JText::_('JM_ERR_UPDATE_NOUPDATES'), 'error');
            $this->app->redirect(JURI::base() . 'index.php?option=com_joomailermailchimpintegration');
        }

        // Download the package
        $config = JFactory::getConfig();
        $target = $config->get('tmp_path') . '/joomailermailchimpintegration_update.zip';
        $result = $this->getModel('update')->downloadPackage($updates->packageUrl, $target);

        if ($result === false) {
            $this->app->enqueueMessage(JText::_('JM_ERR_UPDATE_CANTDOWNLOAD'), 'error');
            $this->app->redirect(JURI::base() . 'index.php?option=com_joomailermailchimpintegration');
        }

        // Extract the package
        jimport('joomla.installer.helper');
        $result = JInstallerHelper::unpack($target);

        if ($result === false) {
            $this->app->enqueueMessage(JText::_('JM_ERR_UPDATE_CANTEXTRACT'), 'error');
            $this->app->redirect(JURI::base() . 'index.php?option=com_joomailermailchimpintegration');
        }

        // Package extracted; run the installer
        $tempdir = $result['dir'];
        @ob_end_clean(); ?>
        <html>
            <head></head>
            <body>
                <form action="<?php echo JURI::base().'index.php?option=com_installer&amp;view=install';?>" method="post" name="frm" id="frm">
                    <input type="hidden" name="task" value="install.install" />
                    <input type="hidden" name="installtype" value="folder" />
                    <input type="hidden" name="install_directory" value="<?php echo htmlspecialchars($tempdir) ?>" />
                    <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
                </form>
                <script type="text/javascript">
                    document.frm.submit();
                </script>
            </body>
        <html><?php
        exit;
    }
}
