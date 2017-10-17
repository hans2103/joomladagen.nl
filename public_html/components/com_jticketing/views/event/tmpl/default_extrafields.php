<?php
/**
* @version     1.5
* @package     com_jticketing
* @copyright   Copyright (C) 2014. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
* @author      Techjoomla <extensions@techjoomla.com> - http://techjoomla.com
*/
// no direct access
defined('_JEXEC') or die;

if(count($this->extraData)): ?>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<table class="table table-striped table-bordered table-hover">
				<?php
				foreach($this->extraData as $f):
				?>
					<tr>
						<td>
							<strong><?php echo $f->label;?></strong>
						</td>

						<td>
							<?php
							if (!is_array($f->value)):
								echo $f->value;
							else:
								foreach($f->value as $option):
									echo $option->options;
									?>
									<br/>
									<?php
								endforeach;
							endif;
							?>
						</td>
					</tr>
				<?php
				endforeach;
				?>
			</table>
		</div>
	</div><!--row-->
<?php
endif;
