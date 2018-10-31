<?php

if (!isset($_GET['getmemyreport']))
{
	exit();
}

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

$db = \Joomla\CMS\Factory::getDbo();

$query = 'SELECT `j2store_orderitems`.`order_id`,
`j2store_orderitems`.`created_by`,
`j2store_orderitemattributes`.`orderitemattribute_name`,
`j2store_orderitemattributes`.`orderitemattribute_price`,
`j2store_orderitemattributes`.`orderitemattribute_value`,
`j2store_orderitems`.`orderitem_discount`,
`j2store_orderitems`.`orderitem_finalprice`,
`j2store_orderitems`.`orderitem_name`,
`j2store_orderitems`.`orderitem_option_price`,
`j2store_orderitems`.`orderitem_price`,
`j2store_orderitems`.`orderitem_quantity`,
`j2store_orderitems`.`orderitem_sku`,
`j2store_orderitems`.`product_type`,
`j2store_orderitems`.`variant_id`
FROM `#__j2store_orderitems` AS `j2store_orderitems`
LEFT JOIN `#__j2store_orderitemattributes` AS `j2store_orderitemattributes` ON `j2store_orderitemattributes`.`orderitem_id` = `j2store_orderitems`.`j2store_orderitem_id`
LEFT JOIN `#__j2store_orders` AS `j2store_orders` ON `j2store_orders`.`order_id` = `j2store_orderitems`.`order_id`
WHERE `j2store_orders`.`order_state_id` IN (\'1\')';

$db->setQuery($query);

$records = $db->loadObjectList();

$tickets                             = array();
$diets                               = array();
$diners                              = array();
$workshops                           = array();
$emails                              = array();
$earlyBirdFriday                     = 25;
$earlyBirdSaturday                   = 70;
$regularFriday                       = 35;
$regularSaturday                     = 80;
$free                                = 0;
$dinner                              = 35;
$report                              = array();
$report['Vrijdagticket']['normaal']  = 0;
$report['Vrijdagticket']['gratis']   = 0;
$report['Vrijdagticket']['early']    = 0;
$report['Zaterdagticket']['normaal'] = 0;
$report['Zaterdagticket']['gratis']  = 0;
$report['Zaterdagticket']['early']   = 0;
$report['Vrijdagdiner']['normaal']   = 0;
$report['Vrijdagdiner']['gratis']    = 0;

// Group the records on the SKU
foreach ($records as $index => $record)
{
	// Group for reporting
	switch ($record->orderitem_sku)
	{
		case 'Vrijdagticket':
			if ((int) $record->orderitem_discount === 0)
			{
				if ((int) $record->orderitem_price === $earlyBirdFriday)
				{
					$report[$record->orderitem_sku]['early']++;
				}
				elseif ((int) $record->orderitem_price === $regularFriday)
				{
					$report[$record->orderitem_sku]['normaal']++;
				}
				else
				{
					echo 'NO MATCH FOR FRIDAY<br />';
				}
			}
			else
			{
				$report[$record->orderitem_sku]['gratis']++; // += 1 * $record->orderitem_quantity;
			}
			break;
		case 'Zaterdagticket':
			if ($record->orderitemattribute_name !== 'Dieetwensen')
			{
				if ((int) $record->orderitem_discount === 0)
				{
					if ((int) $record->orderitem_price === $earlyBirdSaturday)
					{
						$report[$record->orderitem_sku]['early']++;
					}
					elseif ((int) $record->orderitem_price === $regularSaturday)
					{
						$report[$record->orderitem_sku]['normaal']++;
					}
					else
					{
						echo 'NO MATCH FOR SATURDAY<br />';
					}
				}
				else
				{
					$report[$record->orderitem_sku]['gratis']++; // += 1 * $record->orderitem_quantity;
				}
			}
			break;
		case 'Vrijdagdiner':
			if ($record->orderitemattribute_name !== 'Dieetwensen')
			{
				if ((int) $record->orderitem_discount === 0)
				{
					$report[$record->orderitem_sku]['normaal']++;
				}
				else
				{
					$report[$record->orderitem_sku]['gratis']++;
				}
			}
			break;
	}

	// Get the customer name
	$query = $db->getQuery(true)
		->select($db->quoteName(array('name', 'email')))
		->from($db->quoteName('#__users'))
		->where($db->quoteName('id') . ' = ' . (int) $record->created_by);
	$db->setQuery($query);
$nameDetails = $db->loadObject();
	$record->name = $nameDetails->name;
	$record->email = $nameDetails->email;
$emails[] = $record->email;

	switch ($record->orderitem_sku)
	{
		case 'Vrijdagticket':
		case 'Zaterdagticket':
			switch ($record->orderitemattribute_name)
			{
				case 'Dieetwensen':
					$diets[$record->name] = $record->orderitemattribute_value;
					break;
				default:
				case 'Naam bezoeker *':
					if (!array_key_exists($record->orderitem_sku, $tickets))
					{
						$tickets[$record->orderitem_sku] = array();
					}

					if (!array_key_exists('price', $tickets[$record->orderitem_sku]))
					{
						$tickets[$record->orderitem_sku]['price'] = 0;
						$tickets[$record->orderitem_sku]['total'] = 0;
					}

					$tickets[$record->orderitem_sku]['names'][] = !empty($record->orderitemattribute_value) ? $record->orderitemattribute_value : $record->name;
					$tickets[$record->orderitem_sku]['price'] += $record->orderitem_price;
					$tickets[$record->orderitem_sku]['total'] += 1;
					break;
			}
			break;
		case 'Vrijdagdiner':
			switch ($record->orderitemattribute_name)
			{
				case 'Dieetwensen':
					$diets[$record->name] = $record->orderitemattribute_value;
					break;
				default:
					if (!array_key_exists('names', $diners))
					{
						$diners['names'] = array();
						$diners['price'] = 0;
						$diners['total'] = 0;
					}

					$diners['names'][] = $record->orderitemattribute_value;
					$diners['price'] += $record->orderitem_price;
					$diners['total'] += 1;
					break;
			}

			break;
		case 'Familieprogramma' :
		case 'Familieprogramma_zaterdag' :
			if (!array_key_exists($record->orderitem_sku, $tickets))
			{
				$tickets[$record->orderitem_sku]          = array();
				$tickets[$record->orderitem_sku]['names'] = array();
				$tickets[$record->orderitem_sku]['price'] = 0;
				$tickets[$record->orderitem_sku]['total'] = 0;
			}

			$tickets[$record->orderitem_sku]['names'][] = $record->name;
			$tickets[$record->orderitem_sku]['price'] += $record->orderitem_finalprice;
			$tickets[$record->orderitem_sku]['total'] += 1;
			break;
		case 'Sponsorpakket_250':
			if ($record->orderitemattribute_name === 'Sponsor Supporter')
			{
				if (!array_key_exists($record->orderitem_sku, $tickets))
				{
					$tickets[$record->orderitem_sku]          = array();
					$tickets[$record->orderitem_sku]['names'] = array();
					$tickets[$record->orderitem_sku]['price'] = 0;
					$tickets[$record->orderitem_sku]['total'] = 0;
				}

				$tickets[$record->orderitem_sku]['names'][] = $record->name;
				$tickets[$record->orderitem_sku]['price'] += $record->orderitem_finalprice;
				$tickets[$record->orderitem_sku]['total'] += 1;
			}

			break;
		case 'Workshop_A':
			if (stristr($record->orderitem_name, 'phpstorm'))
			{
$workshops['phpstorm']['names'][] = $record->name  . ' - ' . $record->email;

				if (!array_key_exists('price', $workshops['phpstorm']))
				{
					$workshops['phpstorm']['price'] = 0;
					$workshops['phpstorm']['total'] = 0;
				}

				$workshops['phpstorm']['price'] += $record->orderitem_finalprice;
				$workshops['phpstorm']['total'] += $record->orderitem_quantity;
			}
			elseif (stristr($record->orderitem_name, 'fabrik'))
			{
				$workshops['fabrik']['names'][] = $record->name . ' - ' . $record->email;

				if (!array_key_exists('price', $workshops['fabrik']))
				{
					$workshops['fabrik']['price'] = 0;
					$workshops['fabrik']['total'] = 0;
				}

				$workshops['fabrik']['price'] += $record->orderitem_finalprice;
				$workshops['fabrik']['total'] += $record->orderitem_quantity;
			}
			elseif (stristr($record->orderitem_name, 'rsform'))
			{
				$workshops['rsform']['names'][] = $record->name . ' - ' . $record->email;

				if (!array_key_exists('price', $workshops['rsform']))
				{
					$workshops['rsform']['price'] = 0;
					$workshops['rsform']['total'] = 0;
				}

				$workshops['rsform']['price'] += $record->orderitem_finalprice;
				$workshops['rsform']['total'] += $record->orderitem_quantity;
			}
			elseif (stristr($record->orderitem_name, 'joomla'))
			{
				$workshops['joomla']['names'][] = $record->name . ' - ' . $record->email;

				if (!array_key_exists('price', $workshops['joomla']))
				{
					$workshops['joomla']['price'] = 0;
					$workshops['joomla']['total'] = 0;
				}

				$workshops['joomla']['price'] += $record->orderitem_finalprice;
				$workshops['joomla']['total'] += $record->orderitem_quantity;
			}
			break;
	}
}

$total = $diners['price'];
?>
<table>
	<caption>Tickets verdeeld over het type</caption>
	<thead>
	<tr>
		<th>Type</th>
		<th>Normale prijs</th>
		<th>Early Bird</th>
		<th>Gratis</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach ($report as $index => $item) : ?>
			<tr>
				<td><?php echo $index; ?></td>
				<td><?php echo $item['normaal']; ?></td>
				<td><?php echo isset($item['early']) ? $item['early'] : 0; ?></td>
				<td><?php echo $item['gratis']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<hr />
<table>
	<caption>Overzicht</caption>
	<thead>
		<tr>
			<th>
				Item
			</th>
			<th>
				Aantal
			</th>
			<th>
				Opbrengst
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($tickets as $ticket => $values) : $total += $values['price'];?>
		<tr>
			<td>
				<?php if ($values['names']) : ?>
					<a href="#<?php echo $ticket; ?>"><?php echo $ticket; ?></a>
				<?php else: ?>
					<?php echo $ticket; ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $values['total']; ?>
			</td>
			<td>
				&euro; <?php echo number_format($values['price'], 2, ',', '.'); ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td><a href="#vrijdagdiner">Vrijdagdiner</a></td>
			<td><?php echo count($diners['names']); ?></td>
			<td>&euro; <?php echo number_format($diners['price'], 2, ',', '.'); ?></td>
		</tr>
		<tr>
			<td><a href="#diets">Dieetwensen</a></td>
			<td><?php echo count($diets); ?></td>
			<td></td>
		</tr>
		<?php foreach ($workshops as $workshop => $values) : $total += $values['price']; ?>
			<tr>
				<td>
					<a href="#<?php echo $workshop; ?>">Workshop <?php echo ucfirst($workshop); ?></a>
				</td>
				<td>
					<?php echo $values['total']; ?>
				</td>
				<td>&euro; <?php echo number_format($values['price'], 2, ',', '.'); ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td></td>
			<td></td>
			<td>&euro; <?php echo number_format($total, 2, ',', '.'); ?></td>
		</tr>
	</tbody>
</table>
<?php foreach ($tickets as $ticket => $values) : ?>
<table id="<?php echo $ticket; ?>">
	<caption>Toegangstickets <?php echo $ticket; ?></caption>
	<thead>
		<tr>
			<th>
				Naam
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>Totaal: <?php echo $values['total']; ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php sort($values['names']); foreach ($values['names'] as $name) : ?>
			<tr>
				<td><?php echo $name; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach; ?>
<table id="vrijdagdiner">
	<caption>Vrijdagdiner</caption>
	<thead>
	<tr>
		<th>
			Naam
		</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td>Totaal: <?php echo $diners['total']; ?></td>
	</tr>
	</tfoot>
	<tbody>
	<?php sort($diners['names']); foreach ($diners['names'] as $diner) : ?>
		<tr>
			<td><?php echo $diner; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<table id="diets">
	<caption>Dieetwensen</caption>
	<thead>
	<tr>
		<th>
			Naam
		</th>
		<th>
			Dieetwens
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($diets as $user => $diet) : ?>
		<tr>
			<td><?php echo $user; ?></td>
			<td><?php echo $diet; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<div>Het aantal workshops kan hoger liggen dan het aantal namen. Dit komt omdat we geen namen opvragen voor de workshop. We weten dus niet
wie er komen voor een workshop.</div>
<?php foreach ($workshops as $workshop => $values) : ?>
<table id="<?php echo $workshop; ?>">
	<caption>Workshop <?php echo $workshop; ?></caption>
	<thead>
	<tr>
		<th>
			Naam
		</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td>Totaal: <?php echo count($values['names']); ?></td>
	</tr>
	</tfoot>
	<tbody>
	<?php sort($values['names']); foreach ($values['names'] as $name) : ?>
		<tr>
			<td><?php echo $name; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach;

