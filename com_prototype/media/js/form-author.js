/*
 * @package    Prototype Component
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('[name="jform[payment]"]:radio').change(function () {
			console.log('change');
			showAuthorPhones();
		});
		showAuthorPhones();

		function showAuthorPhones() {
			var payment = $('[data-prototype-form="author"]').find('[data-author-phones="payment"]'),
				free = $('[data-prototype-form="author"]').find('[data-author-phones="free"]'),
				value = $('[name="jform[payment]"]:checked').val();
			$(payment).hide();
			$(free).hide();

			console.log(value);

			if (value == 1) {
				$(payment).show();
			}
			else {
				$(free).show();
			}
		}
	});
})(jQuery);