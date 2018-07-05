/*
 * @package    Prototype Component
 * @version    1.0.7
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		var joomlaParams = Joomla.getOptions('prototypeList', '');
		// Item Click
		$('body').on('click', '[data-prototype-show]', function () {
			var item = $(this),
				id = $(item).data('prototype-show'),
				listElement = $('[data-prototype-item="' + id + '"]');
			$(listElement).attr('data-viewed', 'true');
			getBalloon(id);
		});

		// Get balloon
		function getBalloon(id) {
			var ajaxData = [];
			ajaxData.push({name: 'id', value: joomlaParams.catid});
			ajaxData.push({name: 'item_id', value: id});
			var container = $('[data-prototype-balloon]'),
				content = $(container).find('[data-prototype-balloon-content]'),
				loading = $(container).find('[data-prototype-balloon-loading]'),
				error = $(container).find('[data-prototype-balloon-error]');
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
					showPrototypeListBalloon();
				},
				complete: function () {
					$(loading).hide();
				},
				success: function (response) {
					if (response.success) {
						var data = response.data;
						$(content).html(data.balloon);
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