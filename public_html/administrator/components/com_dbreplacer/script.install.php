<?php
/**
 * @package         DB Replacer
 * @version         5.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class Com_DBReplacerInstallerScript extends Com_DBReplacerInstallerScriptHelper
{
	public $name           = 'DB_REPLACER';
	public $alias          = 'dbreplacer';
	public $extension_type = 'component';

	public function onAfterInstall()
	{
		$this->deleteOldFiles();
	}

	private function deleteOldFiles()
	{
		$this->deleteFolders(
			array(
				JPATH_SITE . '/components/com_dbreplacer',
			)
		);
	}
}
