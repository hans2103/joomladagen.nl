<?php
defined('_JEXEC') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';

class JFormFieldDiscountmethods extends JFormFieldList
{
    protected $type = 'discountmethods';

    public function getInput()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__extensions')
            ->where('folder ='.$db->q('j2store'))
            ->where('element ='.$db->q('app_discountrules'))
            ->where('type ='.$db->q('plugin'));
        $db->setQuery($query);
        $plugin = $db->loadObject();
        $vars = new stdClass();
        $vars->params = $this->getRegistryObject($plugin->params);
        $vars->list = $this->value;
        $vars->name = $this->name;
        $vars->user_groups = $this->getUserGroup();
        F0FModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models');
        $geo_model = F0FModel::getTmpInstance('Geozones','J2StoreModel');
        $geozonelist = $geo_model->enabled(1)->getItemList();
        $geozone = array();
        $geozone[''] = JText::_("J2STORE_ALL");
        foreach ($geozonelist as $list){
            $geozone[$list->j2store_geozone_id] = $list->geozone_name;
        }
        $vars->geozones = $geozone;
        $vars->discount_types = array(
          'by_one_get_one' => JText::_('J2STORE_DISCOUNT_RULE_BY_ONE_GET_ONE')
        );
        return $this->_getLayout('discount_method',$vars,'app_discountrules');
    }

    function getUserGroup(){
        $db = F0FPlatform::getInstance()->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text');
        $query->from('#__usergroups AS a');
        $query->group('a.id, a.title');
        $query->order('a.id ASC');
        $query->order($query->qn('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);
        $user_list = $db->loadObjectList();
        $options = array();
        $options[''] = JText::_('JALL');
        foreach($user_list as $user){
            $options[$user->value] = $user->text;
        }
        return $options;

    }


    public function getRegistryObject($json){
        if(!$json instanceof JRegistry) {
            $params = new JRegistry();
            try {
                $params->loadString($json);

            }catch(Exception $e) {
                $params = new JRegistry('{}');
            }
        }else{
            $params = $json;
        }
        return $params;
    }


    /**
     * Gets the parsed layout file
     *
     * @param string $layout The name of  the layout file
     * @param object $vars Variables to assign to
     * @param string $plugin The name of the plugin
     * @param string $group The plugin's group
     * @return string
     * @access protected
     */
    function _getLayout($layout, $vars = false, $plugin = '', $group = 'j2store' )
    {

        if (empty($plugin))
        {
            $plugin = $this->_element;
        }

        ob_start();
        $layout = $this->_getLayoutPath( $plugin, $group, $layout );
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;
    }


    /**
     * Get the path to a layout file
     *
     * @param   string  $plugin The name of the plugin file
     * @param   string  $group The plugin's group
     * @param   string  $layout The name of the plugin layout file
     * @return  string  The path to the plugin layout file
     * @access protected
     */
    function _getLayoutPath($plugin, $group, $layout = 'default')
    {
        $app = JFactory::getApplication();

        // get the template and default paths for the layout
        $templatePath = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/plugins/'.$group.'/'.$plugin.'/'.$layout.'.php';
        $defaultPath = JPATH_SITE.'/plugins/'.$group.'/'.$plugin.'/'.$plugin.'/tmpl/'.$layout.'.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists( $templatePath ))
        {
            return $templatePath;
        }
        else
        {
            return $defaultPath;
        }
    }

}