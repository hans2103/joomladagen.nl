<?php
defined('_JEXEC') or die;

function modChrome_tpl($module, &$params, &$attribs)
{

    $moduleTag = $params->get('module_tag', 'div');
    $headerTag = htmlspecialchars($params->get('header_tag', 'h3'));
    $moduleClass = '';

    if ( isset($attribs['class'])) {
        $moduleClass = $attribs['class'] . ' ';
    }

    if ($module->content)
    {
        echo '<' . $moduleTag . ' class="' . $moduleClass . htmlspecialchars($params->get('moduleclass_sfx')) . ' module">';

        if ($module->showtitle)
        {
            echo '<div class="module__header"><' . $headerTag . '>' . $module->title . '</' . $headerTag . '></div>';
        }

        echo '<div class="module__content">' . $module->content . '</div>';

        echo '</' . $moduleTag . '>';
    }
}