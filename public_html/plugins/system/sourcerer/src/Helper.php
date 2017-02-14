<?php
/**
 * @package         Sourcerer
 * @version         7.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Sourcerer;

defined('_JEXEC') or die;

use JFactory;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Protect as RL_Protect;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onContentPrepare($article, $context)
	{
		$params = Params::get();

		$area = isset($article->created_by) ? 'articles' : 'other';

		$remove = $params->remove_from_search
			&& in_array($context, ['com_search.search', 'com_search.search.article', 'com_finder.indexer']);

		Security::set($article);

		if (!RL_Document::isCategoryList($context))
		{
			switch (true)
			{
				case (isset($article->text)):
					Replace::replace($article->text, $area, $article, $remove);
					break;

				case (isset($article->introtext)):
					Replace::replace($article->introtext, $area, $article, $remove);

				case (isset($article->fulltext)) :
					Replace::replace($article->fulltext, $area, $article, $remove);
					break;
			}
		}

		if (isset($article->description))
		{
			Replace::replace($article->description, $area, $article, $remove);
		}

		if (isset($article->title))
		{
			Replace::replace($article->title, $area, $article, $remove);
		}
	}

	public function onAfterDispatch()
	{
		if (!RL_Document::isHtml() || !$buffer = RL_Document::getBuffer())
		{
			return;
		}

		$buffer = Area::tag($buffer, 'component');

		RL_Document::setBuffer($buffer);
	}

	public function onAfterRender()
	{
		// only in html, pdfs, ajax/raw and feeds
		if (!in_array(JFactory::getDocument()->getType(), ['html', 'pdf', 'ajax', 'raw']) && !RL_Document::isFeed())
		{
			return;
		}

		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		$params = Params::get();

		list($pre, $body, $post) = RL_Html::getBody($html);

		Protect::_($body);
		Replace::replaceInTheRest($body);

		Clean::cleanLeftoverJunk($body);
		RL_Protect::unprotect($body);

		$params->enable_in_head
			? Replace::replace($pre, 'head')
			: Clean::cleanTagsFromHead($pre);

		$html = $pre . $body . $post;

		JFactory::getApplication()->setBody($html);
	}
}
