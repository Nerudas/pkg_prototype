/*
 * @package    Prototype Component
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		var imagesCount = 0;
		setInterval(function () {
			var newImagesCount = $('#jform_images_result').find('.item').length;
			if (imagesCount != newImagesCount) {
				imagesCount = newImagesCount;
				$('#item-form').trigger('change');
			}
		}, 10);
	});
})(jQuery);