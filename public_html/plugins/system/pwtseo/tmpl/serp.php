<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;
?>

<div class="pseo-serp-wrapper">
    <h2 class="pseo-heading">Google search result preview:</h2>
    <div class="pseo-serp">
        <div class="pseo-serp__title js-serp-title">{{ page.pagetitle | truncate({length: 70}) }}</div>
        <div class="pseo-serp__url js-serp-ext-url">{{ page.url }}</div>
        <div class="pseo-serp__description js-serp-description">{{ page.metadesc | truncate({length: 150}) }}</div>
    </div>
</div>