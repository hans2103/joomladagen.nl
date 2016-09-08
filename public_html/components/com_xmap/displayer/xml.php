<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class XmapDisplayerXml
 */
class XmapDisplayerXml extends XmapDisplayerAbstract
{
    /**
     * @var string
     */
    public $view = 'xml';

    /**
     * @var array
     */
    protected $links = array();

    /**
     * @var array
     */
    protected $sitemapItems = array();

    /**
     * @var SimpleXMLElement
     */
    protected $baseXml = null;

    /**
     * @var string ISO 639 language code for news sitemaps
     */
    protected $defaultLanguage = '*';

    /**
     * @see News: https://support.google.com/news/publisher/answer/74288
     * @see Images: https://support.google.com/webmasters/answer/178636
     * @see Videos: https://support.google.com/webmasters/answer/80472
     * @see Mobile: https://support.google.com/webmasters/answer/34648
     *
     * @var array Array of valid fields for each sitemap type
     */
    protected $fields = array(
        'news'   => array(
            'publication_date',
            'title',
            'keywords',
            'access',
            'genres',
            'stock_tickers',
        ),

        'images' => array(
            'loc',
            'title',
            'caption',
            'geo_location',
            'license',
        ),

        'videos' => array(
            'thumbnail_loc',
            'title',
            'description',
            'content_loc',
            'player_loc',
            'duration',
            'expiration_date',
            'rating',
            'view_count',
            'publication_date',
            'family_friendly',
            'restriction',
            'gallery_loc',
            'price',
            'requires_subscription',
            'uploader',
            'live',
        )
    );

    /**
     * @var array xml namespaces
     */
    protected $spaces = array(
        'news'   => 'http://www.google.com/schemas/sitemap-news/0.9',
        'image'  => 'http://www.google.com/schemas/sitemap-image/1.1',
        'video'  => 'http://www.google.com/schemas/sitemap-video/1.1',
        'mobile' => 'http://www.google.com/schemas/sitemap-mobile/1.0',
    );

    /**
     * @var string base url
     */
    protected $base = '';

    /**
     * @param stdClass $sitemap
     * @param array $items
     * @param array $extensions
     */
    public function __construct(stdClass $sitemap, array &$items, array &$extensions)
    {
        parent::__construct($sitemap, $items, $extensions);

        $languageTag = JFactory::getLanguage()->getTag();

        $this->base = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

        if (in_array($languageTag, array('zh-cn', 'zh-tw')))
        {
            $this->defaultLanguage = $languageTag;
        } else
        {
            $this->defaultLanguage = XmapHelper::getLanguageCode();
        }
    }

