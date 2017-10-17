<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

class TableJLike extends JTable {
	var $id = null;

	function __construct(&$db) {
		parent::__construct ( '#__jlike', 'id', $db );
	}
}
