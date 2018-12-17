/*
 * @package    Prototype Component
 * @version    1.4.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */


(function ($) {
	$(document).ready(function () {
		$('[data-input-presetsconfig]').each(function () {
			// Elements
			var field = $(this),
				id = field.attr('id'),
				blank = field.find('.item[data-key="preset_X"]'),
				result = field.find('#' + id + '_result'),
				form = field.closest('form');

			// Fix selector
			if (!field.selector) {
				field.selector = '#' + field.attr('id');
			}

			// Add preset
			addPreset();
			$('body').on('click', field.selector + ' .actions .add', function () {
				addPreset();
			});

			// Remove preset
			$('body').on('click', field.selector + ' .actions .remove', function () {
				$(this).closest('.item').remove();
				if (result.find('.item').length == 0) {
					addPreset();
				}
				reIndex();
			});

			// Move preset
			$(result.selector).sortable({
				handle: ".actions .move",
				start: function (event, ui) {
					result.addClass('sortable');
				},
				stop: function (event, ui) {
					reIndex();
					result.removeClass('sortable');
				}
			});

			// Remove empty presets
			$(form).on('submit', function () {
				result.find('[name*="value"]').each(function (i, input) {
					if ($(input).val() == '') {
						$(input).closest('.item').remove();
						reIndex();
					}
				});
			});

			// Add preset function
			function addPreset() {

				console.log(blank);
				var newRow = blank.clone();
				$(newRow).find('input').each(function (i, input) {
					$(input).attr('id', $(input).data('id'));
					$(input).attr('name', $(input).data('name'));
					$(input).removeAttr('data-id');
					$(input).removeAttr('data-name');
				});
				$(newRow).appendTo(result);
				reIndex();


			}

			// Reindex function
			function reIndex() {
				result.find('.item').each(function (i, item) {
					var i_key = $(item).attr('data-key');
					var i_prefix = 'preset';
					var i_pattern = new RegExp(i_key, 'g');
					$(item).find('[id*="' + i_prefix + '_"]').each(function (a, input) {
						var a_id = $(input).attr('id').replace(i_pattern, i_prefix + '_' + (i + 1));
						$(input).attr('id', a_id);
					});
					$(item).find('[name*="' + i_prefix + '_"]').each(function (a, input) {
						var a_name = $(input).attr('name').replace(i_pattern, i_prefix + '_' + (i + 1));
						$(input).attr('name', a_name);
					});
					$(item).attr('data-key', i_prefix + '_' + (i + 1));
				});
			}
		});
	});
})(jQuery);