    /**
     * define base xml tree
     *
     * return void
     */
    protected function setBaseXml()
    {
        $this->baseXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $this->baseXml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->spaces as $space => $schema)
        {
            if ($this->isType($space))
            {
                $this->baseXml->addAttribute('xmlns:xmlns:' . $space, $schema);
            }
        }
    }

    /**
     * @return string
     */
    public function printSitemap()
    {
        foreach ($this->items as $menutype => &$items)
        {
            $this->printMenuTree($items);
        }

        return $this->baseXml->asXML();
    }

    /**
     * Prints an XML node for the sitemap
     *
     * @param stdClass $node
     *
     * @return bool
     */
    public function printNode(stdClass $node)
    {
        if (is_null($this->baseXml))
        {
            $this->setBaseXml();
        }

        if ($this->isExcluded($node->id, $node->uid))
        {
            return false;
        }

        if (!isset($node->browserNav))
        {
            $node->browserNav = 0;
        }

        if ($node->browserNav == 3)
        {
            return false;
        }

        if (!isset($node->secure))
        {
            $node->secure = JUri::getInstance()->isSSL();
        }

        if ($node->secure)
        {
            $link = JRoute::_($node->link, true, $node->secure);
        } else
        {
            if ($node->link == JUri::root())
            {
                $link = $node->link;
            } else
            {
                $link = $this->base . JRoute::_($node->link);
            }
        }

        // link is already in xml map
        if (isset($this->links[$link]))
        {
            return true;
        }

        $this->count++;
        $this->links[$link] = true;

        if (!isset($node->priority))
        {
            $node->priority = $this->params->get('default_priority', 0.5);
        }

        if (!isset($node->changefreq))
        {
            $node->changefreq = $this->params->get('default_changefreq', 'daily');
        }

        $modified = $this->getValidNodeModified($node);

        // mandatory fields in every type of sitemap
        $url = $this->baseXml->addChild('url');

        $url->addChild('loc', $link);

        /**
         * @see https://support.google.com/webmasters/answer/183668
         */
        if ($this->isType('normal'))
        {
            if ($modified)
            {
                $url->addChild('lastmod', $modified);
            }

            $changefreq = $this->getProperty('changefreq', $node->changefreq, $node->id, 'xml', $node->uid);
            $priority = $this->getProperty('priority', $node->priority, $node->id, 'xml', $node->uid);

            $url->addChild('changefreq', $changefreq);
            $url->addChild('priority', $priority);
        }

        if ($this->isType('news') && isset($node->newsItem) && $node->newsItem == true)
        {
            if (!isset($node->language) || $node->language == '*')
            {
                $node->language = $this->defaultLanguage;
            }

            $news = $url->addChild('news:news:news');

            // required fields
            $publication = $news->addChild('news:news:publication');
            $publication->addChild('news:news:name', $this->sitemap->params->get('news_publication_name'));
            $publication->addChild('news:news:language', $node->language);

            foreach ($this->fields['news'] as $field)
            {
                if (property_exists($node, $field) && !empty($node->{$field}))
                {
                    $news->addChild('news:news:' . $field, $node->{$field});
                }
            }

            if (!property_exists($news, 'news:news:publication_date'))
            {
                $news->addChild('news:news:publication_date', $modified);
            }
        }

        if ($this->isType('images') && isset($node->images) && !empty($node->images))
        {
            foreach ($node->images as $img)
            {
                $image = $url->addChild('image:image:image');

                foreach ($this->fields['images'] as $field)
                {
                    if (property_exists($img, $field) && !empty($img->{$field}))
                    {
                        $image->addChild('image:image:' . $field, $img->{$field});
                    }
                }

                // backward compatibility
                if (property_exists($img, 'src') && !empty($img->src))
                {
                    $image->addChild('image:image:loc', $img->src);
                }
            }
        }

        if ($this->isType('videos') && isset($node->videos) && !empty($node->videos))
        {
            foreach ($node->videos as $vdi)
            {
                $video = $url->addChild('video:video:video');

                foreach ($this->fields['videos'] as $field)
                {
                    if (property_exists($vdi, $field) && !empty($vdi->{$field}))
                    {
                        $video->addChild('video:video:' . $field, $vdi->{$field});
                    }
                }
            }
        }

        if ($this->isType('mobile') && isset($node->mobileItem) && $node->mobileItem == true)
        {
            $url->addChild('mobile:mobile');
        }

        return true;
    }

    /**
     * @param stdClass $node
     *
     * @return int|null|string
     */
    protected function getValidNodeModified(stdClass $node)
    {
        $nullDate = JFactory::getDbo()->getNullDate();

        $modified = (isset($node->modified) && $node->modified != false && $node->modified != $nullDate && $node->modified != -1) ? $node->modified : null;
        if (!$modified && $this->isType('news'))
        {
            $modified = JFactory::getDate()->toUnix();
        }

        if ($modified && !is_numeric($modified))
        {
            $modified = JFactory::getDate($modified)->toUnix();
        }

        if ($modified)
        {
            $modified = gmdate('Y-m-d\TH:i:s\Z', $modified);
        }

        return $modified;
    }

    /**
     * @todo also check if value on menuitem (added with system plugin)
     *
     * @param string $property The property that is needed
     * @param string $value The default value if the property is not found
     * @param int $Itemid The menu item id
     * @param string $view (xml / html)
     * @param int $uid Unique id of the element on the sitemap (the id asigned by the extension)
     *
     * @return string
     */
    protected function getProperty($property, $value, $Itemid, $view, $uid)
    {
        if (isset($this->sitemapItems[$view][$Itemid][$uid][$property]))
        {
            return $this->sitemapItems[$view][$Itemid][$uid][$property];
        }

        return $value;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isType($type)
    {
        switch ($type)
        {
            case'news':
                return $this->isNews;
                break;

            case 'images':
            case 'image':
                return $this->isImages;
                break;

            case 'videos':
            case 'video':
                return $this->isVideos;
                break;

            case 'mobile':
                return $this->isMobile;
                break;

            default:
            case 'normal':
                return !$this->isNews;
                break;
        }
    }

    /**
     * @param array $items
     */
    public function setSitemapItems(array $items)
    {
        $this->sitemapItems = $items;
    }

    /**
     * @param bool $val
     */
    public function displayAsNews($val)
    {
        $this->isNews = (bool)$val;
    }

    /**
     * @param bool $val
     */
    public function displayAsImages($val)
    {
        $this->isImages = (bool)$val;
    }

    /**
     * @param bool $val
     */
    public function displayAsVideos($val)
    {
        $this->isVideos = (bool)$val;
    }

    /**
     * @param bool $val
     */
    public function displayAsMobile($val)
    {
        $this->isMobile = (bool)$val;
    }
}
