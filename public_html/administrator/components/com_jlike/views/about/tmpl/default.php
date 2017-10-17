<?php
/**
 * @version     1.0.0
 * @package     com_JLIKE
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');
JHTML::_('behavior.framework');
JHTML::_('behavior.modal');


//$helperobj = new comquick2cartHelper;

if (JVERSION < '3.0')
{
	$strapperClass = 'techjoomla-bootstrap';
}
else
{
	$strapperClass = '';
}


?>

<div class="<?php echo $strapperClass; ?> tj-dashboard">

		<?php if(!empty($this->sidebar)): ?>
			<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		</div>
			<div id="j-main-container" class="span10">
		<?php else : ?>
			<div id="j-main-container">
		<?php endif;?>

		<div class="clearfix">&nbsp;</div>


		<div class="row-fluid">
			<div class="span6" align="center">

					<div class="row-fluid alert alert-success" style="background-color:#DFF0D8;border-color:#D6E9C6;color: #468847;padding: 1px 0;">
						<div><a href="http://techjoomla.com/"><img src="../components/com_jlike/assets/images/jlike_logo.png" alt="some_text"></a></div>
						<div style="font-weight:bold;">jLike is a powerful like system for your entire site.</div>
						<h4><a href="http://techjoomla.com/table/extension-documentation/documentation-for-jlike-formerly-jomlike/" title=""><?php echo JText::_('COM_JLIKE_DOCUMENT');?></a> | <a href="http://demos.techjoomla.com/jomsocial/"><?php echo JText::_('COM_JLIKE_DEMO');?></a> | <a href="http://techjoomla.com/support/support-tickets"><?php echo JText::_('COM_JLIKE_SUPPORT');?></a> </h4>
						<p> </p>
					</div>
					<div class="row-fluid">
						<div class="well">
							<a href="http://extensions.joomla.org/extensions/extension-specific/jomsocial-extensions/16990" target="_blank">
								<?php
								if(JVERSION >= '3.0')
									echo '<i class="icon-quote"></i>';
								else
									echo '<i class="icon-bullhorn"></i>';
								?> <?php echo JText::_('COM_JLIKE_LEAVE_JED_FEEDBACK'); ?>
							</a>
						</div>
					</div>

			</div>
			<div class="span6 well well-small">
				<div class="row-fluid center">
					<a href="http://techjoomla.com/" target="_blank">
						<img src="<?php echo JUri::base().'components/com_jlike/images/techjoomla.png'; ?>" alt="TechJoomla" class="jbolo_vertical_align_top">
					</a>
					<p>Copyright (C)2012-2013 <a href="http://techjoomla.com/" target="_blank">TechJoomla</a>. All rights reserved.</p>
				</div>

				<div class="row-fluid">
						<div class="span12 ">
							<p class="pull-right">
								<span class="label label-info"><?php echo JText::_('COM_JLIKE_STAY_TUNNED'); ?></span>
							</p>
						</div>
				</div>
				<div class="row-striped">
					<div class="row-fluid no-bottom-border">
						<div class="span4"><?php echo JText::_('COM_JLIKE_FACEBOOK'); ?></div>
						<div class="span8">
							<!-- facebook button code -->
							<div id="fb-root"></div>
							<script>(function(d, s, id) {
							  var js, fjs = d.getElementsByTagName(s)[0];
							  if (d.getElementById(id)) return;
							  js = d.createElement(s); js.id = id;
							  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
							  fjs.parentNode.insertBefore(js, fjs);
							}(document, 'script', 'facebook-jssdk'));</script>
							<div class="fb-like" data-href="https://www.facebook.com/techjoomla" data-send="true" data-layout="button_count" data-width="250" data-show-faces="false" data-font="verdana"></div>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_JLIKE_TWITTER'); ?></div>
						<div class="span8">
							<!-- twitter button code -->
							<a href="https://twitter.com/techjoomla" class="twitter-follow-button" data-show-count="false">Follow @techjoomla</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_JLIKE_GPLUS'); ?></div>
						<div class="span8">
							<!-- Place this tag where you want the +1 button to render. -->
							<div class="g-plusone" data-annotation="inline" data-width="300" data-href="https://plus.google.com/102908017252609853905"></div>
							<!-- Place this tag after the last +1 button tag. -->
							<script type="text/javascript">
							(function() {
							var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
							po.src = 'https://apis.google.com/js/plusone.js';
							var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							})();
							</script>
						</div>
					</div>
				</div>
			</div>
		</div><!--row-fluid ends-->

</div><!--BOOTSTRAP DIV-->
