<?php
/**
 * Plugin Name: Виджет температур в городах
 * Description: Отображает текущую температуру в заданных городах.
 * Version: 0.1
 * Author: Ivan Luchkin
 */


if(!defined('ABSPATH')) exit();


// Папка плагина
define('CWP_PLUGIN_PATH', plugin_dir_path(__FILE__));

// URL плагина
define('CWP_PLUGIN_URL', plugin_dir_url(__FILE__));

// API KEY OPENWEATHERMAP
define('CWP_OPENWEATHERMAP_API_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXX');

// Подключение функций плагина
require_once CWP_PLUGIN_PATH.'includes/functions.php';

// Подключение виджета
require_once CWP_PLUGIN_PATH.'includes/widget.php';

// Регистрация типа записи при инициализации WordPress
add_action('init', 'cwp_register_post_type');

// Регистрация таксономии при инициализации WordPress
add_action('init', 'cwp_register_countries_taxonomy');

// Для авторизованных пользователей
add_action('wp_ajax_search_cities', 'cwp_search_cities_ajax');

// Для неавторизованных пользователей
add_action('wp_ajax_nopriv_search_cities', 'cwp_search_cities_ajax');

// Добавление метабокса
add_action('add_meta_boxes', 'cwp_add_meta_box');

// Сохранение данных при сохранении записи
add_action('save_post', 'cwp_save_city_meta');

// Добавление шаблона страницы
add_filter('theme_page_templates', 'cwp_page_template');

// Загрузка шаблона страницы
add_filter('page_template', 'cwp_load_page_template');

// Регистрация виджета
add_action('widgets_init', 'cwp_register_city_temperature_widget');

// Добавление скриптов в очередь
add_action('wp_enqueue_scripts', 'cwp_enqueue_scripts');

// Добавление хука перед таблицей
add_action('cwp_before_cities_table', 'cwp_before_cities_table');

// Добавление хука после таблицы
add_action('cwp_after_cities_table', 'cwp_after_cities_table');
