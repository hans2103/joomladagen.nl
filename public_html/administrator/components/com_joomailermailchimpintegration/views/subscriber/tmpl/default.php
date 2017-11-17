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

// no direct access
defined('_JEXEC') or die('Restricted Access');

echo $this->sidebar; ?>
<form action="#" method="post" id="adminForm" name="adminForm">
    <div id="userdiv" class="clearfix">
        <div id="avatarContainer" class="left">
            <div id="avatarContainerInner">
                <?php if ($this->avatar) { ?>
                    <img src="<?php echo $this->avatar; ?>" alt="">
                <?php } ?>
                <?php if($this->kloutScore !== false) : ?>
                <div id="kloutScore">
                    <a href="http://klout.com/<?php echo $this->twitterName; ?>" target="_blank">
                            <span><?php echo $this->kloutScore; ?></span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="userInfo" class="left">
            <div id="username"><?php echo $this->user->username; ?></div>
            <table class="usertable">
                <tbody>
                    <tr>
                        <td class="lbl">
                            <?php echo JText::_('Email');?>:
                        </td>
                        <td >
                            <a href="mailto:<?php echo $this->user->email;?>"><?php echo $this->user->email; ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">
                            <?php echo JText::_('Joined'); ?>:
                        </td>
                        <td>
                            <?php echo $this->user->registerDate; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">
                            Last login:
                        </td>
                    <td>
                        <?php echo $this->user->lastvisitDate?>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="right">
            <div id="socialStuff" class="clearfix">
                <div id="hotness" class="left">
                    <?php echo JText::_('JM_HOTNESS_RATING'); ?>
                    <span id="hotnessRatingStars" style="width:<?php echo (12 * $this->hotnessRating); ?>px"></span>
                </div>
                <div class="ratings right">
                    <?php echo JText::_('JM_MEMBER_RATING'); ?>
                    <span class="ratingBG">
                        <span class="rating-value" style="width:<?php echo round($this->memberInfo['member_rating'] * 20); ?>%"></span>
                    </span>
                </div>
            </div>


            <?php
            $panels = array('Social', 'JomSocial');
            $options = array(
                'active' => 'socialTab_0'
            );
            echo JHtml::_('bootstrap.startTabSet', 'socialTabs', $options);
            foreach ($panels as $index => $panel) {
                echo JHtml::_('bootstrap.addTab', 'socialTabs', 'socialTab_' . $index, $panel);
                require_once(__DIR__ . '/' . strtolower($panel) . '.php');
                echo JHtml::_('bootstrap.endTab');
            }
            echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
    </div>

    <div>
        <table class="adminlist">
            <thead>
                <th width="10"></th>
                <th>Item</th>
                <th>Date</th>
                <th>Cost</th>
                <th>Product Category</th>
            </thead>
            <tbody>
                <?php if(count($this->hotActivity)) : ?>
                    <?php foreach($this->hotActivity as $key => $hotActivity) : ?>
                        <tr>
                            <td></td>
                            <td align="center">
                                <?php echo $hotActivity->title; ?>
                            </td>
                            <td  align="center">
                                <?php echo $hotActivity->crdate; ?>
                            </td>
                            <td  align="center">
                                <?php echo $hotActivity->price; ?>
                            </td>
                            <td  align="center"><?php echo $hotActivity->joomailerProductCategory; ?></td>
                        </tr>
                    <?php endforeach;    ?>
                <?php else : ?>
                        <tr>
                            <td colspan="7">This user was lazy. He didn't do anything...</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="10">#</th>
                <th nowrap="nowrap">
                    <?php echo JText::_( 'Newsletters Sent' ); ?>
                </th>
                <th width="70" nowrap="nowrap">
                    <?php echo JText::_('JM_OPENED'); ?>
                </th>
                <th width="70" nowrap="nowrap">
                    <?php echo JText::_('JM_CLICKS'); ?>
                </th>
                <th width="20" nowrap="nowrap">
                    <?php echo JText::_( 'Segments' ); ?>
                </th>
                <th width="20" nowrap="nowrap">
                    <?php echo JText::_('JM_SENT_DATE'); ?>
                </th>
                <th width="20" nowrap="nowrap">
                    <?php echo JText::_('JM_SUBSCRIBED_DATE')?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
        $limit = count($this->stats);
        $k = $this->limitstart;
        $i = 0;
        foreach ($this->stats as $row) {
            if( isset($row['received']) && $row['received']) {
                $img = '<img src="' . JURI::root(true) . '/media/com_joomailermailchimpintegration/backend/images/tick.png"/>';
            } else {
                $img = '<img src="' . JURI::root(true) . '/media/com_joomailermailchimpintegration/backend/images/cross.png"/>';
            } ?>
            <tr class="<?php echo "row$k"; ?>">
                <td align="center">
                    <?php echo $i+1+$this->limitstart; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo $row['title']; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo $row['opens']; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo $row['clicks']; ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo ($row['segment_text'] ? $row['segment_text'] : '-'); ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo JHTML::_('date', strtotime($row['send_time']), JText::_('DATE_FORMAT_LC2')); ?>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php echo JHTML::_('date', strtotime($row['list_sub']), JText::_('DATE_FORMAT_LC2')); ?>
                </td>
            </tr>
            <?php
            $i++;
            $k = 1 - $k;
        } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="15">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="controller" value="subscribers" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
