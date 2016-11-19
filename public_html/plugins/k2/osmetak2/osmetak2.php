<?php
/**
 * @package   OSMetaK2
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractPlugin;

require_once __DIR__ . '/include.php';

if (defined('ALLEDIA_FRAMEWORK_LOADED')) {
    /**
     * OSMeta System Plugin - Renderer
     *
     * @since  1.0
     */
    class PlgK2OSMetaK2 extends AbstractPlugin
    {
        public function __construct(&$subject, $config = array())
        {
            $this->namespace = 'OSMetaK2';

            parent::__construct($subject, $config);
        }

        /**
         * Event method onAfterRender, to process the metadata on the front-end
         *
         * @access  public
         *
         * @return bool
         */
        public function onRenderAdminForm(&$item, $type, $tab = '')
        {
            if ($tab === 'content') {
                $lang = JFactory::getLanguage();
                $lang->load('com_osmeta');

                // Get the item's metatitle
                $db = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('title')
                    ->from('#__osmeta_metadata')
                    ->where('item_type = 3')
                    ->where('item_id = ' . $db->q($item->id));
                $db->setQuery($query);
                $metaTitle = $db->loadResult();

                $label = JText::_('COM_OSMETA_SEARCH_ENGINE_TITLE_LABEL');

                $doc = JFactory::getDocument();
                if (version_compare(JVERSION, '3.0', '>=')) {
                    // JQuery
                    $doc->addScriptDeclaration("
                        jQuery(function() {
                            var tr = jQuery('<tr><td align=\"right\" class=\"key\">{$label}</td><td><input type=\"text\" class=\"text_area\" name=\"metatitle\" value=\"{$metaTitle}\"></td></tr>');
                            jQuery('textarea[name=metadesc]').parent().parent().parent().prepend(tr);
                        });
                    ");
                } else {
                    // Mootools
                    $doc->addScriptDeclaration("
                        window.addEvent('domready', function() {
                            var tr = new Element('tr');
                            var td = new Element('td', {
                                text: '{$label}',
                                class: 'key'
                            });
                            td.style.align = 'right';
                            td.inject(tr);
                            var td = new Element('td');
                            var input = new Element('input', {
                                type: 'text',
                                name: 'metatitle',
                                value: '{$metaTitle}'
                            });
                            input.inject(td);
                            td.inject(tr);

                            tr.injectBefore($$('textarea[name=metadesc]')[0].parentElement.parentElement);
                        });
                    ");
                }
            }
        }

        public function onAfterK2Save(&$row, $isNew)
        {
            $app = JFactory::getApplication();
            $metaTitle = strip_tags($app->input->get('metatitle', '', 'raw'));

            // Check if the row already exists
            $db = JFactory::getDbo();

            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__osmeta_metadata')
                ->where('item_type = 3')
                ->where('item_id = ' . $db->q($row->id));
            $db->setQuery($query);
            $count = (int) $db->loadResult();

            // Store the item's metatitle
            if ($count > 0) {
                $query = $db->getQuery(true)
                    ->update('#__osmeta_metadata')
                    ->set('title = ' . $db->q($metaTitle))
                    ->where('item_type = 3')
                    ->where('item_id = ' . $row->id);
                $db->setQuery($query);
                $db->execute();
            } else {
                $query = $db->getQuery(true)
                    ->insert('#__osmeta_metadata')
                    ->set('title = ' . $db->q($metaTitle))
                    ->set('item_type = 3')
                    ->set('item_id = ' . $db->q($row->id));
                $db->setQuery($query);
                $db->execute();
            }
        }
    }
}
