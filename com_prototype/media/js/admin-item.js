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
		$('#jform_preset').on('change', function () {
			setPreset()
		});

		setPreset();

		function setPreset() {
			var key = $('#jform_preset').val(),
				option = $('#jform_preset').find('option[value="' + key + '"]'),
				price = option.data('preset-price'),
				price_title = option.data('preset-price_title'),
				delivery = option.data('preset-delivery'),
				delivery_title = option.data('preset-delivery_title'),
				object = option.data('preset-object'),
				object_title = option.data('preset-object_title');


			if (price != undefined && price != '') {
				$('#jform_preset_price').val(price);
			}
			else {
				$('#jform_preset_price').val('null');
			}
			if (price_title != undefined && price_title != '') {
				$('[data-preset-price="label"]').text(price_title);
			}
			else {
				$('[data-preset-delivery="label"]').text('');
			}

			if (delivery != undefined && delivery != '') {
				$('#jform_preset_delivery').val(delivery);
			}
			else {
				$('#jform_preset_delivery').val('null');
			}
			if (delivery_title != undefined && delivery_title != '') {
				$('[data-preset-delivery="label"]').text(delivery_title);
			}
			else {
				$('[data-preset-delivery="label"]').text('');
			}

			if (object != undefined && object != '') {
				$('#jform_preset_object').val(object);
			}
			else {
				$('#jform_preset_object').val('null');
			}
			if (object_title != undefined && object_title != '') {
				$('[data-preset-object="label"]').text(object_title);
			}
			else {
				$('[data-preset-delivery="label"]').text('');
			}

		}
	});
})(jQuery);