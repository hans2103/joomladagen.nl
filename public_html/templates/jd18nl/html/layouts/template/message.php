<?php
defined('JPATH_BASE') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$messageQueue = JFactory::getApplication()->getMessageQueue();

foreach ($messageQueue as $message)
{
	?>
    <div class="message__wrapper message__wrapper--<?php echo $message['type']; ?>">
        <div class="container container--shift">
            <div class="content content--small">
				<?php echo $message['message']; ?>
            </div>
        </div>
    </div>
	<?php
}

if (PWTTemplateHelper::getPageOption() == 'com-j2store')
{
	?>
    <div class="message__wrapper message__wrapper--none">
        <div class="container container--shift">
            <div class="content content--small">

            </div>
        </div>
    </div>
<?php }