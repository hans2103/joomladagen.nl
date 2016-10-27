<?php
/**
 * @package   OSMeta
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2013-2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Alledia\OSMeta\Pro\Container;

// No direct access
defined('_JEXEC') or die();

/**
 * Metatags Container Factory Class
 *
 * @since  1.0
 */
class Factory extends \Alledia\OSMeta\Free\Container\Factory
{
    /**
     * Class instance
     *
     * @var Factory
     */
    private static $instance;

    /**
     * Get the singleton instance of this class
     *
     * @return Factory The instance
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Process the metada
     *
     * @param  array  $metadata    The metadata
     * @param  string $queryString Request query string
     * @return void
     */
    protected function processMetadata(&$metadata, $queryString)
    {
        $params = array();
        if ($queryString != null) {
            parse_str($queryString, $params);
        }

        // Check if the current page is the same as the menu link. If so, apply the menu metadata
        if (isset($params['Itemid'])) {
            $container = new Component\Menus;
            $menuMetadata = $container->getMetadata((int)$params['Itemid']);

            if ($menuMetadata['type'] === 'component') {
                $url = \JRoute::_('index.php?' . $queryString);
                $menuLinkUrl = \JRoute::_($menuMetadata['link']);

                if ($url === $menuLinkUrl) {

                    // Only apply if it is empty
                    $metatitle = trim($metadata['metatitle']);
                    if (empty($metatitle)) {
                        $metadata['metatitle'] = $menuMetadata['metatitle'];
                    }

                    $metadesc = trim($metadata['metadescription']);
                    if (empty($metadesc)) {
                        $metadata['metadescription'] = $menuMetadata['metadescription'];
                    }
                } else {
                    $metatitle = trim($metadata['metatitle']);
                    if (empty($metatitle)) {
                        $metadata['metatitle'] = \JFactory::getDocument()->getTitle();
                    }

                    $metadesc = trim($metadata['metadescription']);
                    if (empty($metadesc)) {
                        $metadata['metadescription'] = \JFactory::getDocument()->getDescription();
                    }
                }
            }

            $metadata['item_id'] = (int)$params['Itemid'];
        }
    }

    /**
     * Method to process the body, injecting the metadata
     *
     * @param string $body        Body buffer
     * @param string $queryString Query string
     *
     * @access  public
     *
     * @return string
     */
    public function processBody($body, $queryString)
    {
        $body = parent::processBody($body, $queryString);

        // OpenGraph meta title
        if (isset($this->metadata["metatitle"]) && !empty($this->metadata["metatitle"])) {
            $body = preg_replace(
                "/<meta[^>]*property[\\s]*=[\\s]*[\\\"\\\']+og:title[\\\"\\\']+[^>]*>/i",
                '<meta property="og:title" content="' . htmlspecialchars($this->metadata["metatitle"]) . '" />',
                $body,
                1
            );
        }

        // OpenGraph meta description
        if (isset($this->metadata["metadescription"]) && !empty($this->metadata["metadescription"])) {
            $body = preg_replace(
                "/<meta[^>]*property[\\s]*=[\\s]*[\\\"\\\']+og:description[\\\"\\\']+[^>]*>/i",
                '<meta property="og:description" content="' . htmlspecialchars($this->metadata["metadescription"]) . '" />',
                $body,
                1
            );
        }

        return $body;
    }
}
