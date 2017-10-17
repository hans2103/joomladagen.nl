<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

class TableJLike_likes extends JTable {
	var $id = null;

	function __construct(&$db) {
		parent::__construct ( '#__jlike_likes', 'id', $db );

		//Hack to make this act a little like active record
		$this->_db->setQuery ( 'SHOW columns FROM ' . $this->_tbl );
		foreach ( $this->_db->loadObjectList () as $k => $column ) {
			$field = $column->Field;
			$this->$field = '';
		}
	}

	public function bind($from) {
		$from = json_decode ( $from );
		parent::bind ( $from );
	}

	public function store($updateNulls = false) {

		foreach ( get_object_vars ( $this ) as $k => $v ) {

			if (is_array ( $v ) or is_object ( $v ) or $k [0] == '_') { // internal or NA field
				continue;
			}
			$set [] = $this->_db->nameQuote ( $k ) . '=' . $this->_db->Quote ( $v );
		}

		$sql = 'REPLACE INTO ' . $this->_tbl . ' SET ' . implode ( ',', $set );

		$this->_db->setQuery ( $sql );

		if ($this->_db->query ()) {
			return true;
		} else {
			$this->setError ( get_class ( $this ) . '::store failed - ' . $this->_db->getErrorMsg () );
			return false;
		}
	}
}
