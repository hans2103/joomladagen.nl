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

if ($this->canEdit)
{
    JHtml::_('bootstrap.framework');
    JHtml::_('bootstrap.loadCss');
    JHtml::_('bootstrap.tooltip');
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('#xmap i[class*="icon-"]').css('cursor', 'pointer').on('click', function () {
                var $this = $(this);

                $.ajax({
                    url: '<?php echo JUri::root(); ?>index.php',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        option: 'com_xmap',
                        format: 'json',
                        task: 'sitemap.editElement',
                        action: 'toggleElement',
                        id: $this.data('id'),
                        uid: $this.data('uid'),
                        itemid: $this.data('itemid'),
                        lang: '<?php echo XmapHelper::getLanguageCode(); ?>',
                        '<?php echo JSession::getFormToken(); ?>': 1
                    },
                    success: function (response) {
                        $this.removeClass('icon-remove-sign icon-ok-sign');
                        $this.attr('title', response.message).tooltip('fixTitle').tooltip('show');

                        if (response.success) {
                            $this.addClass(response.data.state ? 'icon-ok-sign' : 'icon-remove-sign');
                        } else {
                            $this.addClass('icon-circle-question-mark');
                        }
                    },
                    error: function (data) {
                        console.log(data);
                        $this.addClass('icon-circle-question-mark');
                    }
                });
            });
        });
    </script>
<?php } ?>