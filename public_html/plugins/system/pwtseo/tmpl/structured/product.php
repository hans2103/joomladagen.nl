<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

$data      = $displayData['data'];
$seo       = $displayData['seo'];
$config    = $displayData['config'];
$component = $displayData['component'];

$images = array();

if ($data->product_images)
{
	foreach ($data->product_images as $image)
	{
		$images[] = Uri::base() . '/' . $image->product_image;
	}
}
?>
{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "<?php echo $data->product_name ?>",
    "image": <?php echo json_encode($images) ?>,
    "description": "<?php echo $data->product_description ?>"
    <?php if ($data->product_identification): ?>
        <?php foreach ($data->product_identification as $item): ?>
        ,"<?php echo $item->product_identifier ?>":"<?php echo $item->product_identifier_value ?>"
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($data->product_brand): ?>
    ,"brand": {
        "@type": "Thing",
        "name": "<?php echo $data->product_brand ?>"
    }
    <?php endif; ?>
}