<?php
/**
 * Регистрация пользовательского типа записи Cities
 */
function cwp_register_post_type()
{
	register_post_type('cities',
		[
			'labels'		=> [
				'name'				=> __('Cities'),				// Название во множественном числе
				'singular_name'		=> __('City')					// Название в единственном числе
			],
			'public'		=> true,								// Тип записи доступен публично
			'has_archive'	=> true,								// Разрешить архив
			'supports'		=> ['title', 'editor', 'thumbnail'],	// Поддерживаемые функции
			'menu_icon'		=> 'dashicons-location-alt',			// Иконка
		]
	);
}


/**
 * Регистрация пользовательской таксономии Countries
 */
function cwp_register_countries_taxonomy()
{
	register_taxonomy(
		'countries',												// Имя таксономии
		'cities',													// Привязка к типу записи Cities
		[
			'label'			=> __('Countries'),						// Название таксономии
			'rewrite'		=> ['slug' => 'country'],				// Слаг
			'hierarchical'	=> true,								// Иерархическая таксономия (как категории)
		]
	);
}


/**
 * Добавление метабокса для ввода широты и долготы
 */
function cwp_add_meta_box()
{
	add_meta_box(
		'city_coordinates',											// ID метабокса
		'City Coordinates',											// Заголовок метабокса
		'cwp_render_city_meta_box',									// Функция для отображения содержимого метабокса
		'cities',													// Привязка к типу записи Cities
		'normal',													// Контекст (нормальный блок редактирования)
		'high'														// Приоритет (высокий)
	);
}


/**
 * Получение списка городов с возможностью поиска
 */
function cwp_cities_list($search_string = false)
{
	global $wpdb;

	if($search_string && !empty($search_string))
	{
		$query_like		= $wpdb->prepare(" AND p.post_title LIKE %s", '%'.$wpdb->esc_like($search_string).'%');
	}else{
		$query_like		= '';
	}

	$query 			= "SELECT
    						p.ID,
    						p.post_title AS city,
    						t.name AS country 
    					FROM ".$wpdb->posts." p
    						LEFT JOIN ".$wpdb->term_relationships." tr ON p.ID = tr.object_id
    						LEFT JOIN ".$wpdb->terms." t ON tr.term_taxonomy_id = t.term_id
    					WHERE
    						p.post_type = 'cities'
    						AND p.post_status = 'publish'
    						".$query_like."
    					ORDER BY 
    						t.name ASC,
    						p.post_title ASC";

	$items_list		= $wpdb->get_results($query);

	if(!is_null($items_list) && count($items_list) > 0)
	{
		foreach($items_list as $key => $item)
		{
			$latitude	= get_post_meta($item->ID, '_latitude', true);
			$longitude	= get_post_meta($item->ID, '_longitude', true);

			$items_list[$key]->temperature	= cwp_city_temperature($latitude, $longitude);
		}
	}

	return $items_list;
}


/**
 * Получение данных температуры по широте и долготе
 */
function cwp_city_temperature($latitude, $longitude)
{
	if(!empty($latitude) && !empty($longitude))
	{
		$url		= 'https://api.openweathermap.org/data/2.5/weather?'.http_build_query([
				'lat'		=> $latitude,
				'lon'		=> $longitude,
				'appid'		=> CWP_OPENWEATHERMAP_API_KEY,
				'units'		=> 'metric'
			]);
		$response	= wp_remote_get($url);

		if(!is_wp_error($response))
		{
			$data	= json_decode(wp_remote_retrieve_body($response), true);

			if(isset($data['main']) && isset($data['main']['temp']))
			{
				return $data['main']['temp'].'°C';
			}
		}
	}

	return 'Н/д';
}


/**
 * Обработка AJAX-запроса для поиска города
 */
function cwp_search_cities_ajax()
{
	$search		= isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';	// Получение строки поиска
	$results	= cwp_cities_list($search);												// Выполнение запроса

	wp_send_json($results);																// Возврат формате JSON
}


/**
 * Отображение содержимого метабокса
 */
function cwp_render_city_meta_box($post)
{
	$latitude	= get_post_meta($post->ID, '_latitude', true);							// Получение значения широты
	$longitude	= get_post_meta($post->ID, '_longitude', true);							// Получение значения долготы

	wp_nonce_field('save_city_meta', 'city_meta_nonce');								// Защита от CSRF

	echo '<label for="latitude">Широта:</label>';
	echo '<input type="text" id="latitude" name="latitude" value="'.esc_attr($latitude).'" style="width: 100%; margin-bottom: 10px;">';

	echo '<label for="longitude">Долгота:</label>';
	echo '<input type="text" id="longitude" name="longitude" value="'.esc_attr($longitude).'" style="width: 100%;">';
}


/**
 * Сохранение данных метабокса
 */
function cwp_save_city_meta($post_id)
{
	if(!isset($_POST['city_meta_nonce']) || !wp_verify_nonce($_POST['city_meta_nonce'], 'save_city_meta')){
		return;																				// Проверка nonce
	}

	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;																				// Исключение автосохранения
	}

	if(!current_user_can('edit_post', $post_id)){
		return;																				// Проверка прав пользователя
	}

	if(isset($_POST['latitude'])){
		update_post_meta($post_id, '_latitude', sanitize_text_field($_POST['latitude']));	// Сохранение широты
	}

	if(isset($_POST['longitude'])){
		update_post_meta($post_id, '_longitude', sanitize_text_field($_POST['longitude']));	// Сохранение долготы
	}
}


/**
 * Добавление шаблона страницы через фильтр
 */
function cwp_page_template($templates)
{
	$templates['cities-table.php'] = 'Список городов с температурой';					// Добавление шаблона

	return $templates;
}


/**
 * Загрузка шаблона страницы
 */
function cwp_load_page_template($template)
{
	global $post;

	if($post && get_page_template_slug($post->ID) == 'cities-table.php')
	{
		$template = CWP_PLUGIN_PATH.'templates/cities-table.php';						// Путь к шаблону
	}

	return $template;
}


/**
 * Регистрация виджета
 */
function cwp_register_city_temperature_widget()
{
	register_widget('CWP_Widget');
}


/**
 * Подключение скриптов и стилей
 */
function cwp_enqueue_scripts()
{
	wp_enqueue_script('cwp-ajax', CWP_PLUGIN_URL.'assets/js/cities-ajax.js', ['jquery'], null, true);	// Подключение JS
	wp_localize_script('cwp-ajax', 'cwp_ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);		// Передача URL
}


/**
 * Хук перед таблицей
 */
function cwp_before_cities_table()
{
	echo 'custom action hook перед таблицей.';
}


/**
 * Хук после таблицы
 */
function cwp_after_cities_table()
{
	echo 'custom action hook после таблицы.';
}
