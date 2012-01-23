<?php
/**
 * @package MiefSlideshow
 */
/*
	Plugin Name: Mief.nl - Slideshow
	Plugin URI: http://mief.nl
	Description: Simple html slideshow for photographs
	Version: 1.0
	Author: Rik van der Kemp
	Author URI: http://mief.nl
	License: GPL
*/

/*  Copyright 2011  Rik van der Kemp  (email : rik@mief.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
global $wpdb;

define('WP_DEBUG', true);
define('MIEF_SLIDER_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('MIEF_SLIDER_ASSETS_DIR', MIEF_SLIDER_PLUGIN_DIR_URL . 'assets/');
define('MIEF_SLIDESHOW_TABLE', $wpdb->prefix . "mief_slideshow");

if (is_admin()) {
    require_once(dirname(__FILE__) . '/admin.php');
}

/**
 * The template tag to use.
 * This will load all images according to a slideshow id and show them
 * as such.
 */
function mief_slideshow() {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'mief_slider'
        , MIEF_SLIDER_ASSETS_DIR . 'js/slider.js'
    );

    wp_enqueue_style(
        'mief_slider'
        , MIEF_SLIDER_ASSETS_DIR . 'style/slider.css'
    );

    $photos = mief_slideshow_get_images();
    require_once(plugin_dir_path(__FILE__) . 'templates/slideshow.php');
}

function mief_slideshow_get_images() {
    global $wpdb;

    $query = sprintf('SELECT * FROM %s ORDER BY weight ASC', MIEF_SLIDESHOW_TABLE);
    $results = $wpdb->get_results($query);

    foreach ($results as &$row) {
        $row->filename = unserialize($row->filename);
    }

    return $results;
}