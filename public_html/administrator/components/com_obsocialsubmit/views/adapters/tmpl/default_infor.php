<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;

global $option;
?>
<table class="table table-bordered">
	<tr>
		<td valign="top"><strong><?php echo JText::_( 'COM_OBSOCIALSUBMIT_VERSION' ); ?></strong></td>
		<td>
			<?php echo obSocialSubmitHelper::getVersion( $option ); ?>
		</td>
	</tr>

	<tr>
		<td valign="top"><strong><?PHP echo JText::_( 'COM_OBSOCIALSUBMIT_COPYRIGHT' ); ?></strong></td>
		<td>(C) 2007-<?php echo date( "Y" ); ?> <a href="http://foobla.com" target="_blank">foobla.com</a>.</td>
	</tr>

	<tr>
		<td valign="top"><strong><?php echo JText::_( 'COM_OBSOCIALSUBMIT_LICENSE' ); ?></strong></td>
		<td>GNU/GPL</td>
	</tr>

	<tr>
		<td valign="top"><strong><?php echo JText::_( 'COM_OBSOCIALSUBMIT_CREDITS' ); ?></strong></td>
		<td>
			<ul style="margin: 0; padding-left: 15px;">
				<li><strong>Thong Tran</strong> (the product manager).</li>
				<li><strong>Phong Lo</strong> (developer).</li>
				<li><strong>Tung Pham</strong> (developer).</li>
				<li><strong>Andy Ha</strong> (developer).</li>
			</ul>
		</td>
	</tr>
</table>

<div class="alert alert-info" id="obsupport">
	<h4>
		<i class="fa fa-question fa-2x"></i>
		<a href="http://foobla.com/kb/obsocialsubmit"><?php echo JText::_( 'COM_OBSOCIALSUBMIT_GO_TO_FAQ' ); ?></h4></a>
	</h4>
	<hr />
	<h4>
		<i class="fa fa-smile-o fa-2x"></i> <?php echo JText::_( 'COM_OBSOCIALSUBMIT_CPANEL_SUPPORT_TEXT' ); ?>
	</h4>
</div>