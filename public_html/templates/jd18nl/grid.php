<?php
/*
 * @package     parcls
 * @copyright   Copyright (c) Perfect Web Team / perfectwebteam.nl
 * @license     GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('stylesheet', 'templates/' . PWTTemplateHelper::template() . '/css/grid.css', array('version' => 'auto'));
?>

<div class="overlay-grid-container" style="display: none;">
    <div class="overlay-grid">
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
        <div class="overlay-grid__item"></div>
    </div>
    <div class="overlay-8pixel"></div>
    <script>
        var isCtrl = false;
        document.onkeyup = function (e) {
            if (e.keyCode == 17) isCtrl = false;
        };

        document.onkeydown = function (e) {
            e = e || window.event;
            if (e.keyCode == 17) isCtrl = true;

            // Grid (G key)
            if (e.keyCode == 71 && isCtrl == true) {
                var gridContainer = document.getElementsByClassName('overlay-grid-container')[0];
                if (gridContainer.style.display == 'none') {
                    gridContainer.style.display = 'block';
                } else {
                    gridContainer.style.display = 'none';
                }
            }

            // Remove all modernizr classes
            if (e.keyCode == 77 && isCtrl == true) { // M key
                document.documentElement.className = "";
            }
        };
    </script>
</div>
