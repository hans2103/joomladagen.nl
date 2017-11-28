<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * PWT image Editor Button.
 *
 * @since  1.0
 */
class PlgButtonPwtimage extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button.
	 *
	 * @param   string  $name  The name of the button to display.
	 *
	 * @return  object The button to show.
	 *
	 * @since   1.0
	 */
	public function onDisplay($name)
	{
		$js = "
		function select_pwtimage_article(path, alt, caption, gallery)
		{
			if (path)
			{	
				var tag = '{image';
				
				if (parseInt(gallery) == 1)
				{
					var tags = [];
					tag = '{gallery ';
					
					var images = path.split('|');
					images.forEach(function(image) {
						var items = image.split(',');
						tags.push('path=\"' + items[0] + '\" alt=\"' + items[1] + '\" caption=\"' + items[2] + '\"');
					});
					
					tag += tags.join('|');
				}
				else
				{
					tag += ' path=\"' + path + '\"';
					
					if (alt) {
						tag += ' alt=\"' + alt + '\"';
					}
					
					if (caption) {
						tag += ' caption=\"' + caption + '\"';
					}
				}
				
				tag = tag + '}';
				
				jInsertEditorText(tag, '" . $name . "');
			}
			jModalClose();
		}";

		Factory::getDocument()->addScriptDeclaration($js);

		HTMLHelper::_('behavior.modal');

		// Load the plugin parameters
		$showFolder  = (int) $this->params->get('showFolder', 0) === 1 ? 'true' : 'false';
		$showTools   = (int) $this->params->get('showTools', 1) === 1 ? 'true' : 'false';
		$showGallery = (int) $this->params->get('showGallery', 0) === 1 ? 'true' : 'false';
		$showHelp    = (int) $this->params->get('showHelp', 1) === 1 ? 'true' : 'false';

		$link = 'index.php?option=com_pwtimage&amp;view=image&amp;tmpl=component&amp;wysiwyg=1&amp;' .
			'showFolder=' . $showFolder . '&amp;' .
			'showGallery=' . $showGallery . '&amp;' .
			'showTools=' . $showTools . '&amp;' .
			'showHelp=' . $showHelp;

		$button          = new CMSObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = Text::_('PLG_PWTIMAGE_IMAGE');
		$button->name    = 'image';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";
		$button->set('onclick', 'PWTModalFixer(editor);');

		Factory::getDocument()->addScriptDeclaration('
		function PWTModalFixer(editor) {
			// For MCE editor
			if (typeof editor !== "undefined" && typeof editor.windowManager !== "undefined") {
				var i = 0;
				var interval = setInterval(function() {
					i++;
					var windows = editor.windowManager.getWindows();
					if (windows.length) {
						if (windows[0].$el) {
							windows[0].$el.addClass("pwt-custom-modal-styling")
						}
						clearInterval(interval);
					}
					if (i == 40) {
						clearInterval(interval);
					}
				}, 50);
			}
			// For other editors
			else if (typeof editor !== "undefined") {
				var i = 0;
				var interval = setInterval(function() {
					i++;
					var window = document.getElementById("sbox-window");
					console.log(window.length);
					if (window) {
						window.classList.add("pwt-custom-modal-styling");
						clearInterval(interval);
					}
					if (i == 40) {
						clearInterval(interval);
					}
				}, 50);
			}
		};
		'
		);

		return $button;
	}
}
