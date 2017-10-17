<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die;
?>

<div class="col-lg-12 col-md-12 pull-right">
	<?php
	$versionHTML = '<span class="label label-info pull-right">' . JText::_('COM_JTICKETING_HAVE_INSTALLED_VER') . ': ' . $this->version . '</span>';

	if ($this->latestVersion)
	{
		if ($this->latestVersion->version > $this->version)
		{
			$versionHTML = '<div class="alert alert-error">' .
								'<i class="icon-puzzle install"></i>' .
								JText::_('COM_JTICKETING_HAVE_INSTALLED_VER') . ': ' . $this->version .
								'<br/>' .
								'<i class="icon icon-info"></i>' .
								JText::_("COM_JTICKETING_NEW_VER_AVAIL") . ': ' .
								'<span class="jticketing_latest_version_number">' .
									$this->latestVersion->version .
								'</span>
								<br/>' .
								'<i class="icon icon-warning"></i>' .
								'<span class="small">' .
									JText::_("COM_JTICKETING_LIVE_UPDATE_BACKUP_WARNING") . '
								</span>' . '
							</div>
							<div class="left">
								<a href="index.php?option=com_installer&view=update" class="btn btn-small btn-primary">' .
									JText::sprintf('COM_JTICKETING_LIVE_UPDATE_TEXT', $this->latestVersion->version) . '
								</a>
								<a href="' . $this->latestVersion->infourl . '/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=invitex&utm_content=updatedetailslink&utm_campaign=jticketing_ci' . '" target="_blank" class="btn btn-small btn-info">' .
									JText::_('COM_JTICKETING_LIVE_UPDATE_KNOW_MORE') . '
								</a>
							</div>';
		}
	}
	?>
	<?php echo $versionHTML; ?>
</div>

<div class="clearfix">&nbsp;</div>
