<?php
defined('JPATH_BASE') or die;

$this->template = JFactory::getApplication()->getTemplate();

$img   = $displayData['img'];
$img   = str_replace(array("'", '"'), "", $img);

$alt   = $displayData['alt'];
$alt   = str_replace(array("'", '"'), "", $alt);

$array = array();
$arraynoscript = array();

$array['data-src'] = '/'.$img;

if(isset($displayData['class']))
{
	$array['class'] = $displayData['class'];
	$arraynoscript['class'] = $displayData['class'];
}

if(isset($displayData['aria-hidden']))
{
	$array['aria-hidden'] = true;
	$arraynoscript['aria-hidden'] = true;
}

?>
<div class="lazyload">
    <?php echo JHtml::_('image', 'templates/' . $this->template . '/images/spacer.gif', $alt, $array); ?>
    <noscript>
	    <?php echo JHtml::_('image', $img, $alt, $arraynoscript); ?>
    </noscript>
</div>