<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
$docs  = JFactory::getDocument();
/*Add Guide libraries*/


$params 	= JComponentHelper::getParams('com_obsocialsubmit');

$guide = $params->get('guideline');



global $isJ25;
if(!$isJ25){
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}
JHtml::_('behavior.multiselect');
$manifest = $this->get('ManifestCache');
//echo '<pre>'.print_r( $manifest, true ).'</pre>';
// $client		= $this->state->get('filter.client_id') ? 'administrator' : 'site';
$user		= JFactory::getUser();
?>
<div id="foobla">
	<div class="row-fluid">
<?php if(!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
	<div class="row-fluid">
		<div class="span7 form-horizontal">
			<!-- Version Notifying -->
			<?php echo obSocialSubmitHelper::versionNotify(); ?>
			<!-- MSG -->
			<?php echo $this->loadTemplate('msg'); ?>
			
			<!-- INSTANCES -->
			<?php echo $this->loadTemplate('instances'); ?>

			<!-- PLUGINS -->
			<?php echo $this->loadTemplate('plugins'); ?>
		</div>
		<div class="span5 form-horizontal">
			<?php echo $this->loadTemplate('infor'); ?>
		</div>
	</div>
</div>
	</div>
</div>


<?php  if($guide){
    
// $docs->addStyleSheet(JURI::root()."administrator/components/com_obsocialsubmit/assets/css/pageguide.min.css");

// $docs->addScript("//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js");
// $docs->addScript(JURI::root()."administrator/components/com_obsocialsubmit/assets/js/pageguide.js");
?>
<!--Begin guide-->
        <!-- this set of pageguide steps will be displayed by default. -->
            <ul id="tlyPageGuide" data-tourtitle="check out these elements">
              <!-- use these classes to indicate where each number bubble should be
              positioned. data-tourtarget should contain the selector for the element to
              which you want to attach this pageguide step. -->
              <li class="tlypageguide_bottom" data-tourtarget=".sidebar-nav">
                    <h3>IMPORTANT! List functions at here.</h3>            
                    <p>TO POST. You can make follow step:</p>
                    <p>Step 1: Go to Connections to create connect with Social. You can click <a href="index.php?option=com_obsocialsubmit&amp;view=connections" target="_blank">here</a> </p>
                    <p>Step 2: Go to Adapters to create connect with extension in Joomla. Eg: Content component, K2 component. You can click <a href="index.php?option=com_obsocialsubmit&amp;view=adapters" target="_blank">here</a> </p>
                    <p>Step 3: Go to Joomla's extension What you created at step 2 to try.</p>                    
              </li>
              <li class="tlypageguide_top" data-tourtarget=".well-post">
                  <p>This is Post Social area.</p>  
                  <p>You can share your status for all Social what you connected.</p> 
              </li>
              <li class="tlypageguide_top" data-tourtarget=".well-plugins">
                  <p>This is check Status Connections area.</p>
                  <p>These OB Social Submit's connections with Social, Joomla's extensions</p>
              </li>
              <li class="tlypageguide_top" data-tourtarget=".well-conections">
                  <p>This is OB Social Submit's plugins Manager</p>  
                  <p>Check status all plugins of OB Social Submit.</p>
              </li>
			  <li class="tlypageguide_bottom" data-tourtarget=".span5.form-horizontal">
                  <p>OB Submit Version</p>
                  <p>If you need our help. You can go to here: <a target="_blank" href="http://foob.la/support">http://foob.la/support</a></p>
              </li>
            </ul>
            <!-- this is a second set of pageguide steps. it is possible to have multiple
            pageguides per page, but you will need to initiate them separately, using the
            steps_element parameter to specify the selectors for each. -->           
            <div class="tlyPageGuideWelcome">
                <p>Welcome to OB Social Submit</p>
                <button class="tlypageguide_start">Let's Go.</button>
                <button class="tlypageguide_ignore">Not Now.</button>
                <button class="tlypageguide_dismiss">Got it, Thanks.</button>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    var pageguide = tl.pg.init();
                });
            </script>                                                
<!--End guide-->
<?php } ?>