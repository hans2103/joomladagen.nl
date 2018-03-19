<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

require_once JPATH_PLUGINS . '/csviaddon/j2store/com_j2store/helper/com_j2store.php';

/**
 * J2Store maintenance.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Com_J2StoreMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  7.3.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.3.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  7.3.0
	 */
	private $csvihelper = null;

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver $db         The database class
	 * @param   CsviHelperLog   $log        The CSVI logger
	 * @param   CsviHelperCsvi  $csvihelper The CSVI helper
	 * @param   bool            $isCli      Set if we are running CLI mode
	 *
	 * @since   7.3.0
	 */
	public function __construct(JDatabaseDriver $db, CsviHelperLog $log, CsviHelperCsvi $csvihelper, $isCli = false)
	{
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
		$this->isCli      = $isCli;
	}

	/**
	 * Load a number of maintenance tasks.
	 *
	 * @return  array  List of available operations.
	 *
	 * @since   7.5.0
	 */
	public function getOperations()
	{
		return array('options' => array(
			''                    => JText::_('COM_CSVI_MAKE_CHOICE'),
			'removeproductprices' => JText::_('COM_CSVI_REMOVEPRODUCTPRICES_LABEL'),
			'emptydatabase'       => JText::_('COM_CSVI_EMPTYDATABASE_LABEL')
		)
		);
	}

	/**
	 * Load the options for a selected operation.
	 *
	 * @param   string $operation The operation to get the options for
	 *
	 * @return  string  The options for a selected operation.
	 *
	 * @since   7.5.0
	 */
	public function getOptions($operation)
	{
		switch ($operation)
		{
			case 'emptydatabase':
				$layout = new JLayoutFile('csvi.modal');
				$html   = '<span class="help-block">' . JText::_('COM_CSVI_' . $operation . '_DESC') . '</span>';
				$html .= $layout->render(
					array(
						'modal-header'  => JText::_('COM_CSVI_' . $operation . '_LABEL'),
						'modal-body'    => JText::_('COM_CSVI_CONFIRM_DB_DELETE'),
						'cancel-button' => true
					)
				);

				return $html;
				break;
			case 'removeproductprices':
				$settings = new CsviHelperSettings($this->db);
				$log      = new CsviHelperLog($settings, $this->db);
				$template = new CsviHelperTemplate($template = '');
				$fields   = new CsviHelperFields($template, $log, $this->db);
				$helper   = new Com_J2StoreHelperCom_J2Store($template, $log, $fields, $this->db);
				$options  = $helper->getManufacturerList();
				array_unshift($options, array('text' => JText::_('COM_CSVI_SELECT_MANUFACTURER_OPTION'), 'value' => ''));

				return '<div class="control-group ">
							<div class="control-label">
								<label title="" class="hasTooltip" for="jform_title" id="jform_title-lbl" data-original-title=" ' . JText::_('COM_CSVI_SELECT_MANUFACTURER_DESC') . '">
									' . JText::_('COM_CSVI_SELECT_MANUFACTURER_LABEL') . '
								</label>
							</div>
							<div class="controls">
								' . JHtml::_('select.genericlist', $options, 'form[manufacturer]', 'class="input-large"') . '
							</div>
						</div>';
				break;
			default:
				return '<span class="help-block">' . JText::_('COM_CSVI_' . $operation . '_DESC') . '</span>';
				break;
		}
	}

	/**
	 * Remove all product prices.
	 *
	 * @param   JInput $input An instance of JInput.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   7.5.0
	 */
	public function removeProductPrices(JInput $input)
	{
		$manufacturer = $input->get('manufacturer');

		if ($manufacturer && $manufacturer !== '')
		{
			$vidArray = array();
			$query    = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_variant_id'))
				->from($this->db->quoteName('#__j2store_variants'))
				->leftJoin(
					$this->db->quoteName('#__j2store_products')
					. ' ON ' . $this->db->quoteName('#__j2store_products.j2store_product_id') . ' = ' . $this->db->quoteName('#__j2store_variants.product_id')
				)
				->where($this->db->quoteName('#__j2store_products.manufacturer_id') . ' = ' . (int) $manufacturer);
			$this->db->setQuery($query);
			$variantIds = $this->db->loadRowList();

			foreach ($variantIds as $variantId)
			{
				$vidArray[] = $variantId[0];
			}

			try
			{
				$query->clear()
					->update($this->db->quoteName('#__j2store_variants'))
					->set($this->db->quoteName('price') . ' = ' . $this->db->quote(''))
					->where($this->db->quoteName('j2store_variant_id') . ' IN (' . implode(', ', $vidArray) . ')');
				$this->db->setQuery($query)->execute();
				$query->clear()
					->delete($this->db->quoteName('#__j2store_product_prices'))
					->where($this->db->quoteName('variant_id') . ' IN (' . implode(', ', $vidArray) . ')');
				$this->db->setQuery($query)->execute();
				$this->log->setLineNumber(1);
				$this->log->addStats('empty', JText::_('COM_CSVI_DEBUG_DELETE_PRODUCT_PRICES_MANUFACTURER'));
			} catch (Exception $e)
			{
				$this->log->addStats('error', $e->getMessage());
			}
		}
		else
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__j2store_variants'))
				->set($this->db->quoteName('price') . ' = ' . $this->db->quote(''));
			$this->db->setQuery($query)->execute();
			$this->db->truncateTable('#__j2store_product_prices');
			$this->log->setLineNumber(1);
			$this->log->addStats('empty', JText::_('COM_CSVI_J2STORE_PRICES_EMPTIED'));
		}

		return true;
	}

	/**
	 * Empty J2Store tables.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   7.5.0
	 */
	public function emptyDatabase()
	{
		$linenumber = 1;

		// Collect all Joomla content table ids before cleaning the products
		$this->log->setLinenumber($linenumber++);
		$cidArray = array();
		$query    = $this->db->getQuery(true)
			->select($this->db->quoteName('product_source_id'))
			->from($this->db->quoteName('#__j2store_products'));
		$this->db->setQuery($query);
		$contentIds = $this->db->loadRowList();

		foreach ($contentIds as $contentId)
		{
			$cidArray[] = $contentId[0];
		}

		try
		{
			if ($cidArray)
			{
				$query->clear()
					->delete($this->db->quoteName('#__content'))
					->where($this->db->quoteName('id') . ' IN (' . implode(', ', $cidArray) . ')');
				$this->db->setQuery($query)->execute();
				$this->log->add('Empty Joomla content table');
			}
			$this->log->addStats('empty', JText::_('COM_CSVI_JOOMLA_CONTENT_TABLE_EMPTIED'));
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_products');
		$this->db->setQuery($query);
		$this->log->add('Empty product table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product quantities table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_productquantities');
		$this->db->setQuery($query);
		$this->log->add('Empty product quantities table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_QUANTITY_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product filters table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_product_filters');
		$this->db->setQuery($query);
		$this->log->add('Empty product filters table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_FILTERS_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product options table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_product_options');
		$this->db->setQuery($query);
		$this->log->add('Empty product options table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_OPTIONS_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product option values table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_product_optionvalues');
		$this->db->setQuery($query);
		$this->log->add('Empty product option values table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_OPTION_VALUES_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product prices table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_product_prices');
		$this->db->setQuery($query);
		$this->log->add('Empty product prices table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_PRICES_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product variant option values table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_product_variant_optionvalues');
		$this->db->setQuery($query);
		$this->log->add('Empty product variant option values table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_VARIANT_OPTION_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product price index table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_productprice_index');
		$this->db->setQuery($query);
		$this->log->add('Empty product prices index table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_PRICE_INDEX_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product images table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_productimages');
		$this->db->setQuery($query);
		$this->log->add('Empty product images table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_IMAGES_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty product files table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_productfiles');
		$this->db->setQuery($query);
		$this->log->add('Empty product files table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_PRODUCT_FILES_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		// Empty cart items table
		$this->log->setLinenumber($linenumber++);
		$query = 'TRUNCATE TABLE ' . $this->db->quoteName('#__j2store_cartitems');
		$this->db->setQuery($query);
		$this->log->add('Empty cart items table');

		try
		{
			$this->db->execute();
			$this->log->addStats('empty', 'COM_CSVI_CART_ITEMS_TABLE_HAS_BEEN_EMPTIED');
		} catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage());
		}

		$linenumber--;
		$this->log->setLineNumber($linenumber);

		return true;
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.3.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 80;
	}
}
