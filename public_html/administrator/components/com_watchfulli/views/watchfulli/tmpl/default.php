<?php
/**
 * @version     backend/views/watchfulli/tmpl/default.php 2014-03-31 16:35:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;
?>
<h3><?php echo JText::_('COM_WATCHFULLI_AUTHENTICATION'); ?></h3>
<p>    
    <?php echo JText::_('COM_WATCHFULLI_BEFORE_ADDSITE_FORM'); ?>
    <form action="https://app.watchful.li/index.php" method="post" target="_blank">
        <input type="hidden" name="name" value="<?php echo $this->sitename ?>">
        <input type="hidden" name="access_url" value="<?php echo JURI::root() ?>">
        <input type="hidden" name="secret_word"value="<?php echo $this->secret_key ?>">
        <input type="hidden" name="word_akeeba" value="<?php echo $this->akeeba_secret_key ?>">
        <input type="hidden" name="option" value="com_jmonitoring">
        <input type="hidden" name="task" value="save">
        <input type="hidden" name="controller" value="editsite">
        <input type="hidden" name="view" value="editsite">
        <input type="hidden" name="source" value="client">
        <input style="<?php echo $this->style ?>" type="submit" value="<?php echo JText::_('COM_WATCHFULLI_ADDSITE') ?>" class="btn btn-primary">
    </form>
</p>

<p>
    <?php echo JText::_('COM_WATCHFULLI_SECRET_KEY'); ?>:
    <input readonly="readonly" type="text" style="width:250px;" size="55" value="<?php echo $this->secret_key ?>" />
</p>

<div>
    <p><?php echo JText::_('COM_WATCHFULLI_WHITELIST_WATCHIP_INTRO'); ?></p>
    <a href="<?php echo JRoute::_('index.php?option=com_watchfulli&task=whitelist'); ?>" class="btn">
        <?php echo JText::_('COM_WATCHFULLI_WHITELIST_WATCHIP_BTN'); ?>
    </a>
</div>
<?php if($this->debug_mode && file_exists($this->log_file)): ?>
    <hr/>
    <h3>Debug info</h3>
    <pre>
        <?php echo file_get_contents($this->log_file); ?>
    </pre>
<?php endif ?>