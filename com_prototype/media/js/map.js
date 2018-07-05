/*
 * @package    Bulletin Board Component
 * @version    1.0.6
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		var mapContainer = $('[data-prototype-map]'),
			mapSelector = mapContainer.attr('id'),
			afterInit = $('[data-afterInit]'),
			afterInitShow = $('[data-afterInit="show"]'),
			afterInitHide = $('[data-afterInit="hide"]');
		afterInitShow.hide();
		afterInitHide.show();

		// Set Params
		var storageParams = localStorage.getItem('map'),
			joomlaParams = Joomla.getOptions('prototypeMap', '');
		if (storageParams) {
			storageParams = $.parseJSON(storageParams);
		}
		else {
			storageParams = {
				latitude: joomlaParams.latitude,
				longitude: joomlaParams.longitude,
				center: joomlaParams.center,
				zoom: joomlaParams.zoom
			};
			localStorage.setItem('map', JSON.stringify(storageParams));
		}

		if (joomlaParams.priority_center) {
			storageParams = $.parseJSON(localStorage.getItem('map'));
			if (joomlaParams.priority_center.zoom) {
				storageParams.zoom = joomlaParams.priority_center.zoom;
			}
			if (joomlaParams.priority_center.center) {
				storageParams.latitude = joomlaParams.priority_center.center[0] * 1;
				storageParams.longitude = joomlaParams.priority_center.center[1] * 1;
				storageParams.center = [storageParams.latitude, storageParams.longitude];
			}
			localStorage.setItem('map', JSON.stringify(storageParams));
		}

		var checkSize = setInterval(function () {
			if ($(mapContainer).width() > 0 && $(mapContainer).height() > 0) {
				clearInterval(checkSize);
				initializeMap();
			}
		}, 3);

		function initializeMap() {
			var mapParams = {
				center: storageParams.center,
				zoom: storageParams.zoom
			};

			ymaps.ready(function () {
				// Map object
				var map = new ymaps.Map(mapSelector, {
					center: mapParams.center,
					zoom: mapParams.zoom,
					controls: []
				});
				map.behaviors.disable("dblClickZoom");

				// Object Manager
				var objectManager = new ymaps.ObjectManager({clusterize: false});
				map.geoObjects.add(objectManager);

				// Items
				var itemsTotal = 0,
					itemsOffset = 0,
					totalRequest = false,
					itemsRequest = false,
					itemsViewed = [],
					itemList = $('[data-prototype-itemlist="items"]'),
					counterCurrent = $('[data-prototype-counter="current"]'),
					counterTotal = $('[data-prototype-counter="total"]');

				function startItemsRequests() {
					if (totalRequest) {
						totalRequest.abort();
					}
					if (itemsRequest) {
						itemsRequest.abort();
					}
					$(itemList).html('');
					$(counterCurrent).text(0);
					$(counterTotal).text(0);

					objectManager.removeAll();
					itemsTotal = 0;
					itemsOffset = 0;

					getBounds();
					getItems();
				}

				function getItems() {
					var ajaxData = $(filter).serializeArray();
					ajaxData.push({name: 'id', value: joomlaParams.catid});
					ajaxData.push({name: 'layout', value: joomlaParams.layout});
					ajaxData.push({name: 'view', value: 'map'});
					ajaxData.push({name: 'return_view', value: 'map'});
					$.each(bounds, function (key, value) {
						ajaxData.push({name: 'filter[coordinates][' + key + ']', value: value});
					});

					if (itemsTotal == 0) {
						$(counterCurrent).text(0);
						totalRequest = $.ajax({
							type: 'GET',
							dataType: 'json',
							cache: false,
							url: '/index.php?option=com_prototype&task=items.getTotal',
							data: ajaxData,
							success: function (response) {
								var total = response.data;
								itemsTotal = total;
								$(counterTotal).text(total);
								if (total > 0) {
									getItems();
								}
							}
						});
					}
					if (itemsTotal > 0) {
						ajaxData.push({name: 'limitstart', value: itemsOffset});
						itemsRequest = $.ajax({
							type: 'GET',
							dataType: 'json',
							cache: false,
							url: '/index.php?option=com_prototype&task=items.getPlacemarks',
							data: ajaxData,
							success: function (response) {
								if (response.success) {
									var data = response.data,
										placemarks = data.placemarks;

									$.each(placemarks, function (key, placemark) {
										var object = {
											type: 'Feature',
											id: placemark.id,
											geometry: {
												type: 'Point',
												coordinates: $.parseJSON(placemark.coordinates)
											},
											options: {}
										};
										$.each(placemark.options, function (key, value) {
											if (key == 'customLayout') {
												if ($.inArray(placemark.id * 1, itemsViewed) !== -1) {
													var clone = $(value).clone(),
														html = '';
													clone.filter('[data-prototype-placemark]').attr('data-viewed', 'true');
													$.each(clone, function (key) {
														var outerHTML = clone[key].outerHTML;
														if (outerHTML != '' && outerHTML != undefined) {
															html += clone[key].outerHTML;
														}
													});
													value = html;
												}
												object.options[key] = value;
												key = 'iconLayout';
												value = ymaps.templateLayoutFactory.createClass(value);
											}

											if ($.inArray(placemark.id * 1, itemsViewed) !== -1 && key == 'iconShape') {
												value.coordinates = value.coordinates_viewed;
											}

											object.options[key] = value;
										});
										objectManager.add(object);

									});

									$(data.html).appendTo($(itemList));
									$(itemsViewed).each(function (key, value) {
										$('[data-prototype-item="' + value + '"]').attr('data-viewed', 'true');
									});


									if ($('[data-prototype-item][data-show="true"]').length > 0) {
										$('[data-prototype-item][data-show="false"]').hide();
									}

									itemsOffset = itemsOffset + data.count;
									$(counterCurrent).text(itemsOffset);

									if (itemsOffset < itemsTotal) {
										getItems();
									}
								}
							}
						});
					}

				}

				// Filter
				var filter = $('[data-prototype-filter]');
				$(filter).on('submit', function () {
					startItemsRequests();

					return false;
				});
				$(filter).find('[name*="extra"]').on('change', function () {
					startItemsRequests();
					console.log('change');
					return false;
				});

				// Placemark Click
				objectManager.objects.events.add('click', function (e) {
					showPlacemark(e.get('objectId'));
				});

				// Item Click
				$('body').on('click', '[data-prototype-show]', function () {
					var item = $(this),
						id = $(item).data('prototype-show'),
						mapElement = $('[data-prototype-placemark="' + id + '"]');

					var maxScale = 1.4,
						duration = 350;
					$({scale: 1}).animate({
						scale: maxScale
					}, {
						duration: duration,
						step: function (now) {
							mapElement.css('transform', 'scale(' + now + ')')
						}
					}, 'linear');
					setTimeout(function () {
						$({scale: maxScale}).animate({
							scale: 1
						}, {
							duration: duration,
							step: function (now) {
								mapElement.css('transform', 'scale(' + now + ')')
							}
						}, 'linear');
					}, duration);

					setTimeout(function () {
						showPlacemark(id)

					}, duration * 2);
				});

				// Show placemark;
				function showPlacemark(id) {
					var placemark = objectManager.objects.getById(id),
						listElement = $('[data-prototype-item="' + id + '"]');

					// Prepare iconShape
					var shape = placemark.options.iconShape;
					shape.coordinates = shape.coordinates_viewed;

					// Prepare layout
					var clone = $(placemark.options.customLayout).clone(),
						html = '';
					clone.filter('[data-prototype-placemark]').attr('data-viewed', 'true');
					$.each(clone, function (key) {
						var outerHTML = clone[key].outerHTML;
						if (outerHTML != '' && outerHTML != undefined) {
							html += clone[key].outerHTML;
						}
					});
					var layout = ymaps.templateLayoutFactory.createClass(html);

					// set new placemark params
					objectManager.objects.setObjectOptions(id, {
						iconLayout: layout,
						iconShape: shape,
					});

					// Set active to list element
					$(listElement).attr('data-viewed', 'true');

					getBalloon(id);
					itemsViewed.push(id * 1);
				}

				// Show item from link
				if (joomlaParams.item_id) {
					var showItemFromLink = setInterval(function () {
						var mapElement = $('[data-prototype-placemark="' + joomlaParams.item_id + '"]');
						if (mapElement.length > 0) {
							clearInterval(showItemFromLink);
							var maxScale = 1.4,
								duration = 350;
							$({scale: 1}).animate({
								scale: maxScale
							}, {
								duration: duration,
								step: function (now) {
									mapElement.css('transform', 'scale(' + now + ')')
								}
							}, 'linear');
							setTimeout(function () {
								$({scale: maxScale}).animate({
									scale: 1
								}, {
									duration: duration,
									step: function (now) {
										mapElement.css('transform', 'scale(' + now + ')')
									}
								}, 'linear');
							}, duration);
						}
					}, 3);
				}

				// Get balloon
				function getBalloon(id) {
					var ajaxData = [];
					ajaxData.push({name: 'id', value: joomlaParams.catid});
					ajaxData.push({name: 'item_id', value: id});
					ajaxData.push({name: 'return_view', value: 'map'});
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
							showPrototypeMapBalloon();
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

				// Bounds
				var bounds = {
					north: 90,
					south: -90,
					west: -180,
					east: 180
				};

				function getBounds() {
					var projection = map.options.get('projection'),
						center = map.getGlobalPixelCenter(),
						zoom = map.getZoom(),
						size = map.container.getSize();

					var lowerCorner = projection.fromGlobalPixels([
						center[0] - size[0] / 2,
						center[1] + size[1] / 2
					], zoom);

					var upperCorner = projection.fromGlobalPixels([
						center[0] + size[0] / 2,
						center[1] - size[1] / 2
					], zoom);

					var mapBounds = map.getBounds(),
						offsetBounds = [lowerCorner, upperCorner];

					var north = offsetBounds[1][0],
						south = offsetBounds[0][0],
						west = -180,
						east = 180;

					if (mapBounds[0][1].toFixed(6) != mapBounds[1][1].toFixed(6)) {
						west = offsetBounds[0][1];
						east = offsetBounds[1][1];
					}

					bounds.north = north.toFixed(6);
					bounds.south = south.toFixed(6);
					bounds.west = west.toFixed(6);
					bounds.east = east.toFixed(6);
				}

				// Zoom
				var zoomPlus = $('[data-prototype-map-zoom="plus"]'),
					zoomCurrent = $('[data-prototype-map-zoom="current"]'),
					zoomMinus = $('[data-prototype-map-zoom="minus"]');

				$(zoomPlus).on('click', function () {
					if (map.getZoom() != 19) {
						map.setZoom(map.getZoom() + 1, {duration: 200});
					}
				});
				$(zoomMinus).on('click', function () {
					if (map.getZoom() != 0) {
						map.setZoom(map.getZoom() - 1, {duration: 200});
					}
					else {
						$(zoomMinus).attr('disabled', 'disabled');
					}
				});

				function checkZoomButtons(zoom) {
					if (zoom < 19) {
						$(zoomPlus).removeAttr('disabled');
					}
					else {
						$(zoomPlus).attr('disabled', 'disabled');
					}
					if (zoom > 0) {
						$(zoomMinus).removeAttr('disabled');
					}
					else {
						$(zoomMinus).attr('disabled', 'disabled');
					}
				}

				checkZoomButtons(map.getZoom());
				zoomCurrent.text(map.getZoom());

				// Geo location
				$('[data-prototype-map-geo]').on('click', function () {
					ymaps.geolocation.get().then(function (geo) {
						var coords = geo.geoObjects.position;
						map.setCenter(coords, 15);
					});
				});

				// On change map bounds
				map.events.add('boundschange', function (event) {
					//  Change zoom
					if (event.get('newZoom') != event.get('oldZoom')) {
						var zoom = event.get('newZoom');
						mapParams.zoom = zoom;
						zoomCurrent.text(zoom);
						checkZoomButtons(zoom);
					}
					//  Change center
					if (event.get('newCenter') != event.get('oldCenter')) {
						var latitude = event.get('newCenter')[0].toFixed(6),
							longitude = event.get('newCenter')[1].toFixed(6);
						mapParams.center = [latitude, longitude];
						mapParams.latitude = latitude;
						mapParams.longitude = longitude;
					}
					localStorage.setItem('map', JSON.stringify(mapParams));

					startItemsRequests();
				});

				afterInitShow.show();
				afterInitHide.hide();
				afterInit.removeAttr('data-afterInit');
				startItemsRequests();

				// END MAP
			});
		}
	});
})(jQuery);