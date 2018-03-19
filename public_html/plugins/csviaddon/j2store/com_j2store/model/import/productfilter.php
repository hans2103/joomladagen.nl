<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace j2store\com_j2store\model\import;

defined('_JEXEC') or die;

/**
 * Product files import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.5.0
 */
class Productfilter extends \RantaiImportEngine
{
	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  7.5.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_J2StoreHelperCom_J2Store
	 * @since  7.5.0
	 */
	protected $helper;

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.5.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$sku = $this->getState('sku', false);

		if (!$sku)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', 'COM_CSVI_NO_PRODUCT_SKU_FOUND');
		}
		else
		{
			$this->loaded = true;
			$this->setState('product_id', $this->helper->getProductId('sku', $sku));

			// Remove the existing filters for the product
			if ($this->template->get('clean_filters', 0) && $this->getState('product_id', false))
			{
				$query = $this->db->getQuery(true)
					->delete($this->db->quoteName('#__j2store_product_filters'))
					->where($this->db->quoteName('product_id') . ' = ' . (int) $this->getState('product_id', false));
				$this->db->setQuery($query)->execute();
				$this->log->add('Removed existing filters of product as clean filters set to Yes');
			}

			if (!$this->getState('product_id', false) && !$this->template->get('overwrite_existing_data'))
			{
				$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('sku')), false);
				$this->loaded = false;
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   7.5.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('product_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('sku', '')));
		}
		else
		{
			if (!$this->getState('product_id', false))
			{
				$this->log->add('Product not found with SKU ' . $this->getState('sku'));
				$this->log->AddStats('skipped', \JText::sprintf('COM_CSVI_NO_PRODUCT_ID_FOUND', $this->getState('sku')));
			}
			else
			{
				if ($this->getState('product_id', false) && $this->getState('filter_id', false))
				{
					$filterIds = explode('|', $this->getState('filter_id', false));

					foreach ($filterIds as $filterId)
					{
						$query = $this->db->getQuery(true)
							->delete($this->db->quoteName('#__j2store_product_filters'))
							->where($this->db->quoteName('product_id') . ' = ' . (int) $this->getState('product_id', false))
							->where($this->db->quoteName('filter_id') . ' = ' . (int) $filterId);
						$this->db->setQuery($query)->execute();

						$values = array((int) $this->getState('product_id', false), (int) $filterId);
						$query->clear()
							->insert($this->db->quoteName('#__j2store_product_filters'))
							->columns($this->db->quoteName(array('product_id', 'filter_id')))
							->values(implode(',', $values));
						$this->db->setQuery($query);

						try
						{
							$this->db->execute();
							$this->log->add('Product filters added successfully', false);
							$this->log->addStats('Success', 'COM_CSVI_COM_J2STORE_FILTER_ADDED');
						}
						catch (\Exception $e)
						{
							$this->log->add('Cannot add product filters. Error: ' . $e->getMessage(), false);
							$this->log->addStats('incorrect', $e->getMessage());

							return false;
						}
					}
				}
				else
				{
					if ($this->getState('filter_name', false) && $this->getState('filter_group_name', false))
					{
						$filterNames      = explode('|', $this->getState('filter_name', false));
						$filterGroupNames = explode('|', $this->getState('filter_group_name', false));

						if (count($filterNames) === count($filterGroupNames))
						{
							foreach ($filterGroupNames as $key => $value)
							{
								$filterGroupId = $this->helper->getFilterGroupId($value);

								if ($filterGroupId)
								{
									$filterIds = explode('#', $filterNames[$key]);

									foreach ($filterIds as $filterId)
									{
										$filterNameId = $this->helper->getFilterId($filterId, $filterGroupId);

										if ($filterNameId)
										{
											$query = $this->db->getQuery(true)
												->delete($this->db->quoteName('#__j2store_product_filters'))
												->where($this->db->quoteName('product_id') . ' = ' . (int) $this->getState('product_id', false))
												->where($this->db->quoteName('filter_id') . ' = ' . (int) $filterNameId);
											$this->db->setQuery($query)->execute();

											$values = array((int) $this->getState('product_id', false), (int) $filterNameId);
											$query->clear()
												->insert($this->db->quoteName('#__j2store_product_filters'))
												->columns($this->db->quoteName(array('product_id', 'filter_id')))
												->values(implode(',', $values));
											$this->db->setQuery($query);

											try
											{
												$this->db->execute();
												$this->log->add('Product filters added successfully', false);
												$this->log->addStats('Success', 'COM_CSVI_COM_J2STORE_FILTER_ADDED');
											}
											catch (\Exception $e)
											{
												$this->log->add('Cannot add product filters. Error: ' . $e->getMessage(), false);
												$this->log->addStats('incorrect', $e->getMessage());

												return false;
											}
										}
										else
										{
											$this->log->add(\JText::sprintf('COM_CSVI_COM_J2STORE_FILTER_NOT_FOUND', $filterId));
										}
									}
								}
								else
								{
									$this->log->add('No filter name or filter group name found', false);
									$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_COM_J2STORE_GROUPNAME_NOT_FOUND', $value));
								}
							}
						}
					}
					else
					{
						$this->log->add('No filter name or filter group name found', false);
						$this->log->addStats('incorrect', 'COM_CSVI_COM_J2STORE_FILTERNAME_GROUPNAME_NOT_FOUND');
					}
				}
			}
		}

		return true;
	}
}
