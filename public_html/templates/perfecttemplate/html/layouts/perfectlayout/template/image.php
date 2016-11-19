<?php
/*
 * @package		NPO Radio 1
 * @copyright	Copyright (c) 2015-2016 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$image           = $displayData['image'];
$backgroundimage = '';

if(is_array($image))
{
	if(isset($image[0]) && $image[0])
	{
		$image = $image[0];
	}
	elseif(isset($image[1]) && $image[1])
	{
		$image = $image[1];
	}
	elseif(isset($image[2]) && $image[2])
	{
		$image = $image[2];
	}
}

if (isset($image) && is_numeric($image))
{
	if ($displayData['ratio'] == '4-3')
	{
		$backgroundimage = 'http://radiobox2.omroep.nl/image/file/' . $image . '/image.jpg?width=200&height=150';
	}
	elseif ($displayData['ratio'] == '3-4')
	{
		$backgroundimage = 'http://radiobox2.omroep.nl/image/file/' . $image . '/image.jpg?width=200&height=300';
	}
	else
	{
		$backgroundimage = 'http://radiobox2.omroep.nl/image/file/' . $image . '/image.jpg?width=230&height=230';
	}
}
elseif (isset($image))
{
	$backgroundimage = $image;
}

if (isset($displayData['coverall']))
{
	$coverall = ' image-coverall-beta';
}
else
{
	$coverall = '';
}

if (is_array($backgroundimage))
{
	$backgroundimage = array_shift($backgroundimage);
}

echo '<div class="image__wrapper ' . $displayData['class'] . '">';
echo '  <div class="image__placeholder image__placeholder--' . $displayData['ratio'] . $coverall . '"';
if ($backgroundimage)
{
	echo '       style="background-image: url("' . $backgroundimage . '");"';
}
echo '  >';
echo '  </div>';
echo '</div>';
