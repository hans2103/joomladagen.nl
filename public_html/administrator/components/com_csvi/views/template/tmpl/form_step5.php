<?php
/**
 * @package     CSVI
 * @subpackage  Template
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

if ($this->action === 'export' && count($this->fields) === 0)
{
	echo '<span class="error">' . JText::_('COM_CSVI_WIZARD_EXPORT_FINALIZE_NO_FIELDS') . '</span>';
}
else
{
	echo JText::_('COM_CSVI_WIZARD_' . $this->action . '_FINALIZE');
}
