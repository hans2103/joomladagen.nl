<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
/**
 * @package		jlike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

jimport ( 'joomla.application.component.model' );

class JLikeModelSettings extends JModelLegacy {
	protected $_id = null;
	protected $_data = null;

	function __construct() {
		parent::__construct ();

		$array = JRequest::getVar ( 'cid', 0, '', 'array' );
		$this->setId ( ( int ) $array [0] );
	}

	function setId($id) {
		$this->_id = $id;
		$this->_data = null;
	}

	function getData() {
		// Load the data
		if (empty ( $this->_data )) {
			$query = $this->_buildQuery ();
			$this->_data = $this->_getList ( $query );
		}

		if (! $this->_data) {
			$this->_data = new stdClass ();
			$this->_data->id = 0;
		}
		return $this->_data;
	}

	function _buildQuery() {
		$query = ' SELECT * FROM #__jlike ';
		return $query;
	}

	function store() {

		$post=JRequest::get('post');
    if($post){
		$db=JFactory::getDBO();
		$query="SELECT namekey from `#__jlike_config`";
	  $db->setQuery($query);
	  $config_rows=$db->loadResultArray();

		$jlike_config=JRequest::getVar('config', '', 'post', 'string', JREQUEST_ALLOWRAW);
		foreach($jlike_config as $k=>$v)
		{

				if(is_array($v))
					 $v=implode(',',$v);
				$c_data = new stdClass;
				$c_data->namekey 	=	$k;
				$c_data->value= $v;
				if(!in_array($k,$config_rows))
				{
						//$inv_config[$k]			= $v;
						$db->insertObject( '#__jlike_config', $c_data, 'id' );
				}
				else 
				{
						$query="SELECT id from `#__jlike_config` where namekey='".$k."'";
						$db->setQuery($query);
     			  $c_data->id 	=	$db->loadResult();
						$db->updateObject( '#__jlike_config', $c_data, 'id' );

				}
		}
			 return true;

		}
		else
			return false;
	}

	function delete() {
		$id = JRequest::getInt ( 'published', 0 );
		$row = $this->getTable ();

		if (! $row->delete ( $id )) {
			$this->setError ( $row->getErrorMsg () );
			return false;
		}

		return true;
	}

}
