<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');
jimport('joomla.registry.registry');

class  plgSystemSppagebuilderproupdater extends JPlugin
{

    public function onExtensionAfterSave($option, $data) {

        if ( ($option == 'com_config.component') && ( $data->element == 'com_sppagebuilder' ) ) {

            $params = new JRegistry;
            $params->loadString($data->params);
            
            $email       = $params->get('joomshaper_email');
            $license_key = $params->get('joomshaper_license_key');

            if(!empty($email) and !empty($license_key) )
            {

                $extra_query = 'joomshaper_email=' . urlencode($email);
                $extra_query .='&amp;joomshaper_license_key=' . urlencode($license_key);

                $db = JFactory::getDbo();
                
                $fields = array(
                    $db->quoteName('extra_query') . '=' . $db->quote($extra_query),
                    $db->quoteName('last_check_timestamp') . '=0'
                );

                // Update site
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__update_sites'))
                    ->set($fields)
                    ->where($db->quoteName('name').'='.$db->quote('SP Page Builder'));
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

}