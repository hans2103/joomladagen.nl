<?php
/**
 * @package   J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license   GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
$this->params                = J2Store::config();
$plugin_title_html           = J2Store::plugin()->eventWithHtml('AddMyProfileTab');
$plugin_content_html         = J2Store::plugin()->eventWithHtml('AddMyProfileTabContent', array($this->orders));
$messages_above_profile_html = J2Store::plugin()->eventWithHtml('AddMessagesToMyProfileTop', array($this->orders));
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

$array = array(
	'title' => JText::_('J2STORE_MYPROFILE')
);

echo JLayouts::render('template.content.header', $array);
?>
<?php if ($this->params->get('show_logout_myprofile', 0)): ?>
	<?php
	JHtml::_('behavior.keepalive');
	$return = base64_encode('index.php?option=com_j2store&view=myprofile');
	?>
	<?php $user = JFactory::getUser(); ?>
	<?php if ($user->id > 0): ?>
        <div class="pull-right">
            <form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="login-form" class="form-vertical">
                <div class="logout-button">
                    <input type="submit" name="Submit" class="btn btn-primary"
                           value="<?php echo JText::_('JLOGOUT'); ?>"/>
                    <input type="hidden" name="option" value="com_users"/>
                    <input type="hidden" name="task" value="user.logout"/>
                    <input type="hidden" name="return" value="<?php echo $return; ?>"/>
					<?php echo JHtml::_('form.token'); ?>
                </div>
            </form>
        </div>
	<?php endif; ?>
<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-myprofile-top'); ?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item article__item--shift">
            <div class="j2store">
                <div class="j2store-order j2store-myprofile">
					<?php if ($messages_above_profile_html != '')
					{
						?>
                        <div class="j2store-myprofile-addtional_messages">
							<?php
							echo $messages_above_profile_html;
							?>
                        </div>
						<?php
					} ?>
                    <div class="tabs">
                        <div class="tab">
                            <a class="tab-button" href="#"><?php echo JText::_('J2STORE_MYPROFILE_ORDERS'); ?></a>
                            <div class="tab-content">
								<?php echo J2Store::modules()->loadposition('j2store-myprofile-order'); ?>
                                <div class="table-responsive">
									<?php echo $this->loadTemplate('orders'); ?>
                                </div>
                            </div>
                        </div>
						<?php if ($this->params->get('download_area', 1)): ?>
                            <div class="tab">
                                <a class="tab-button"
                                   href="#"><?php echo JText::_('J2STORE_MYPROFILE_DOWNLOADS'); ?></a>
                                <div class="tab-content">
									<?php echo J2Store::modules()->loadposition('j2store-myprofile-download'); ?>
                                    <div class="<?php echo $J2gridCol; ?>12">
                                        <div class="table-responsive">
											<?php echo $this->loadTemplate('downloads'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
						<?php endif; ?>
						<?php if ($this->user->id) : ?>
                            <div class="tab">
                                <a class="tab-button" href="#"><?php echo JText::_('J2STORE_MYPROFILE_ADDRESS'); ?></a>
                                <div class="tab-content">
									<?php echo J2Store::modules()->loadposition('j2store-myprofile-address'); ?>
                                    <div class="<?php echo $J2gridCol; ?>12">
										<?php echo $this->loadTemplate('addresses'); ?>
                                    </div>
                                </div>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
			<?php echo J2Store::modules()->loadposition('j2store-myprofile-bottom'); ?>

            <script>
                (function () {
                    'use strict'

                    let tabsClass = 'tabs'
                    let tabClass = 'tab'
                    let tabButtonClass = 'tab-button'
                    let activeClass = 'active'

                    /* Activates the chosen tab and deactivates the rest */
                    function activateTab(chosenTabElement) {
                        let tabList = chosenTabElement.parentNode.querySelectorAll('.' + tabClass)
                        for (let i = 0; i < tabList.length; i++) {
                            let tabElement = tabList[i]
                            if (tabElement.isEqualNode(chosenTabElement)) {
                                tabElement.classList.add(activeClass)
                            } else {
                                tabElement.classList.remove(activeClass)
                            }
                        }
                    }

                    /* Initialize each tabbed container */
                    let tabbedContainers = document.body.querySelectorAll('.' + tabsClass)
                    for (let i = 0; i < tabbedContainers.length; i++) {
                        let tabbedContainer = tabbedContainers[i]

                        /* List of tabs for this tabbed container */
                        let tabList = tabbedContainer.querySelectorAll('.' + tabClass)

                        /* Make the first tab active when the page loads */
                        activateTab(tabList[0])

                        /* Activate a tab when you click its button */
                        for (let i = 0; i < tabList.length; i++) {
                            let tabElement = tabList[i]
                            let tabButton = tabElement.querySelector('.' + tabButtonClass)
                            tabButton.addEventListener('click', function (event) {
                                event.preventDefault()
                                activateTab(event.target.parentNode)
                            })
                        }

                    }

                })()

            </script>
        </div>
    </div>
</section>
