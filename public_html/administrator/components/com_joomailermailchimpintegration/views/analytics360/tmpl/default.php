<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

$this->session->clear('gtoken');

// no direct access
defined('_JEXEC') or die('Restricted Access'); ?>

<?php echo $this->sidebar; ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="1" />
    <input type="hidden" name="controller" value="campaigns" />
</form>
<div class="wrap a360-wrap">
	<h2 class="a360-head">
		<div class="a360-header-links">
			<a href="http://analytics.google.com" target="_blank">visit Google Analytics &raquo;</a> |
			<a href="http://www.mailchimp.com/signup/?pid=joomailer&source=website" target="_blank">visit MailChimp &raquo;</a>
		</div>
		<?php echo JText::_('Analytics360°'); ?>
	</h2>
	<p class="a360-subhead" id="a360-header-credit">compliments of <a class="a360-mailchimp-link" href="http://mailchimp.com/?pid=joomailer&source=website" target="_blank"><span>MailChimp</span></a></p>
	<p id="a360-notification"><?php /*echo $notification;*/ ?></p>

<?php /*<div class="wrap">*/?>

	<?php if (!$this->session->has('gtoken')) : ?>
		<h2>Feed The Chimp!</h2>
		<p>
            <?php
            echo JText::_('JM_NO_ANALYTICS_LOGIN_SUPPLIED');
			return;
            ?>
		</p>
	<?php endif; ?>

	<div id="a360-datepicker">
		<div id="a360-datepicker-pane" style="display:none;">
			<div id="a360-datepicker-calendars"></div>
			<input type="submit" id="a360-apply-date-range" class="button" value="Apply" />
			<div id="a360-current-date-range-desc"></div>
		</div>
		<div id="a360-datepicker-popup">
			<div id="a360-current-date-range">
				<div id="a360-current-start-date"><input style="display:none;" type="text" /><span>Loading</span></div> -
				<div id="a360-current-end-date"><input style="display:none;" type="text" /><span></span></div>
			</div>
		</div>
	</div>

	<div class="a360-box" id="a360-box-site-traffic">
		<div class="a360-box-header"><h3>Site Traffic</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<ul id="a360-linechart-legend">
				<li class="blog-post" style="display:none;">blog post</li>
				<li class="campaign" style="display:none;">email campaign</li>
			</ul>
			<ul class="a360-tabs left">
			</ul>
			<ul class="a360-tab-contents border">
				<li id="a360-all-traffic-container">
					<div id="a360-all-traffic-graph">
					</div>
				</li>
				<li id="a360-campaign-traffic-container" style="display:none">
				</li>
			</ul>
			<div class="a360-stats-container">
				<dl class="a360-stats-list" style="display:none;">
					<dt>Visits</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-visits-spark"></div>
						<div class="a360-stat" id="a360-stat-visits"></div>
					</dd>
					<dt>Pageviews</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-pageviews-spark"></div>
						<div class="a360-stat" id="a360-stat-pageviews"></div>
					</dd>
					<dt>Pages/Visit</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-pages-per-visit-spark"></div>
						<div class="a360-stat" id="a360-stat-pages-per-visit"></div>
					</dd>
				</dl>
			</div>
			<div class="a360-stats-container">
				<dl class="a360-stats-list" style="display:none;">
					<dt>Bounce Rate</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-bounce-rate-spark"></div>
						<div class="a360-stat" id="a360-stat-bounce-rate"></div>
					</dd>
					<dt>Avg. Time on Site</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-time-on-site-spark"></div>
						<div class="a360-stat" id="a360-stat-time-on-site"></div>
					</dd>
					<dt>% New Visits</dt>
					<dd>
						<div class="a360-stat-spark" id="a360-stat-new-visits-spark"></div>
						<div class="a360-stat" id="a360-stat-new-visits"></div>
					</dd>
				</dl>
			</div>
		</div>
	</div>

	<div class="a360-box" id="a360-box-traffic-by-region">
		<div class="a360-box-header"><h3>Traffic By Region</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<div id="a360-geo-map"></div>
		</div>
	</div>
	<div class="a360-box half" id="a360-box-referring-traffic-overview">
		<div class="a360-box-header"><h3>Referring Traffic Overview</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<div id="a360-referring-traffic-overview-legend"></div>
			<div id="a360-referring-traffic-chart"></div>
		</div>
	</div>
	<div class="a360-box half" id="a360-box-list-growth">
		<div class="a360-box-header"><h3>List Growth</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<div id="a360-list-growth-chart"></div>
			<?php if (count($this->a360_list_options)) : ?>
			<select id="a360-list-growth-list-id">
				<?php echo implode("\n", $this->a360_list_options); ?>
			</select>
			<?php else : ?>
				<h4>No Lists Found</h4>
			<?php endif; ?>
		</div>
	</div>
	<div class="a360-box" id="a360-box-top-referrers">
		<div class="a360-box-header"><div class="a360-breadcrumbs"></div><h3>Top Referrers</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<div class="a360-table-container" id="a360-top-referrers"></div>
		</div>
	</div>
	<div class="a360-box" id="a360-box-top-content">
		<div class="a360-box-header"><div class="a360-breadcrumbs"></div><h3>Top Content</h3><div class="a360-box-status"></div></div>
		<div class="a360-box-content">
			<div class="a360-table-container" id="a360-top-content"></div>
		</div>
	</div>
</div>
<?php echo $this->sidebar ? '</div>' : ''; ?>
