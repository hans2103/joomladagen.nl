function addClone(rId,rClass)
		{
			//window.field_lenght=f_lenght;
			var pre=field_lenght;
			field_lenght++;

				var removeButton="<div id='remove_btn_div"+pre+"' class='com_tjfields_remove_button span2'>";
				removeButton+="<button class='btn btn-small btn-danger' type='button' id='remove"+pre+"'";
				removeButton+="onclick=\"removeClone('com_tjfields_repeating_block"+pre+"','remove_btn_div"+pre+"');\" title=\"<?php echo JText::_('COM_TJFIELDS_REMOVE_TOOLTIP');?>\" >";
				removeButton+="<i class=\""+tjfield_icon_minus+"\"></i></button>";
				removeButton+="</div>";

				var newElem=techjoomla.jQuery('#'+rId+pre).clone().attr('id',rId+field_lenght);
				newElem.find('input[name=\"tjfields[' + pre + '][optionname]\"]').val('');
				newElem.find('input[name=\"tjfields[' + pre + '][optionvalue]\"]').val('');
				newElem.find('input[name=\"tjfields[' + pre + '][optionname]\"]').attr({'name': 'tjfields[' + field_lenght + '][optionname]','value':''});
				newElem.find('input[name=\"tjfields[' + pre + '][optionvalue]\"]').attr({'name': 'tjfields[' + field_lenght + '][optionvalue]','value':''});
				newElem.find('input[name=\"tjfields[' + pre + '][hiddenoption]\"]').attr({'name': 'tjfields[' + field_lenght + '][hiddenoption]','value':''});
				newElem.find('input[name=\"tjfields[' + pre + '][hiddenoptionid]\"]').attr({'name': 'tjfields[' + field_lenght + '][hiddenoptionid]','value':''});
				newElem.find('span[name=\"tjfields[' + pre + '][defaultoptionvalue]\"]').attr({'name': 'tjfields[' + field_lenght + '][defaultoptionvalue]'}); //newElem.find('img[src="localhost/jt315/administrator/components/com_tjfields/images/default.png"]').attr({'src':'localhost/jt315/administrator/components/com_tjfields/images/nodefault.png'});

				/*incremnt id*/
				newElem.find('input[id=\"tjfields_optionname_'+pre+'\"]').attr({'id': 'tjfields_optionname_'+field_lenght,'value':''});
				newElem.find('input[id=\"tjfields_optionvalue_'+pre+'\"]').attr({'id': 'tjfields_optionvalue_'+field_lenght,'value':''});
				newElem.find('input[id=\"tjfields_hiddenoption_'+pre+'\"]').attr({'id': 'tjfields_hiddenoption_'+field_lenght,'value':''});
				newElem.find('input[id=\"tjfields_hiddenoptionid_'+pre+'\"]').attr({'id': 'tjfields_hiddenoptionid_'+field_lenght,'value':''});
				newElem.find('span[id=\"tjfields_defaultoptionvalue_'+pre+'\"]').attr({'id': 'tjfields_defaultoptionvalue_'+field_lenght,'value':''});

				techjoomla.jQuery('#'+rId+pre).after(newElem);
				techjoomla.jQuery('#tjfields_defaultoptionvalue_'+field_lenght).html('<i class="'+tjfield_icon_emptystar+'"></i>');
				techjoomla.jQuery('#'+rId+pre).after(removeButton)
			//	techjoomla.jQuery('#'+rId+pre).append(removeButton);
		}

	   function removeClone(rId,r_btndivId){
					techjoomla.jQuery('#'+rId).remove();
					techjoomla.jQuery('#'+r_btndivId).remove();
		}

		function getdefaultimage(span_id)
		{
			//make all nodefault..
				if(techjoomla.jQuery('#jform_type').val()=='single_select' || techjoomla.jQuery('#jform_type').val()=='radio')
				{
					techjoomla.jQuery('.tjfields_defaultoptionvalue').each(function(){

							techjoomla.jQuery(this).html("<i class='"+tjfield_icon_emptystar+"'></i>");
							//techjoomla.jQuery(this).attr('src',"<?php echo JUri::root().'administrator'.DS.'components'.DS.'com_tjfields'.DS.'images'.DS.'nodefault.png' ?>");

						});
					techjoomla.jQuery('.tjfields_hiddenoption').each(function(){
						techjoomla.jQuery(this).attr('value',0);
					});
				}
				var str1= span_id;
				var req_id= str1.split('_');


				if(techjoomla.jQuery('#'+span_id).children('i').hasClass( tjfield_icon_star ))
				{
					techjoomla.jQuery('#'+span_id).html("<i class='"+tjfield_icon_emptystar+"'></i>");
					techjoomla.jQuery('#tjfields_hiddenoption_'+req_id[2]).attr('value',0);
				}
				else
				{
					techjoomla.jQuery('#'+span_id).html("<i class='"+tjfield_icon_star+"'></i>");
					techjoomla.jQuery('#tjfields_hiddenoption_'+req_id[2]).attr('value',1);
				}
		}
