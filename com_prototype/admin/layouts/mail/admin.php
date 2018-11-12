<?php
/**
 * @package    Prototype Component
 * @version    1.3.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="body" style="font-family: sans-serif; font-size: 18px;">
	<table style="border-collapse: collapse; border-spacing: 0; width: 100%; margin: 15px 0; border: 1px solid #ddd;">
		<tr style="background: #fafafa;">
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo Text::_('JGLOBAL_TITLE'); ?>
			</td>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<a href="<?php echo $item->adminLink; ?>"><?php echo $item->title; ?></a>
			</td>
		</tr>
		<tr>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo Text::_('COM_PROTOTYPE_CATEGORY'); ?>
			</td>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo $category->title; ?></td>
		</tr>
		<tr style="background: #fafafa;">
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo Text::_('JAUTHOR'); ?></td>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo $author->get('name'); ?>
				(<?php echo $author->get('email'); ?>)
			</td>
		</tr>
		<tr>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php echo Text::_('JSTATUS'); ?></td>
			<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
				<?php if ($item->state > 0): ?>
					<span style="color: green"><?php echo Text::_('JPUBLISHED'); ?></span>
				<?php else: ?>
					<span style="color: red;"><?php echo Text::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<?php if (!empty($item->extra)): ?>
			<?php $i = 0;
			foreach ($item->extra as $key => $value): ?>
				<tr <?php echo ($i % 2) ? '' : 'style="background: #fafafa;"'; ?>>
					<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
						<?php echo Text::_('COM_PROTOTYPE_ITEM_EXTRA_' . $key); ?>
					</td>
					<td style="padding: 8px 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top;">
						<?php echo $value; ?>
					</td>
				</tr>
				<?php $i++;
			endforeach; ?>
		<?php endif; ?>
	</table>
</div>