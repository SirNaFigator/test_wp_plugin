jQuery(document).ready(function($)
{
	// Обработка ввода в поле поиска
	$('input[name="search"]').on('input', function(){
		// Получение значения поиска
		let search_string	= $(this).val();

		$.ajax({
			url: cwp_ajax_object.ajax_url,			// URL для AJAX-запроса
			type: 'GET',
			data: {
				action: 'search_cities',			// Действие
				search: search_string				// Строка поиска
			},
			success: function(response){
				// Селектор тела таблицы
				let table = $('#temperature_table tbody');

				// Очистка тела таблицы
				table.html("");

				response.forEach(function(item)
				{
					let td_country		= $('<td />').html(item.country);
					let td_city			= $('<td />').html(item.city);
					let td_temperature	= $('<td />').html(item.temperature);
					let tr				= $('<tr />').append(td_country, td_city, td_temperature);

					table.append(tr);
				});
			}
		});
	});
});