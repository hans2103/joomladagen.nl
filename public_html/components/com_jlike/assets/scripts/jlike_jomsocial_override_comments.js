	var jLikeSmilehtml;
		/** DONE
		 * This
		 * - shows jlike_jomsocial_smiley box when clicked on smiely icon in chatbox window
		 *
		 * @param htmlElement selector
		 **/
		function jLikeshowSmiley(selector)
		{
			if (techjoomla.jQuery(selector).parent().find(".jlike_jomsocial_smileybox").css("display") == 'block')
			{
				techjoomla.jQuery(selector).parent().find(".jlike_jomsocial_smileybox").css("display", "none");
				return false;
			}
			if (jLikeSmilehtml != null)
			{
				techjoomla.jQuery(selector).parent().html(jLikeSmilehtml);
				return;
			}
			techjoomla.jQuery.ajax(
				{
					url: site_link + "components/com_jlike/assets/smileys.txt",
					success: function (data)
					{
						JLikeSmilebackhtml = data;
						var smileyarr = data.split("\n");

						jLikeSmilehtml = '<button onclick="javascript:jLikeshowSmiley(this);" alt="" class="jlike_jomsocial_smiley" id="jlike_jomsocial_smiley" type="button"></button><div class=jlike_jomsocial_smileybox><table><tr>';
						var getsmiledata = new Array();
						for (var i = 0; i < smileyarr.length - 1; i++)
						{
							var getdata = smileyarr[i].split("=");
							getsmiledata.push(getdata[1]);
						}
						getsmiledata = jbunique(getsmiledata);
						for (var i = 0; i < getsmiledata.length; i++)
						{
							if (i % 2 == 0 && i != 0)
							{
								jLikeSmilehtml += '</tr><tr>';
							}
							jLikeSmilehtml += '<td><img src="' + site_link + 'components/com_jlike/assets/images/smileys/' + getsmiledata[i] + '"  onClick="javascript:jLikeSmileyClicked(this);" class="jlike_jomsocial_smiley"/></td>';
						}
						jLikeSmilehtml += '</tr></table></div>';
						techjoomla.jQuery(selector).parent().html(jLikeSmilehtml);
					}
				});
				return false;
		}

		/** DONE
		 * This
		 * - returns unique array for given array
		 *
		 * @param array arrayName
		 * @return array newArray
		 *
		 * */

		function jbunique(arrayName)
		{
			var newArray = new Array();
			label: for (var i = 0; i < arrayName.length; i++)
			{
				for (var j = 0; j < newArray.length; j++)
				{
					if (newArray[j] == arrayName[i]) continue label;
				}
				newArray[newArray.length] = arrayName[i];
			}
			return newArray;
		}
		/** DONE
		 * This
		 * - hides smileybox when clicked on a smiley
		 * - pushes smiley code in textinput area
		 *
		 * @param htmlElement selector
		 *
		 * */

		/* This - hides smileybox when clicked on a smiley - pushes smiley code in textinput area
		 * @param htmlElement selector
		 **/
		function jLikeSmileyClicked(selector)
		{
			techjoomla.jQuery(selector).parent().parent().parent().parent().parent().hide();
			var srcarr = techjoomla.jQuery(selector).attr("src").split("/");
			if (JLikeSmilebackhtml != null)
			{
				var smileyarr = JLikeSmilebackhtml.split("\n");
				for (var i = 0; i < smileyarr.length; i++)
				{
					var getdata = smileyarr[i].split("=");
					if (getdata[1] == srcarr[srcarr.length - 1])
					{
						techjoomla.jQuery(selector).parent().parent().parent().parent().parent().parent().parent().parent().find(".smiley_textarea").insertAtCaret(getdata[0]);
						techjoomla.jQuery(selector).parent().parent().parent().parent().parent().parent().parent().parent().find(".smiley_textarea").focus();
						break;
					}
				}
				return;
			}
		}
