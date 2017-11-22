<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

$title       = $this->escape($this->params->get('page_heading'));

echo JLayouts::render('template.content.header', array('title' => $title));
?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item">
          <?php echo RSFormProHelper::displayForm($this->formId); ?>
        </div>
    </div>
</section>