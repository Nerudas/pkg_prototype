<?php
/**
 * @package    Prototype Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

?>
<style>
	[data-prototype-item][data-viewed="true"] {
		opacity: 0.3;
	}
</style>
<div class="items">
	<?php if ($this->items): ?>
		<?php foreach ($this->items as $item):?>
			<div class="item" data-prototype-item="<?php echo $item->id; ?>">
				<h2><a data-prototype-show="<?php echo $item->id; ?>"><?php echo $item->title; ?></a></h2>
				<div class="uk-text-muted uk-text-small">

				</div>
			</div>
			<hr data-prototype-item="<?php echo $item->id; ?>">
		<?php endforeach; ?>
	<?php endif; ?>
</div>


