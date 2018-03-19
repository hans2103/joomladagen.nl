<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Export controller.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviControllerExport extends JControllerLegacy
{
	/**
	 * Export for front-end.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	public function export()
	{
		// Get the template ID
		$templateId = $this->input->get('csvi_template_id', false);
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_csvi/models');
		$db = JFactory::getDbo();

		if ($templateId)
		{
			// Load the model
			/** @var CsviModelExports $model */
			$model = $this->getModel('Exports', 'CsviModel');

			// Initialise
			$model->initialise($templateId);

			// Get the run ID
			$runId = $model->getRunId();

			try
			{
				if ($runId)
				{
					if ($templateId)
					{
						$model->loadTemplate($templateId);

						// Load the template
						$template = $model->getTemplate();

						// Get the component and operation
						$component = $template->get('component');
						$operation = $template->get('operation');
						$override = $template->get('override');

						// Get the administrator template
						$query = $db->getQuery(true)
							->select($db->quoteName('template'))
							->from($db->quoteName('#__template_styles'))
							->where($db->quoteName('client_id') . ' = 1')
							->where($db->quoteName('home') . ' = 1');
						$db->setQuery($query)->execute();
						$adminTemplate = $db->loadResult();

						if ($component && $operation)
						{
							if ($template->getEnabled())
							{
								if ($template->getFrontend())
								{
									$secret = $template->getSecret();

									if (!empty($secret))
									{
										// Check if the secret key matches
										$key = $this->input->get('key', '', 'string');

										if ($key == $secret)
										{
											// Set any template details found in the request
											foreach ($_GET as $name => $var)
											{
												if ($position = stripos($name, 'form_') !== false)
												{
													// Option name
													$option = str_ireplace('form_', '', $name);

													// Get the value
													$value = $this->input->getString($name, $template->get($name));

													if (strlen($value) > 0 )
													{
														// Check for multiple values
														if (strpos($value, '|'))
														{
															$value = explode('|', $value);
														}

														// Set the template option name
														$template->set($option, $value);
													}
												}
											}

											// Setup the component autoloader
											$extension = substr($component, 4);
											JLoader::registerNamespace($extension, JPATH_PLUGINS . '/csviaddon/');

											// This loader is still needed for other classes than the export
											JLoader::registerPrefix(ucfirst($component), JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component);

											// Load the export routine
											$classname = '\\' . $extension . '\\' . $component . '\\model\export\\' . $operation;

											if ($override)
											{
												if (file_exists(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/export/' . $override . '.php'))
												{
													JLoader::registerPrefix(ucfirst($component), JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component);
													$classname = ucwords($component) . 'ModelExport' . ucwords($override);
												}
												else
												{
													JLoader::registerNamespace($extension, JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/');
													$classname = '\\' . $extension . '\\' . $component . '\\model\export\\' . $override;
												}
											}

											$routine = new $classname;

											// Prepare for export
											$routine->initialiseExport($runId);
											$routine->onBeforeExport($component);

											// Inject our own template instance because we could have modified the settings
											$routine->setTemplate($template);

											if ($override)
											{
												// Set the override for the operation model if exists
												$overridefile = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/export/' . $override . '.php';

												if (file_exists($overridefile))
												{
													$this->addModelPath(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . 'model/export');
												}
												else
												{
													$this->addModelPath(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/model/export');
												}
											}

											// Start the export
											if ($routine->runExport())
											{
												// Get the export location from the URL
												$exportTo = $this->input->get('exportto', 'tofront');

												// Load the destinations
												$destinations = array($exportTo);

												if ($exportTo === 'template')
												{
													// Get output destinations from the template
													$destinations = $template->get('exportto', 'tofront');

													if (!is_array($destinations))
													{
														$destinations = array($destinations);
													}
												}

												// Move download option to end so the file is saved before deleted
												if (in_array('todownload', $destinations))
												{
													$keyDownload = array_search('todownload', $destinations);
													unset($destinations[$keyDownload]);
													$destinations[] = 'todownload';
												}

												// Load the name of the file to process
												$processfile = $routine->getProcessfile();

												// Delete any empty file if needed
												$routine->deleteEmptyFile($processfile);
												$message = '';
												$type    = '';

												if (JFile::exists($processfile))
												{
													// Check output destinations
													foreach ($destinations as $destination)
													{
														switch ($destination)
														{
															case 'tofile':
																$model->writeFile($processfile);
																$destinationFile = $template->get('localpath', JPATH_SITE) . '/' . basename($processfile);
																$message         = JText::sprintf('COM_CSVI_EXPORTFILE_CREATED', $destinationFile);
																break;
															case 'toftp':
																$message = JText::sprintf('COM_CSVI_FILE_SAVED_ON_FTP', $processfile);

																if (!$model->ftpFile($processfile))
																{
																	$message = JText::sprintf('COM_CSVI_FTP_EXPORTFILE_NOT_CREATED', $processfile);
																	$type    = 'error';
																}
																break;
															case 'toemail':
																$message = JText::_('COM_CSVI_MAIL_SEND');

																if (!$model->emailFile($processfile))
																{
																	$message = JText::_('COM_CSVI_NO_MAIL_SEND_EXPORT');
																	$type = 'error';
																}
																break;
															case 'todownload':
																$model->downloadFile($processfile);
																break;
															case 'tofront':
															default:
																$model->displayFile($processfile);
																break;
														}
													}
												}

												// Remove the temporary file if needed
												JFile::delete($processfile);

												if ($message)
												{
													$link = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : JRoute::_('index.php');
													$this->setRedirect($link, $message, $type);
													$this->redirect();
												}

												JFactory::getApplication()->close();
											}
											else
											{
												throw new CsviException(JText::_('COM_CSVI_EXPORT_RUN_FAILED'), 500);
											}
										}
										else
										{
											throw new CsviException(JText::sprintf('COM_CSVI_SECRET_KEY_DOES_NOT_MATCH', $key), 518);
										}
									}
									else
									{
										throw new CsviException(JText::_('COM_CSVI_SECRET_KEY_EMPTY'), 517);
									}
								}
								else
								{
									throw new CsviException(JText::_('COM_CSVI_EXPORT_TEMPLTE_FRONTEND_NOT_ALLOWED'), 516);
								}
							}
							else
							{
								throw new CsviException(JText::_('COM_CSVI_EXPORT_TEMPLATE_NOT_ENABLED'), 515);
							}
						}
						else
						{
							throw new CsviException(JText::_('COM_CSVI_EXPORT_NO_COMPONENT_NO_OPERATION'), 514);
						}
					}
					else
					{
						throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATEID_FOUND'), 509);
					}
				}
				else
				{
					throw new CsviException(JText::_('COM_CSVI_NO_VALID_RUNID_FOUND'), 506);
				}
			}
			catch (Exception $e)
			{
				// Finalize the export
				$model = $this->getModel('Exports', 'CsviModel');
				$model->setEndTimestamp($runId);

				// Redirect to the template view
				$this->setRedirect('index.php', $e->getMessage(), 'error');
				$this->redirect();
			}
		}
		else
		{
			// Redirect to the template view
			$this->setRedirect('index.php', JText::_('COM_CSVI_NO_TEMPLATE_ID_FOUND'), 'error');
			$this->redirect();
		}
	}
}
