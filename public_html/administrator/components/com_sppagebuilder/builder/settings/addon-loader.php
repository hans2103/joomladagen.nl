<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class AddonLoader {
	protected $fileds_type = array();
	protected $addon_name = '';
	protected $addon_path = '';
	protected $icon_path = '';
	protected $settings = array();
	protected $action_role = '';

	public function __construct( $addon_name, $addon_settings, $action_role )
	{
		$this->addon_name 	= $addon_name;
		$this->settings 	= $addon_settings;
		$this->action_role 	= $action_role;

		$this->getAddonPath( $addon_name );
		$this->getAddon();
		$this->getFieldsTypes();
	}

	public function getAddonHtml()
	{
		$fileds = $this->fileds_type;
		$addon 	= $this->addon_data;

		if (!is_array($fileds)) return;
		if (!is_array($addon)) return;

		$name = $this->addon_name;
		
		foreach ( $fileds as $key => $filed ) {
			include_once JPATH_COMPONENT.'/builder/types/'.$filed.'.php';
		}

		$atts = $addon[$name]['attr'];
		$title = $addon[$name]['title'];

		if( isset( $this->settings['atts']['admin_label'] ) ){
			$admin_label = $atts['admin_label']['std'];
		} else if ( isset($atts['admin_label']['std']) && $atts['admin_label']['std'] ) {
			$admin_label = $atts['admin_label']['std'];
		} else {
			$admin_label = '';
		}

		$output  = '';
		$output .= '<div class="generated" data-element_type="add_ons" data-name="'.substr( $name,3 ).'" data-addon_title="'. $title .'" data-addon_type="'.$addon[$name]['type'].'">';
		$output .= '<div class="generated-item clearfix">';
		$output .= '<img class="item-image" src="' . $this->icon_path . '" alt="' . $title . '" width="24" />';
		$output .= '<h3>' . $title . '</h3>';

		$output .= '<div class="action">';
		$output .= '<a class="addon-edit" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>';
		$output .= '<a class="addon-duplicate" href="javascript:void(0)"><i class="fa fa-repeat"></i></a>';
		$output .= '<a class="remove-addon" href="javascript:void(0)"><i class="fa fa-times"></i></a>';
		$output .= '</div>';

		$output .= '<p class="addon-input-title">' . ( ( $admin_label ) ? $admin_label : '') . '</p>';

		$output .= '</div>';
		$output .= '</div>';
		

		$output .= '<div class="item-inner" data-addon_name="'. $this->addon_name .'">';

		if( !empty( $atts ) )
		{
			foreach( $atts as $fname => $addon_attr )
			{

				if( $addon_attr['type'] == 'repeatable' )
				{
					$rep_addon_name = '';

					if (isset($atts['repetable_item']['addon_name'])) {
						$rep_addon_name = $atts['repetable_item']['addon_name'];
					}

					$output .='<div class="repeatable-items">';
					$output .= '<a href="javascript:void(0)" class="clone-repeatable sppb-btn sppb-btn-primary"><i class="fa fa-plus"></i> ' . JText::_('COM_SPPAGEBUILDER_ADD_NEW') . '</a>';
					$output .='<div class="accordion">';

					if ( $this->action_role === 'addnew' )
					{
						$output .= $this->accordionHeadTpl( $rep_addon_name, $addon_attr['title'] ); // ACCORDION HEADER TEMPLATE

						foreach( $addon_attr['attr'] as $key => $attr )
						{
							$class_name = 'SpType' . ucfirst( $attr['type'] );
							$output .= $class_name::getInput($key,$attr);
						}

						$output .= $this->accordionFooterTpl(); // ACCORDION FOOTER TEMPLATE
					}
					else if( $this->action_role === 'edit' )
					{
						$recontent = $this->settings['scontent'];
						foreach ($recontent as $key => $row)
						{
							$output .= $this->accordionHeadTpl($rep_addon_name, $row['atts']['title']); // ACCORDION HEADER TEMPLATE
							foreach ($row['atts'] as $key => $val)
							{
								$newAttr = $addon_attr['attr'][$key];
								$class_name = 'SpType' . ucfirst( $newAttr['type'] );
								$newAttr['std'] = $val;

								$output .= $class_name::getInput( $key, $newAttr );
							}

							$output .= $this->accordionFooterTpl(); // ACCORDION FOOTER TEMPLATE
						}
					}

					$output .='</div><!--/.accordion-->';
					$output .='</div><!--/.repeatable-items-->';

				}
				else
				{
					if (isset($this->settings['atts'][$fname])) {
						$addon_attr['std'] = $this->settings['atts'][$fname];
					}

					$class_name = 'SpType' . ucfirst( $addon_attr['type'] );
					$output .= $class_name::getInput( $fname, $addon_attr );
				}
			}
		}

		$output .= '</div>';

		return $output;

	}

	protected function getAddonPath()
	{
		$addon = substr($this->addon_name,3);
		$template_name = $this->getTemplateName();
		$template_path = JPATH_ROOT . '/templates/' . $template_name; // current template path

		$tmpl_file_path = $template_path . '/sppagebuilder/addons/'. $addon .'/admin.php';
		$com_file_path 	= JPATH_COMPONENT_SITE . '/addons/'. $addon .'/admin.php';
		
		if ( file_exists( $tmpl_file_path ) ) {
			$this->addon_path = $tmpl_file_path;
			$this->icon_path  = JURI::root(true) . '/templates/' . $template_name . '/sppagebuilder/addons/' . $addon . '/icon.png';
		} else if ( file_exists( $com_file_path ) ) {
			$this->addon_path = $com_file_path;
			$this->icon_path  = JURI::root(true) . '/components/com_sppagebuilder/addons/' . $addon . '/icon.png';
		}

		if( !file_exists( $this->icon_path ) ){
			$this->icon_path = JURI::root(true) . '/administrator/components/com_sppagebuilder/assets/img/addon-default.png';
		}
	}

	private function getAddon()
	{

		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $this->getTemplateName(), JPATH_SITE, $lang->getName(), true);

		if (!$this->addon_path) return;
		include $this->addon_path;
		$this->addon_data = SpAddonsConfig::$addons;
	}

	private function getFieldsTypes()
	{
		if(!is_array($this->addon_data)) return;

		$name = $this->addon_name;
		$fileds = $this->addon_data[$name]['attr'];

		$types = array();
		foreach ($fileds as $key => $filed) {
			if ($key === 'repetable_item')
			{
				$refields = $filed['attr'];
				foreach ($refields as $rekey => $refield) {
					$types[] = $refield['type'];
				}
			}
			else
			{
				$types[] = $filed['type'];
			}
		}

		$this->fileds_type = array_unique($types);
	}

	private function getTemplateName()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('template')));
		$query->from($db->quoteName('#__template_styles'));
		$query->where($db->quoteName('client_id') . ' = 0');
		$query->where($db->quoteName('home') . ' = 1');
		$db->setQuery($query);

		return $db->loadObject()->template;
	}

	private function accordionHeadTpl( $rep_addon_name = '', $title = '' )
	{
		$output ='<div class="accordion-group" data-inner_base="'.$rep_addon_name.'">';
		$output .= '<div class="accordion-heading">';
		$output .= '<a href="javascript:void(0)" class="action-move"><i class="fa fa-ellipsis-v"></i></a>';
		$output .= '<a class="accordion-toggle" data-toggle="collapse"><span>' . $title . '</span></a>';
		$output .= '<a href="javascript:void(0)" class="action-remove"><i class="fa fa-times"></i></a>';
		$output .= '<a href="javascript:void(0)" class="action-duplicate"><i class="fa fa-copy"></i></a>';
		$output .= '</div>';
		$output .='<div class="accordion-body collapse">';
		$output .='<div class="accordion-inner">';

		return $output;
	}

	private function accordionFooterTpl()
	{
		$output ='</div>';
		$output .='</div>';
		$output .='</div>';

		return $output;
	}
}