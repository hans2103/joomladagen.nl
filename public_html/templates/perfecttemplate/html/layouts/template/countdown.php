<?php
$date      = JFactory::getDate();
$edate     = new DateTime('2017-03-01');
$remain    = $edate->diff($date);
$imgTitle  = '';
$imgNumber = '';

if ($remain->days >= 6 && $remain->days <= 13)
{
	$imgTitle  = 'weeks';
	$imgNumber = ceil($remain->days / 7);
}

if ($remain->days >= 0 && $remain->days <= 5)
{
	$imgTitle  = 'days';
	$imgNumber = $remain->days;
}

//if (isset($imgTitle) && !empty($imgTitle))
if(false)
{
	?>
	<div class="block__countdown">
		<div class="block__countdown--container">
			<div class="block__countdown--content">
				<?php $img = JHtml::_('image', 'images/countdown/' . $imgTitle . '-' . $imgNumber . '.png', 'Geniet nu nog van de Early Bird'); ?>
				<?php echo JHtml::_('link', 'https://shop.joomladagen.nl', $img, array()); ?>
			</div>
		</div>
	</div>
<?php } ?>