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
		// Balloon
		$('body').on('click', '[data-prototype-shortcodes-show-balloon]', function () {
			var item = $(this),
				id = $(item).data('prototype-shortcodes-show-balloon'),
				shortcodesElement = $('[data-prototype-item="' + id + '"]');
			$(shortcodesElement).attr('data-viewed', 'true');
			getBalloon(id);
		});

		function getBalloon(id) {
			var ajaxData = [];
			ajaxData.push({name: 'item_id', value: id});
			var container = $('[data-prototype-shortcodes-balloon]'),
				content = $(container).find('[data-prototype-shortcodes-balloon-content]'),
				loading = $(container).find('[data-prototype-shortcodes-balloon-loading]'),
				error = $(container).find('[data-prototype-shortcodes-balloon-error]');
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/index.php?option=com_prototype&task=items.getBalloon',
				cache: false,
				data: ajaxData,
				beforeSend: function () {
					$(content).html('');
					$(error).hide();
					$(loading).show();
					showPrototypeShortcodesBalloon();
				},
				complete: function () {
					$(loading).hide();
				},
				success: function (response) {
					if (response.success) {
						var data = response.data;
						$(content).html(data.html);
					}
					else {
						$(error).show();
					}
				},
				error: function () {
					$(error).show();
				}
			});
		}

		// Author
		$('body').on('click', '[data-prototype-shortcodes-show-author]', function () {
			var item = $(this),
				id = $(item).data('prototype-shortcodes-show-author'),
				shortcodesElement = $('[data-prototype-item="' + id + '"]');
			$(shortcodesElement).attr('data-viewed', 'true');
			getAuthor(id);
		});

		function getAuthor(id) {
			var ajaxData = [];
			ajaxData.push({name: 'item_id', value: id});
			var container = $('[data-prototype-shortcodes-author]'),
				content = $(container).find('[data-prototype-shortcodes-author-content]'),
				loading = $(container).find('[data-prototype-shortcodes-author-loading]'),
				error = $(container).find('[data-prototype-shortcodes-author-error]');
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/index.php?option=com_prototype&task=items.getAuthor',
				cache: false,
				data: ajaxData,
				beforeSend: function () {
					$(content).html('');
					$(error).hide();
					$(loading).show();
					showPrototypeShortcodesAuthor();
				},
				complete: function () {
					$(loading).hide();
				},
				success: function (response) {
					if (response.success) {
						var data = response.data;
						$(content).html(data.html);
					}
					else {
						$(error).show();
					}
				},
				error: function () {
					$(error).show();
				}
			});
		}
	});
})(jQuery);