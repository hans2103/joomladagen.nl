<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$title       = $this->escape($this->params->get('page_heading'));
$description = null;
echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description));

?>
<section class="section__wrapper">
    <div class="container container--shift">
        <div class="content content--small content__form content__form--registration-complete">
            <p>Registratie verzonden</p>
        </div>
    </div>
</section>
