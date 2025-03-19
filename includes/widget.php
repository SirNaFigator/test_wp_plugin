<?php
/**
 * Создание виджета для отображения температуры
 */
class CWP_Widget extends WP_Widget
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct(
			'cwp_city_temperature_widget',												// ID виджета
			'Виджет температур в городах',												// Название виджета
			[
				'description' => 'Отображает текущую температуру в заданных городах.'	// Описание
			]
		);
	}


	/**
	 * Форма настроек виджета в панели администрирования
	 */
	public function form($instance)
	{
		$city_id	= $instance['city_id'] ?? '';										// Получение выбранного города
		$cities		= get_posts(['post_type' => 'cities', 'numberposts' => -1]);		// Получение всех городов

		echo '<p>';
		echo '<label for="'.$this->get_field_id('city_id').'">Город:</label>';
		echo '<select id="'.$this->get_field_id('city_id').'" name="'.$this->get_field_name('city_id').'" class="widefat">';

		foreach ($cities as $city)
		{
			echo '<option value="'.$city->ID.'" '.selected($city_id, $city->ID, false).'>'.$city->post_title.'</option>';
		}

		echo '</select>';
		echo '</p>';
	}


	/**
	 * Обновление данных виджета
	 */
	public function update($new_instance, $old_instance)
	{
		$instance				= [];
		$instance['city_id']	= intval($new_instance['city_id']);						// Обновление ID города

		return $instance;
	}


	/**
	 * Отображение виджета на сайте
	 */
	public function widget($args, $instance)
	{
		if(!isset($instance['city_id']) || (int)$instance['city_id'] < 1)
		{
			return;																		// Если не задано, выходим
		}

		$city			= get_post($instance['city_id']);								// Получение данных о городе
		$latitude		= get_post_meta($instance['city_id'], '_latitude', true);		// Широта
		$longitude		= get_post_meta($instance['city_id'], '_longitude', true);		// Долгота

		if(!$latitude || !$longitude)
		{
			return;																		// Если не задано, выходим
		}

		$temperature	= cwp_city_temperature($latitude, $longitude);					// Получаем данные температуры

		// Отображение виджета
		echo $args['before_widget'];
		echo $args['before_title'].$city->post_title.$args['after_title'];
		echo '<p>Температура: '.$temperature.'</p>';
		echo $args['after_widget'];
	}
}
