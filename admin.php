<?php
/**
 * @package MiefSlideshow
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
require_once('../wp-admin/includes/image.php');
require_once('../wp-admin/includes/file.php');


add_action('admin_menu', 'mief_admin_menu');
register_activation_hook(__FILE__, 'mief_slideshow_install');
add_action('plugins_loaded', 'mief_slideshow_update_db_check');
add_action('plugins_loaded', 'mief_slideshow_detect_upload');
add_action('plugins_loaded', 'mief_slideshow_detect_form');


function mief_slideshow_detect_upload() {
	if (!empty($_FILES)) {
		global $wpdb;

		if (file_is_displayable_image($_FILES['mief_slideshow_file']['tmp_name'])) {
			$overrides = array('test_form' => false);
			$file = wp_handle_upload($_FILES['mief_slideshow_file'],$overrides);

			if ($file) {
				$table_name = $wpdb->prefix . "mief_slideshow";

				$result = $wpdb->insert($table_name, array(
					'filename' => serialize($file),
					'url' => '',
					'weight' => 1
				));
			}
		}
	}
}

/**
 * Update form settings
 *
 * @todo This is a quick 'n dirty solution, abstract this function a bit more
 */
function mief_slideshow_detect_form() {
	global $wpdb;

	if (!empty($_POST)) {
		if(!empty($_POST['mief_slideshow_weight'])) {
			foreach ($_POST['mief_slideshow_weight'] as $pid => $weight) {
				$pid = (int) $pid;
				$query = sprintf('UPDATE %s SET weight=%d WHERE id=%d LIMIT 1',
					MIEF_SLIDESHOW_TABLE,
					$weight,
					$pid
				);

				$wpdb->query($query);
			}
		}

		if (!empty($_POST['mief_slideshow_url'])) {
			foreach ($_POST['mief_slideshow_url'] as $pid => $status) {
				$pid = (int) $pid;
				if ($pid) {
					$query = sprintf('UPDATE %s SET url="%s" WHERE id=%d LIMIT 1',
						MIEF_SLIDESHOW_TABLE,
						mysql_real_escape_string($status),
						$pid
					);

					$wpdb->query($query);
				}
			}
		}

		if (!empty($_POST['mief_slideshow_delete'])) {
			foreach ($_POST['mief_slideshow_delete'] as $pid => $status) {
				$pid = (int) $pid;
				if ($pid) {
					$query = sprintf('DELETE FROM %s WHERE id=%d LIMIT 1',
						MIEF_SLIDESHOW_TABLE,
						$pid
					);
					$wpdb->query($query);
				}
			}
		}
	}
}

function mief_admin_menu() {
	add_options_page('Mief Slideshow options', 'Mief.nl - Slideshow', 'manage_options', 'mief_slideshow_plugin', 'mief_plugin_options');
}

function mief_plugin_options() {
	if ( !current_user_can('manage_options') ) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	$photos = mief_slideshow_get_images();

	require_once(plugin_dir_path(__FILE__) . '/templates/upload.php');
}

global $mief_slideshow_db_version;
$mief_slideshow_db_version = "1.0";

function mief_slideshow_install() {
	global $wpdb, $mief_slideshow_db_version;

	$installed_ver = get_option("mief_slideshow_db_version");

	if ( $installed_ver != $mief_slideshow_db_version ) {
		$table_name = $wpdb->prefix . "mief_slideshow";
		$sql        = "CREATE TABLE " . $table_name . " (
						  id mediumint(9) NOT NULL AUTO_INCREMENT,
						  filename text NOT NULL,
						  url VARCHAR(55) DEFAULT '' NOT NULL,
						  weight mediumint(9) NOT NULL,
						  UNIQUE KEY id (id)
						);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option("mief_slideshow_db_version", $mief_slideshow_db_version);
		update_option("mief_slideshow_db_version", $mief_slideshow_db_version);
	}
}

function mief_slideshow_update_db_check() {
	global $mief_slideshow_db_version;
	if (get_site_option('mief_slideshow_db_version') != $mief_slideshow_db_version) {
		mief_slideshow_install();
	}
}
