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
		var presetsIcon = [];

		function presets() {
			$('[data-input-preset-icon]').each(function () {
				var filename = $(this).data('filename');
				if (filename == '') {
					filename = generatePresetPlacemarkFilename($(this).data('filename'));
				}
				$(this).data('filename', filename);
				$(this).attr('data-filename', filename);
				presetsIcon.push(filename);


				let field = $(this),
					form = field.find('.form'),
					input = form.find('input[type="file"]'),
					image = form.find('img'),
					value = form.find('.value');

				let folder_field = $('#jform_images_folder'),
					folder = $(folder_field).val() + '/presets';

				if (folder_field.length === 0 || folder === '') {
					$(field).remove();
				}
				else {
					getImage();
				}


				// Upload
				input.on('change', function (e) {
					if (!form.hasClass('disable')) {
						uploadImage(e.target.files);
					}
				});

				// Upload image function
				function uploadImage(files) {
					let ajaxData = new FormData();
					ajaxData.append('type', 'image');
					ajaxData.append('folder', folder);
					ajaxData.append('filename', $(field).data('filename'));
					ajaxData.append('noimage', '0');
					ajaxData.append('files[]', files[0]);

					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'index.php?option=com_ajax&plugin=files&group=fieldtypes&format=json&task=uploadFile',
						processData: false,
						contentType: false,
						cache: false,
						global: false,
						async: false,
						data: ajaxData,
						beforeSend: function () {
							$(form).addClass('disable');
						},
						complete: function () {
							$(form).removeClass('disable');
						},
						success: function (response) {
							if (response.success) {
								if (response.data !== '0') {
									$(image).attr('src', '/' + response.data);
									$(value).val(response.data)
								}
								else {
									$(image).attr('src', '');
									$(value).val('');
								}
							}
							else {
								console.error(response.message);
							}
						},
						error: function (response) {
							console.error(response.status + ': ' + response.statusText);
						}
					});
				}

				// Get image function
				function getImage() {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'index.php?option=com_ajax&plugin=files&group=fieldtypes&format=json&task=getFile',
						cache: false,
						global: false,
						async: false,
						data: {
							type: 'image',
							folder: folder,
							filename: $(field).data('filename'),
							noimage: 0,
						},
						beforeSend: function () {
							$(form).addClass('disable');
						},
						complete: function () {
							$(form).removeClass('disable');
						},
						success: function (response) {
							if (response.success) {
								if (response.data != 0) {
									$(image).attr('src', '/' + response.data);
									$(value).val(response.data)
								}
								else {
									$(image).attr('src', '');
									$(value).val('');
								}
							}
							else {
								$(image).attr('src', '');
								$(value).val('');
							}
						},
						error: function (response) {
							console.error(response.status + ': ' + response.statusText);
						}
					});
				}

				// Remove image function
				$(form).find('a.remove').on('click', function () {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'index.php?option=com_ajax&plugin=files&group=fieldtypes&format=json&task=deleteFile',
						cache: false,
						global: false,
						async: false,
						data: {
							type: 'image',
							folder: folder,
							filename: $(field).data('filename'),
							noimage: '0',
						},
						beforeSend: function () {
							$(form).addClass('disable');
						},
						complete: function () {
							$(form).removeClass('disable');
						},
						success: function (response) {
							if (response.success) {
								if (response.data !== '0') {
									$(image).attr('src', '/' + response.data);
									$(value).val(response.data)
								}
								else {
									$(image).attr('src', '');
									$(value).val('');
								}
							}
							else {
								$(image).attr('src', '');
								$(value).val('');
							}
						},
						error: function (response) {
							console.error(response.status + ': ' + response.statusText);
						}
					});
				});
			});
		}

		function generatePresetPlacemarkFilename(filename) {
			if (filename == '' || jQuery.inArray(filename, presetsIcon) >= 0) {
				let a = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'.split(''),
					b = [];
				for (let i = 0; i < 7; i++) {
					let j = (Math.random() * (a.length - 1)).toFixed(0);
					b[i] = a[j];
				}

				filename = b.join('');
				generatePresetPlacemarkFilename(filename);
			}
			return filename;
		}

		presets();

		$(document).on('subform-row-add', function (event, row) {
			if ($(row).data('base-name') == 'presets') {
				presets();
			}
		});
		$(document).on('subform-row-remove', function (event, row) {
			if ($(row).data('base-name') == 'presets') {
				$(row).find('[data-input-preset-icon]').find('a.remove').trigger('click');
			}
		});
	});
})(jQuery);