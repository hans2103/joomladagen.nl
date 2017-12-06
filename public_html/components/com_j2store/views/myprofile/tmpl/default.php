<?php
/**
 * @package J2Store
* @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
* @license GNU GPL v3 or later
*/
// No direct access to this file
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
$this->params = J2Store::config();
$plugin_title_html = J2Store::plugin()->eventWithHtml('AddMyProfileTab');
$plugin_content_html = J2Store::plugin()->eventWithHtml('AddMyProfileTabContent', array($this->orders));
$messages_above_profile_html = J2Store::plugin()->eventWithHtml('AddMessagesToMyProfileTop', array($this->orders));
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>
<?php if($this->params->get('show_logout_myprofile',0)):?>
	<?php
	JHtml::_('behavior.keepalive');
	$return = base64_encode('index.php?option=com_j2store&view=myprofile');
	?>
	<?php $user = JFactory::getUser (); ?>
	<?php if($user->id > 0): ?>
	<div class="pull-right">
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="login-form" class="form-vertical">
			<div class="logout-button">
				<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGOUT'); ?>" />
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.logout" />
				<input type="hidden" name="return" value="<?php echo $return; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
	<?php endif; ?>
<?php endif;?>
<?php echo J2Store::modules()->loadposition('j2store-myprofile-top'); ?>
<div class="j2store">
	<div class="j2store-order j2store-myprofile">
		<h3><?php echo JText::_('J2STORE_MYPROFILE')?></h3>
        <?php if($messages_above_profile_html != ''){
            ?>
            <div class="j2store-myprofile-addtional_messages">
                <?php
                echo $messages_above_profile_html;
                ?>
            </div>
            <?php
        } ?>
		 <div class="tabbable tabs">
         	   <ul class="nav nav-tabs">
                  <li class="active">
	                  	<a href="#orders-tab" data-toggle="tab"><i class="fa fa-th-large"></i>
	                  		 <?php echo JText::_('J2STORE_MYPROFILE_ORDERS'); ?>
	                  	</a>
                 </li>
                 	<?php if($this->params->get('download_area', 1)): ?>
                 	<li>
	                  	<a href="#downloads-tab" data-toggle="tab"><i class="fa fa-cloud-download"></i>
	                  		 <?php echo JText::_('J2STORE_MYPROFILE_DOWNLOADS'); ?>
	                  	</a>
                 	</li>
                 	<?php endif;?>
                 	
                 	<?php if($this->user->id) : ?>
                    <li>
                  		<a href="#address-tab" data-toggle="tab">
                  			<i class="fa fa-globe"></i>
                  		 	<?php echo JText::_('J2STORE_MYPROFILE_ADDRESS'); ?>
                  		</a>
                  </li>
                  <?php endif; ?>
                  <?php echo $plugin_title_html; ?>
            	</ul>
				<div class="tab-content">
	                  <div class="tab-pane active" id="orders-tab">
						  <?php echo J2Store::modules()->loadposition('j2store-myprofile-order'); ?>
		                	<div class="table-responsive">
								<?php echo $this->loadTemplate('orders');?>
							</div>						
	                  </div>
					
					<?php if($this->params->get('download_area', 1)): ?>
	                  <div class="tab-pane" id="downloads-tab">
						  <?php echo J2Store::modules()->loadposition('j2store-myprofile-download'); ?>
	                  		<div class="<?php echo $J2gridCol; ?>12">
		                  		<div class="table-responsive">
		                  			<?php echo $this->loadTemplate('downloads');?>
		                  		</div>
							</div>
	                  </div>
					<?php endif; ?>
					
	                  <?php if($this->user->id) : ?>
	                  	<div class="tab-pane" id="address-tab">
							<?php echo J2Store::modules()->loadposition('j2store-myprofile-address'); ?>
	                  		<div class="<?php echo $J2gridCol; ?>12">
	                  			<?php echo $this->loadTemplate('addresses');?>
							</div>
	                  </div>
	                  <?php endif; ?>
	                  <?php echo $plugin_content_html; ?>
                 </div>
		</div>
	</div>
</div>
<?php echo J2Store::modules()->loadposition('j2store-myprofile-bottom'); ?>