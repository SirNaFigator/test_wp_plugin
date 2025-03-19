<?php
/*
 * Template Name: Список городов с температурой
 */

get_header();
do_action('cwp_before_cities_table');

$search_string	= isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$results		= cwp_cities_list($search_string);

echo '<form method="get" style="margin-bottom: 20px;">';
echo '<input type="text" name="search" placeholder="Поиск города...">';
echo '<button type="submit">Искать</button>';
echo '</form>';

echo '<table border="1" cellpadding="10" cellspacing="0" id="temperature_table">';
echo '<thead><tr><th>Страна</th><th>Город</th><th>Температура</th></tr></thead>';
echo '<tbody>';

if($results && count($results) > 0)
{
	foreach ($results as $row)
	{
		echo '<tr>';
		echo '<td>'.esc_html($row->country).'</td>';
		echo '<td>'.esc_html($row->city).'</td>';
		echo '<td>'.esc_html($row->temperature).'</td>';
		echo '</tr>';
	}
}

echo '</tbody>';
echo '</table>';

do_action('cwp_after_cities_table');
get_footer();
