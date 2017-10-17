<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/

// No direct access
defined('_JEXEC') or die('Restricted access');
$document=JFactory::getDocument();
?>
<form name="attendee_field_form" action="" id="attendee_field_form" class="form-validate">
<?php
$i = 0;

foreach($this->orderitems AS $key => $oitem)
{
	if ($this->fields)
	{
	?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_ATTENDEE_INFORMATION');?></strong></h3>
						</div>
						<div class="panel-body">
						<input type="hidden" id="attendee_field[<?php echo $i;?>][order_items_id]" name="attendee_field[<?php echo $i;?>][order_items_id]" placeholder="" value="<?php if(isset($oitem->id)) echo $oitem->id;?>">
						<input type="hidden" id="attendee_field[<?php echo $i;?>][ticket_type]" name="attendee_field[<?php echo $i;?>][ticket_type]" placeholder="" value="<?php if (isset($oitem->id)) echo $oitem->type_id;?>">
						<input type="hidden" id="attendee_field[<?php echo $i;?>][attendee_id]" name="attendee_field[<?php echo $i;?>][attendee_id]" placeholder="" value="<?php if (isset($oitem->attendee_id)) echo $oitem->attendee_id;?>">
						<!--end of form-group-->
						<div class="form-group">
							<label class="control-label"><?php echo JText::_('TICKET_TYPE');?></label>
							<span>
								<strong>
									<?php echo htmlspecialchars($ticketTypeArr[$oitem->type_id], ENT_COMPAT, 'UTF-8');?>
								</strong>
							</span>
						</div>
						<?php
							if (!empty($this->fields['universal_attendee_fields']) and !empty($this->fields['attendee_fields']))
							{
								$allFields = array_merge((array)$this->fields['core_fields'], (array)$this->fields['universal_attendee_fields'], (array)$this->fields['attendee_fields']);
							}
							elseif (!empty($this->fields['attendee_fields']))
							{
								$allFields = array_merge((array)$this->fields['core_fields'], (array)$this->fields['attendee_fields']);
							}
							elseif (!empty($this->fields['universal_attendee_fields']))
							{
								$allFields = array_merge((array)$this->fields['core_fields'], (array)$this->fields['universal_attendee_fields']);
							}
							else
							{
								$allFields = $this->fields['core_fields'];
							}

							foreach ($allFields AS  $akey => $field)
							{
								// Important trick for universal fields, this is needed to save fields based on id(event specific) & name(universal)
								if (isset($field->is_universal) && $field->is_universal)
								{
									$field->id = $field->name;
								}
								?>
								<div class="form-group">
									<div class="">
										<label for="<?php echo "attendee_field_" . $field->id . "_" . $i; ?>" class=" col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
											<?php
												$field->label = htmlspecialchars($field->label, ENT_COMPAT, 'UTF-8');

												// Show required fields
												if ($field->required)
												{
													echo JHtml::tooltip(JText::_($field->label), JText::_($field->label), '', '* ' . JText::_($field->label));
												}
												else
												{
													echo JHtml::tooltip(JText::_($field->label), JText::_($field->label), '', JText::_($field->label));
												}
											?>
										</label>
									</div>
									<div class="">
									 <?php
										$flag = 0;
										$name = $field->name;

										if (isset($field->options))
										{
											$fieldOptions = explode("|", $field->options);
											$attFieldDefaultSelectOption = explode("|", $field->default_selected_option);
										}

										switch ($field->type)
										{
											case 'text':
												?>
												<input type = "<?php echo $field->type;?>"
													id = "attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"
													<?php if ($field->js_function) echo $field->js_function; ?>
													class = "<?php if ($field->required) echo "required"; echo $field->validation_class;?>"
													name = "attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
													placeholder = "<?php if (isset($field->placehoder)) $field->placehoder; else  echo $field->label ?>"
													value = "<?php if (isset($finalOrderItemsValue[$oitem->attendee_id][$field->name])) echo htmlspecialchars($finalOrderItemsValue[$oitem->attendee_id][$field->name], ENT_COMPAT, 'UTF-8');?>">
												<?php
											break;
											case 'textarea':
												?>
												<textarea
													id = "attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>" <?php if($field->js_function) echo $field->js_function; ?>
													class = "<?php if ($field->required) echo "required"; echo $field->validation_class;?>"
													name = "attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
													placeholder="<?php  if (isset($field->placehoder)) $field->placehoder; else  echo $field->label ?>"><?php if (isset($finalOrderItemsValue[$oitem->attendee_id][$field->name])) echo $finalOrderItemsValue[$oitem->attendee_id][$field->name];?></textarea>
												<?php
											break;
											case 'calendar':
												$date = '';

												if (isset($finalOrderItemsValue[$oitem->attendee_id][$field->name]))
												{
													$date = JFactory::getDate($finalOrderItemsValue[$oitem->attendee_id][$field->name])->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
												}

													$calendarFieldId = '';
													$calendarFieldId = "attendee_field[" . $i . "][" . $field->id . "]";

													//Bind calendar events to field
													$calenderScript = 'window.addEvent(\'domready\', function() {Calendar.setup({
															inputField: "' . $calendarFieldId . '", // id of the input field
															ifFormat: "%Y-%m-%d", // format of the input field
															button: "' . $calendarFieldId . '_img", // trigger for the calendar (button ID)
															align: "Tl", // alignment (defaults to "Bl")
															singleClick: true
													});});';
												?>
												<span class="date_field">
													<?php
														echo JHtml::_('calendar', $date,
														"attendee_field[" . $i . "][" . $field->id . "]",
														"attendee_field[" . $i . "][" . $field->id . "]",
														JText::_('COM_JTICKETING_DATE_FORMAT_CALENDER')
														);
													?>
												</span>
												<?php $document->addScriptDeclaration($calenderScript);?>
												<script> <?php echo $calenderScript; ?> </script>
												<?php
											break;
											case 'single_select':
											case 'multi_select':
												$multipleSelectString = '';

												if ($field->type == "multi_select")

												$multipleSelectString = "multiple='multiple'";
												?>
												 <select
													class="<?php echo "chzn-done"; echo $field->validation_class ?>"
													<?php if (isset($multipleSelectString)) echo $multipleSelectString; ?>
													name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>][]"
													id="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>" >

													<?php
														$defaultSelectedOption = $field->default_selected_option;

														if (!empty($defaultSelectedOption))
														{
															$field_val_array = explode("|", $finalOrderItemsValue[$oitem->attendee_id][$field->name]);

															//Universal Fields returns array
															if (is_array($defaultSelectedOption))
															{
																$fieldOptions = $defaultSelectedOption;
															}
															else
															{
																$fieldOptions = explode("|", $defaultSelectedOption);
															}

															foreach($fieldOptions AS $option)
															{
																$selectedString = '';
																if (is_array($defaultSelectedOption))
																{
																	if (isset($finalOrderItemsValue[$oitem->attendee_id][$field->name]) and in_array($option->value, $field_val_array))
																	{
																		$selectedString = 'selected="selected"';
																	}
																}
																else
																{
																	if (isset($finalOrderItemsValue[$oitem->attendee_id][$field->name]) and in_array($option, $field_val_array))
																	{
																		$selectedString = 'selected="selected"';
																	}
																}
																?>
															<option <?php if (!empty($selectedString)) echo $selectedString; ?> value="<?php if (is_array($defaultSelectedOption)) echo $option->value; else echo $option;?>"><?php if (is_array($defaultSelectedOption)) echo $option->options; else echo $option;?></option>
															<?php
															}
														}
														?>
												</select>
												<?php
											break;

											case 'radio':
												$j = 0;

												if (!is_array($field->default_selected_option))
												{
													$defaultSelectedOption = $field->default_selected_option;
													$fieldOptions     = explode("|", $defaultSelectedOption);
													$selectedString = "checked='checked'";

													if (!empty($fieldOptions))
													{
														foreach ($fieldOptions AS $option)
														{
															if (!empty($finalOrderItemsValue) and $finalOrderItemsValue[$oitem->attendee_id][$field->name] == $option)
															{
																$selectedString = "checked='checked'";
															}

															$j++;
															?>
														 <input <?php if (!empty($selectedString)) echo $selectedString; ?>
															type = "radio"
															id = "<?php echo "attendee_field_".$field->id.$j;?>"
															name = "attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
															value = "<?php echo $option;?>"
															class = "<?php echo  $field->validation_class; ?>" >
														<?php echo $option; ?>
														&nbsp;
														<?php
														}
													}
												}
												else
												{
													$fieldOptions = $field->default_selected_option;

													if (!empty($fieldOptions))
													{
														$selectedString = "checked='checked'";

														foreach($fieldOptions AS $option)
														{
															if (!empty($finalOrderItemsValue) and $finalOrderItemsValue[$oitem->attendee_id][$field->name] == $option)
															{
																$selectedString = "checked='checked'";
															}
															else
															{
																if ($option->default_option == 1)
																{
																	$selectedString = "checked='checked'";
																}
															}

															$j++;
															?>
														 <input <?php if (!empty($selectedString)) echo $selectedString; ?>
															type="radio"
															id="<?php echo "attendee_field_" . $field->id . $j;?>"
															name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
															value="<?php echo $option->value;?>"
															class="<?php echo  $field->validation_class; ?>" >
														 <?php echo $option->options;?>
														 &nbsp;
														 <?php
														}
													}
												}
												//break;
											break;
											case 'default':
											case '':
												?>
												<input
													type="<?php echo $field->type;?>"
													id="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"
													<?php if ($field->js_function) echo $field->js_function; ?>
													class="<?php if ($field->required) echo "required"; echo $field->validation_class;?>"
													name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
													placeholder="<?php if (isset($field->placehoder)) $field->placehoder; else  echo $field->label ?>"
													value="">
												<?php
											break;
										}
										?>
									</div>
									<!--end of form-group-->
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
$i++;
}
?>
</form>
