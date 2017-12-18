<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$showbreadcrumbs = true;
//$showbreadcrumbs = false;
if (isset($displayData['breadcrumbs']) && $displayData['breadcrumbs'] == false)
{
	$showbreadcrumbs = false;
}
?>
<header class="header<?php echo (PWTTemplateHelper::isHome() ? ' header--home' : ''); ?>" role="banner">
    <div class="container">
        <div class="header__content">
			<?php
			if ($showbreadcrumbs) :
				echo '<div class="breadcrumbs__wrapper">';
				// Module params
				$params = array(
					'showHere' => 0
				);

				// Load module and add params
				$module            = JModuleHelper::getModule('mod_breadcrumbs');
				$module->showtitle = 0;
				$module->params    = json_encode($params);

				// Render module
				echo JFactory::getDocument()->loadRenderer('module')->render($module);
				echo '</div>';
			endif;

			echo isset($displayData['create_date']) ? JLayoutHelper::render('template.content.create_date', array('date' => $displayData['create_date'], 'format' => 'DATE_FORMAT_CC1', 'class' => 'header__date')) : '';
			echo isset($displayData['title']) ? '<h1 class="header__title">' . $displayData['title'] . '</h1>' : '';
			echo (isset($displayData['intro'])  && !empty($displayData['intro']) )? '<div class="header__intro">' . $displayData['intro'] . '</div>' : '';
			?>
        </div>
    </div>
</header>

<?php echo JLayouts::render('template.message');





