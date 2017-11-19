<?php
/**
 * @package    NPO Radio 4
 *
 * @author     Perfect Web Team <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://perfectwebteam.nl
 */

defined('_JEXEC') or die;

if ($displayData['pages']) : ?>
    <div class="content pagination">
		<?php echo $displayData['pages']; ?>
    </div>
<?php endif;