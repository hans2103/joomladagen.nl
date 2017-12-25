<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;
?>
<div class="span10">
	<?php echo $this->pspForm->renderFieldset('abn-lite'); ?>
</div>
<script type="text/javascript">
	function setTestserver(value)
	{
		if (value == '1')
		{
			document.adminForm.jform_merchantId.value = 'TESTiDEALEASY';
		}
		else
		{
			document.adminForm.jform_merchantId.value = '';
		}
	}
</script>
