<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die();
$input = JFactory::getApplication()->input;

// LOAD LANGUAGE FILES
$doc = JFactory::getDocument();
$lang = JFactory::getLanguage();
$lang->load('mod_tjfields_search', JPATH_SITE);
$currentComponent = $input->get("option");

// GETTING MODULE PARAMS
$url_cat_param_name       = $params->get('url_cat_param_name', '');
$client_type               = $params->get('client_type', '');
$category_type               = $params->get('category_type', '');
$URLParamConditions               = $params->get('URLParamConditions', '');
$bootstrapVersion = $params->get('bootstrapversion', 'bs3', 'STRING');
$displayLayout = $params->get('layout', 'vertical', 'STRING');
$URLParamConditions = trim($URLParamConditions);

$tmp = explode(".", $client_type);
$configuredComp = $tmp[0];

// Check the Bootstrap here
$mod_tjfilter_layout = $params->get('bootstrapversion');

if (JFile::exists(JPATH_SITE . '/components/com_tjfields/tjfields.php'))
{
	$path = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

	if (!class_exists('tjfieldsHelper'))
	{
		JLoader::register('tjfieldsHelper', $path);
		JLoader::load('tjfieldsHelper');
	}

	$tjfieldsHelper = new tjfieldsHelper;

	if (!empty($URLParamConditions))
	{
		parse_str($URLParamConditions, $conditionList);

		$url = JFactory::getApplication()->input->server->get('REQUEST_URI', '', 'STRING');

		// Get uRL base part and parameter part
		$temp = explode('?', $url);
		$urlArray = array();

		if (!empty($temp[1]))
		{
			parse_str($temp[1], $urlArray);
		}

		$urlArray['option'] = $input->get("option");
		$urlArray['view'] = $input->get("view");
		$urlArray['layout'] = $input->get("layout");
		$showHtml = 1;

		if (!empty($conditionList))
		{
			foreach ($conditionList as $urlParam => $urlValue)
			{
				if (empty($urlArray[$urlParam]) || $urlArray[$urlParam] != $urlValue)
				{
					$showHtml = 0;
					break;
				}
			}
		}

		if ($showHtml == 0)
		{
			return '';
		}
	}

	// Get comma seperated parameters to removed on change of category
	$removeParamOnchangeCat = '';
	$compSpecificFilterHtml = '';

	switch ($configuredComp)
	{
		case 'com_quick2cart':
				require_once JPATH_SITE . '/components/com_quick2cart/helper.php';
				$comquick2cartHelper = new Comquick2cartHelper;
				$removeParamOnchangeCat = $comquick2cartHelper->getParameterToRemoveOnChangeOfCategory();

		break;
	}

	if ($displayLayout == 'horizontal')
	{
		$options         = array();
		$options[]       = JHtml::_('select.option', '', JText::_('MOD_TJFIELDS_SEARCH_SELECT_ALL'));

		if ($bootstrapVersion == 'bs2')
		{
			$basePath = JPATH_SITE . '/components/' . $category_type . '/layouts/corefilters/horizontal/bs2';
		}
		else
		{
			$basePath = JPATH_SITE . '/components/' . $category_type . '/layouts/corefilters/horizontal/bs3';
		}
	}
	else
	{
		$options         = array();
		$options[]       = JHtml::_('select.option', '', JText::_('MOD_TJFIELDS_SEARCH_SELECT_CATEGORY'));

		if ($bootstrapVersion == 'bs2')
		{
			$basePath = JPATH_SITE . '/components/' . $category_type . '/layouts/corefilters/vertical/bs2';
		}
		else
		{
			$basePath = JPATH_SITE . '/components/' . $category_type . '/layouts/corefilters/vertical/bs3';
		}
	}

	$cats = JHtml::_('category.options', $category_type, array('filter.published' => array(1)));
	$fieldsCategorys               = array_merge($options, $cats);

	$layout = new JLayoutFile('corefilters', $basePath);

	$data = "";
	$compSpecificFilterHtml = $layout->render($data);

	// Selected category
	$clientCatUrlParam = $params->get("url_cat_param_name", "prod_cat");
	$selectedCategory = $input->get($clientCatUrlParam, '');

	$fieldsArray = array();

	// Universal field- for client - those field who doesn't mapped agaist category
	// $fieldsArray['universal'] = $tjfieldsHelper->getUniversalFields($client_type);

	// Get client categorySpecific fields
	$rawtjFilterableData = $tjfieldsHelper->getFilterableFields($client_type, $selectedCategory);

	// Formate field detail. Make the array index as field id and add the option in the field index
	if (!empty($rawtjFilterableData))
	{
		foreach ($rawtjFilterableData as $field)
		{
			if (array_key_exists($field->id, $fieldsArray))
			{
				$fieldsArray[$field->id][] = $field;
			}
			else
			{
				$fieldsArray[$field->id] = array();
				$fieldsArray[$field->id][] = $field;
			}
		}
	}

	require JModuleHelper::getLayoutPath('mod_tjfields_search', 'default_' . $mod_tjfilter_layout . '_' . $displayLayout);
}
