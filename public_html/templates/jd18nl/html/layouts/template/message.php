<?php
defined('JPATH_BASE') or die;


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

