/*
 * @package    Prototype Component
 * @version    1.3.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('[data-filter-payment_number]').on('click', function () {
			var value = $(this).data('filter-payment_number');
			$('#filter_payment_number').val(value);
			$('#adminForm').submit();
		});
	});
})(jQuery);