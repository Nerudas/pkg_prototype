/*
 * @package    Prototype Component
 * @version    1.4.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		var form = $('#importForm'),
			stages_group = $(form).find('.control-group.stages'),
			file_group = $(form).find('.control-group.file'),
			run_group = $(form).find('.control-group.run'),
			ajax_url = $(form).attr('action');

		$(stages_group).hide();
		$(stages_group).find('.error').hide();
		$(stages_group).find('.success').hide();
		$(run_group).show();
		$(file_group).show();

		$(form).on('submit', function () {
			$(stages_group).find('.error').hide();
			$(stages_group).find('.success').hide();
			$(stages_group).find('.status').hide();
			$(stages_group).show();
			$(run_group).hide();
			$(file_group).hide();

			stage1();
			return false;
		});

		function stage1() {
			var stage = $(stages_group).find('[data-stage="1"]'),
				status = $(stage).find('.status'),
				error = $(stage).find('.error'),
				success = $(stage).find('.success'),
				current_cont = $(status).find('.current'),
				total_cont = $(status).find('.total');

			$(current_cont).text('0');
			$(total_cont).text('1');
			$(error).hide();
			$(success).hide();
			$(status).show();

			var ajaxData = new FormData();
			ajaxData.append('task', 'import.prepareFile');
			ajaxData.append('files[]', $(form).find('input[type=file]').prop('files')[0]);

			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajax_url,
				processData: false,
				contentType: false,
				cache: false,
				global: false,
				async: false,
				data: ajaxData,
				success: function (response) {
					if (response.success) {
						$(current_cont).text(1);
						$(success).show();
						$(status).hide();
						stage2(response.data);
					} else {
						console.error(response.message);
						$(error).show();
						$(status).hide();
						$(run_group).show();
						$(file_group).show();
					}
				},
				error: function (response) {
					console.error(response.status + ': ' + response.statusText);
					$(error).show();
					$(status).hide();
					$(run_group).show();
					$(file_group).show();
				}
			});
		}

		function stage2(data) {
			var stage = $(stages_group).find('[data-stage="2"]'),
				status = $(stage).find('.status'),
				error = $(stage).find('.error'),
				success = $(stage).find('.success'),
				current_cont = $(status).find('.current'),
				total_cont = $(status).find('.total');

			if (data.offset < data.total) {
				$(current_cont).text(data.offset);
				$(total_cont).text(data.total);
				$(error).hide();
				$(success).hide();
				$(status).show();

				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajax_url,
					cache: false,
					global: false,
					async: false,
					data: {
						'task': 'import.importItems',
						'folder': data.folder,
						'offset': data.offset,
						'limit': data.limit,
						'total': data.total,
					},
					success: function (response) {
						if (response.success) {
							var data = response.data;
							$(current_cont).text(data.offset);
							stage2(response.data);
						} else {
							console.error(response.message);
							$(error).show();
							$(status).hide();
							$(run_group).show();
							$(file_group).show();
						}
					},
					error: function (response) {
						console.error(response.status + ': ' + response.statusText);
						$(error).show();
						$(status).hide();
						$(run_group).show();
						$(file_group).show();
					}
				});
			} else {
				$(success).show();
				$(status).hide();
				stage3(data);
			}
		}

		function stage3(data) {
			var stage = $(stages_group).find('[data-stage="3"]'),
				status = $(stage).find('.status'),
				error = $(stage).find('.error'),
				success = $(stage).find('.success'),
				current_cont = $(status).find('.current'),
				total_cont = $(status).find('.total');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajax_url,
				cache: false,
				global: false,
				async: false,
				data: {
					'task': 'import.clear',
					'folder': data.folder,
				},
				success: function (response) {
					if (response.success) {
						var data = response.data;
						$(current_cont).text('1');
						$(success).show();
						$(status).hide();
						$(run_group).show();
						$(file_group).show();
					} else {
						console.error(response.message);
						$(error).show();
						$(status).hide();
						$(run_group).show();
						$(file_group).show();
					}
				},
				error: function (response) {
					console.error(response.status + ': ' + response.statusText);
					$(error).show();
					$(status).hide();
					$(run_group).show();
					$(file_group).show();
				}
			});


		}
	});
})(jQuery);