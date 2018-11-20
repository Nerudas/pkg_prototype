/*
 * @package    Prototype Component
 * @version    1.3.5
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		setPreset();

		$('[data-preset]').on('click', function () {
			$('#jform_preset').val($(this).data('preset'));
			setPreset()
		});

		$('[data-prototype-form="change-preset"]').on('click', function () {
			$('#jform_preset').val('');
			setPreset()
		});

		function setPreset() {
			var key = $('#jform_preset').val(),
				preset = $('[data-preset="' + key + '"]'),
				title = preset.data('preset-title'),
				price = preset.data('preset-price'),
				price_title = preset.data('preset-price_title'),
				delivery = preset.data('preset-delivery'),
				delivery_title = preset.data('preset-delivery_title'),
				object = preset.data('preset-object'),
				object_title = preset.data('preset-object_title');

			if (title != undefined && title != '') {
				$('[data-preset-title="label"]').text(title);
			}
			else {
				$('[data-preset-title="label"]').text('');
			}

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
				$('[data-preset-price="label"]').text('');
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

			if (preset.length > 0) {
				$('[data-prototype-form="form"]').show();
				$('[data-prototype-form="presets"]').hide();
			}
			else {
				$('[data-prototype-form="form"]').hide();
				$('[data-prototype-form="presets"]').show();
			}
			if ($('#jform_title').val() == '') {
				$('#jform_title').val(title);
			}

			$('#jform_preset').trigger('change');

		}
	});
})(jQuery);