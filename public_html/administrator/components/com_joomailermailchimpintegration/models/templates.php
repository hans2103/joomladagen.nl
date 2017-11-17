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

class joomailermailchimpintegrationModelTemplates extends jmModel {

    public function getTemplateFolders() {
        jimport('joomla.filesystem.folder');
        return JFolder::listFolderTree(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/' , '', 1);
    }

    public function getPalettes($hex = false, $keywords = false) {
        if (strlen($hex) != 6) {
            $hex = false;
        }
        $runs = ($hex || $keywords) ? 1 : 3;
        $colors = array();
        for ($i = 0; $i < $runs; $i++) {
            $curl = curl_init();
            if (!$hex && !$keywords){
                $url = 'http://www.colourlovers.com/api/palettes/random?format=json';
            } else {
                $url = 'http://www.colourlovers.com/api/palettes?format=json';
                if ($hex) {
                    $url .= '&hex=' . $hex;
                }
                if ($keywords){
                    $url .= '&keywords=' . $keywords;
                }
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER,false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $json = curl_exec($curl);
            if (!$hex && !$keywords) {
                $colors[] = json_decode($json);
            } else {
                $result = json_decode($json);
                for ($i = 0; $i < count($result); $i++) {
                    $colors[] = array($result[$i]);
                }
            }
            curl_close($curl);
        }

        return $colors;
    }

    /**
     * Create a copy of a template in the tmp folder (for editing the template subsequently)
     *
     * @param $template
     */
    public function createTmpCopy($templateDir) {
        $filename = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/'
            . $templateDir . '/template.html';
        $template = file_get_contents($filename);
        if (!$template) {
            return false;
        }

        $src = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/' . $templateDir;
        $dest = JPATH_SITE . '/tmp/' . $templateDir . '/';
        JFolder::create($dest, 0777);
        JFolder::copy($src, $dest, '', true);

        $imagepath = '$1="' . JURI::base() . 'components/com_joomailermailchimpintegration/templates/'
            . $templateDir . '/$2$3';
        $template = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $template);
        // prevent preview from being cached
        $metaDataArray = array('<meta http-Equiv="Cache-Control" Content="no-cache">',
                               '<meta http-Equiv="Pragma" Content="no-cache">',
                               '<meta http-Equiv="Expires" Content="0">');
        $template = str_ireplace($metaDataArray, '', $template);

        $metaData = '<meta http-Equiv="Cache-Control" Content="no-cache"><meta http-Equiv="Pragma" Content="no-cache"><meta http-Equiv="Expires" Content="0">';
        if (!stristr($template, '<head>')) {
            $template = str_ireplace('<html>', '<html><head>' . $metaData . '</head>', $template);
        } else {
            $template = str_ireplace('</head>', $metaData . '</head>', $template);
        }

        // update URLs in the template to point to tmp folder
        $templatesPath = JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates';
        $template = str_replace($templatesPath, JURI::root() . 'tmp', $template);

        // write the file (delete first if it already exists)
        $tmpFile = JPATH_SITE . '/tmp/' . $templateDir . '/template.html';
        if (JFile::exists($tmpFile)){
            JFile::delete($tmpFile);
        }
        JFile::write($tmpFile, $template);
    }
}
