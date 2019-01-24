/*
 * @package    Prototype Component
 * @version    1.4.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('[name="jform[payment]"]:radio').change(function () {
			showAuthorPhones();
		});
		$('[name="jform[payment]"]:radio').on('click', function() {
			showAuthorPhones();
		});
		$('[name="jform[payment]"]:radio').on('change', function() {
			showAuthorPhones();
		});
		$('body').on('change', '[name="jform[payment]"]:radio',  function() {
			$('[name="jform[payment]"]:radio').change(function () {
				showAuthorPhones();
			});
		});
		showAuthorPhones();

		function showAuthorPhones() {
			var payment = $('[data-prototype-form="author"]').find('[data-author-phones="payment"]'),
				free = $('[data-prototype-form="author"]').find('[data-author-phones="free"]'),
				value = $('[name="jform[payment]"]:checked').val();
			$(payment).hide();
			$(free).hide();

			if (value == 1) {
				$(payment).show();
			}
			else {
				$(free).show();
			}
		}
	});
})(jQuery);