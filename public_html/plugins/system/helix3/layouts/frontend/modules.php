<?php
/**
 * @package     Helix
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted Access');

//helper & model
$menu_class   = JPATH_ROOT . '/plugins/system/helix3/core/classes/helix3.php';

if (file_exists($menu_class)) {
    require_once($menu_class);
}

$data = $displayData;

$output ='';

    $output .= '<div id="sp-' . JFilterOutput::stringURLSafe($data->settings->name) . '" class="' . $data->className . '">';

        $output .= '<div class="sp-column ' . ($data->settings->custom_class) . '">';

            $load_pos ='';
            // get feature load possition
            if (helix3::hasFeature($data->settings->name)) {
                $load_pos = helix3::getInstance()->loadFeature[$data->settings->name];
                $load_pos = $load_pos['load_pos'];
            }

            // if feature load possition before
            if(isset($load_pos) && $load_pos == 'before'){
                $output .= '<jdoc:include type="modules" name="' . $data->settings->name . '" style="sp_xhtml" />';
            }
            // if feature position is blank
            if ( $load_pos != 'before' &&  $load_pos != 'after' ) {
                $output .= '<jdoc:include type="modules" name="' . $data->settings->name . '" style="sp_xhtml" />';
            }

            if (Helix3::hasFeature($data->settings->name))
            {
                $features = helix3::getInstance()->loadFeature[$data->settings->name]; //Feature

                foreach ($features as $key => $feature){
                    if ($key == 'feature') {
                        $output .= $feature;
                    }
                }
            }

            // if feature load possition before
            if(isset($load_pos) && $load_pos == 'after'){
                $output .= '<jdoc:include type="modules" name="' . $data->settings->name . '" style="sp_xhtml" />';
            }
        
        $output .= '</div>'; //.sp-column

    $output .= '</div>'; //.sp-


echo $output;

