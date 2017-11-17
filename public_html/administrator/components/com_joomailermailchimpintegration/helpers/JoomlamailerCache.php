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
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.cache.cache');

class JoomlamailerCache {
    private $_root;
    private $_cache;
    private $_hash;

    public function __construct($options = array()) {
        $this->_root = JPATH_ADMINISTRATOR . '/cache';
        $this->_hash = JFactory::getConfig()->get('secret');
        $cache = new JCacheStorage();
        $this->_cache = $cache->getInstance('file', array('cachebase' => $this->_root));
    }

    public function getCreationTime($id, $group) {
        $path = $this->_getFilePath($id, $group);
        if (file_exists($path)) {
            $modified = filemtime($path);
        } else {
            $modified = false;
        }

        return $modified;
    }

    protected function _getCacheId($id, $group) {
        $name = md5($this->_cache->_application . '-' . $id . '-' . $this->_cache->_language);
        return $this->_cache->_hash . '-cache-' . $group . '-' . $name;
    }

    protected function _getFilePath($id, $group) {
        $name	= $this->_getCacheId($id, $group);
        $dir	= $this->_root . '/' . $group;

        if (!is_dir($dir)) {
            return false;
        }
        return $dir . '/' . $name . '.php';
    }
}
