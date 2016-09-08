<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$pageURL = JURI::current();
?>




<div class="newssocial">
<!-- Twitter -->
<div class="soctwitter">
	<a href="https://twitter.com/share" class="twitter-share-button" data-lang="nl" data-hashtags="jd16nl" data-url="<?php echo(JURI::current());?>">Tweeten</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>

<!-- Facebook -->
<div class="socfacebook">
	<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/nl_NL/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
	<div class="fb-like" data-send="false" data-layout="button_count" data-width="200" data-show-faces="false" data-href="<?php echo($pageURL);?>"></div>
</div>

<!-- Google Plus -->
<div class="socplus">
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
	  {lang: 'nl'}
	</script>
	<div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?php echo(JURI::current());?>"></div>
	</div>
</div>