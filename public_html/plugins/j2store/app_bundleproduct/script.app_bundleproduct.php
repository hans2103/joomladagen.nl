<?php
/**
 * --------------------------------------------------------------------------------
 *  Bundle Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU GPL v3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
class plgJ2StoreApp_bundleproductInstallerScript
{
    function preflight($type, $parent)
    {
        if (!JComponentHelper::isEnabled('com_j2store')) {
            Jerror::raiseWarning(null, 'J2Store not found. Please install J2Store before installing this plugin');
            return false;
        }
        jimport('joomla.filesystem.file');
        $version_file = JPATH_ADMINISTRATOR . '/components/com_j2store/version.php';
        if (JFile::exists($version_file)) {
            require_once($version_file);
            if (version_compare(J2STORE_VERSION, '3.2.12', 'lt')) {
                Jerror::raiseWarning(null, 'You need at least J2Store version 3.2.12 for this plugin to work');
                return false;
            }
        } else {
            Jerror::raiseWarning(null, 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this plugin');
            return false;
        }
    }

    public function postflight($type, $parent){
        $this->_moveSource($parent);
    }
    /**
     * Method to move source files into
     * Products/view
     * @param object $parent
     */
    public function _moveSource($parent){
        $src = $parent->getParent()->getPath('source');
        //have to move the files in the path
        $source_path = $src.'/source/';
        if (is_dir($source_path)){
            //destination path
            $files = JFolder::files($source_path);
            $folders = JFolder::folders($source_path);
            foreach($folders as $folder){
                $current_folder = $source_path.$folder;
                if($folder == 'admin'){
                    $destination_path = JPATH_ADMINISTRATOR.'/components/com_j2store/';
                    $this->getAdminFolders($current_folder, $destination_path ,$parent );
                }
                if($folder == 'site'){
                    $destination_path = JPATH_SITE.'/components/com_j2store/';
                    $this->getSiteFolders($current_folder,$destination_path ,$parent);
                }
            }
        }
    }

    public function getAdminFolders($current_folder,$destination_path,$parent){
        $sfiles = JFolder::files($current_folder);
        $sfolders = JFolder::folders($current_folder);
        foreach($sfolders as $sfolder){
            if($sfolder == 'models') {
                $mdestination_path = $destination_path . 'models/';
                $mcurrent_folder = $current_folder . '/' . $sfolder . '/';
                $mfolders = JFolder::folders($mcurrent_folder);
                foreach($mfolders as $mfolder){
                    $mdestination_path .= $mfolder.'/';
                    $mcurrent_folder .= $mfolder.'/';
                    $bfiles = JFolder::files($mcurrent_folder);
                    foreach($bfiles as $bfile){
                        if (!JFile::move($mcurrent_folder.$bfile, $mdestination_path.$bfile) ) {
                            $parent->getParent()->abort('Could not move folder '.$mdestination_path.'Check permissions.');
                            return true;
                        }
                    }
                }

            }

            if($sfolder == 'views' ){
                $vdestination_path = $destination_path.'views/';
                $vcurrent_folder = $current_folder .'/'.$sfolder.'/';
                $mfolders = JFolder::folders($vcurrent_folder);
                foreach($mfolders as $mfolder){
                    $vmdestination_path =$vdestination_path.$mfolder.'/';
                    $vmcurrent_folder = $vcurrent_folder.$mfolder.'/';
                    $bfolders = JFolder::folders($vmcurrent_folder);
                    foreach($bfolders as $bfolder){
                        $vdestination_path =$vmdestination_path.$bfolder.'/';
                        $vcurrent_folder = $vmcurrent_folder.$bfolder.'/';
                        $bfiles = JFolder::files($vcurrent_folder);
                        foreach($bfiles as $bfile){
                            if (!JFile::move($vcurrent_folder.$bfile, $vdestination_path.$bfile) ) {
                                $parent->getParent()->abort('Could not move folder '.$vdestination_path.'Check permissions.');
                                return true;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getSiteFolders($current_folder,$destination_path ,$parent){

        $sfiles = JFolder::files($current_folder);
        $sfolders = JFolder::folders($current_folder);

        foreach($sfolders as $sfolder){
            if($sfolder =='views'){

                // make sure only product view is edited
                //if(in_array($sfolder, array('product'))){

                $vdestination_path = $destination_path.'views/';
                $vcurrent_folder = $current_folder .'/'.$sfolder.'/';
                $vfolders = JFolder::folders($vcurrent_folder);

                foreach($vfolders as $vfold){
                    $vsdestination_path =$vdestination_path .$vfold.'/';
                    $vscurrent_folder =$vcurrent_folder.$vfold.'/';
                    $vsfolders = JFolder::folders($vscurrent_folder);

                    foreach($vsfolders as $vsfold)
                        $vstdestination_path = $vsdestination_path.$vsfold.'/';
                    $vstcurrent_folder =	$vscurrent_folder.$vsfold.'/';
                    $vstfiles = JFolder::files($vstcurrent_folder);
                    foreach($vstfiles as $vfile){
                        if (!JFile::move($vstcurrent_folder.$vfile, $vstdestination_path.$vfile) ) {
                            $parent->getParent()->abort('Could not move folder '.$vstdestination_path.'Check permissions.');
                            return true;
                        }
                    }

                }
                //}
            }


            if($sfolder == 'templates' ){
                $tdestination_path = $destination_path.'templates/';
                $tcurrent_folder = $current_folder .'/'.$sfolder.'/';
                $tfolders = JFolder::folders($tcurrent_folder);

                foreach($tfolders as $tsfold){
                    $destination_folder = $tdestination_path.$tsfold.'/';
                    $tscurrent_folder = $tcurrent_folder.$tsfold.'/';
                    $tfiles = JFolder::files($tscurrent_folder);
                    foreach($tfiles as $tfile){
                        if (!JFile::move($tscurrent_folder.$tfile, $destination_folder.$tfile) ) {
                            $parent->getParent()->abort('Could not move folder '.$destination_folder.'Check permissions.');
                            return true;
                        }
                    }

                }
            }
        }
    }
}