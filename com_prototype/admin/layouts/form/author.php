<?php
/**
 * @package    Prototype Component
 * @version    1.3.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   \Joomla\Registry\Registry $author Author data
 * @var   \Joomla\CMS\Form\Form     $form   Author data
 */

HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'media/com_prototype/js/form-author.min.js', array('version' => 'auto'));
?>
<div data-prototype-form="author">
	<div data-author-phones="free" style="display: none;">
		<div>
			<?php echo '<pre>', print_r($author->get('siteContacts')->phones, true), '</pre>'; ?>
		</div>
	</div>
	<div data-author-phones="payment" style="display: none;">

		<div><?php echo Text::_('COM_PROTOTYPE_ITEM_PAYMENT_TEXT'); ?></div>

		<?php echo '<pre>', print_r($author->get('contacts')->phones, true), '</pre>'; ?>
	</div>
	<?php echo $form->renderField('payment'); ?>
</div>