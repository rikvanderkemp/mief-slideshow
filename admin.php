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

/**
 * Detect file upload and handle it
 *
 * return void
 */
function mief_slideshow_detect_upload() {
    if (!empty($_FILES)) {
        global $wpdb;

        if (file_is_displayable_image($_FILES['mief_slideshow_file']['tmp_name'])) {
            $overrides = array('test_form' => false);
            $file = wp_handle_upload($_FILES['mief_slideshow_file'], $overrides);

            if ($file) {
                $table_name = $wpdb->prefix . "mief_slideshow";

                $result = $wpdb->insert($table_name, array(
                    'filename' => serialize($file),
                    'url' => '',
                    'weight' => 1,
                    'slideshow_id' => mief_get_slideshow_mid()
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
        if (!empty($_POST['mief_slideshow_weight'])) {
            foreach ($_POST['mief_slideshow_weight'] as $pid => $weight) {
                $pid = (int)$pid;
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
                $pid = (int)$pid;
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
                $pid = (int)$pid;

                if ($pid) {
                    $image = sprintf(
                        'SELECT filename FROM %s WHERE id = %d',
                        MIEF_SLIDESHOW_TABLE,
                        $pid
                    );

                    $image = $wpdb->get_row($image);
                    $filename = unserialize($image->filename);
                    @unlink($filename['file']);

                    $query = sprintf('DELETE FROM %s WHERE id=%d LIMIT 1',
                        MIEF_SLIDESHOW_TABLE,
                        $pid
                    );
                    $wpdb->query($query);
                }
            }
        }

        if (!empty($_POST['mief_create'])) {
            $title = trim(strip_tags($_POST['mief_create']));
            $wpdb->insert(MIEF_SLIDESHOW_IDX_TABLE, array(
                'title' => $title,
                'settings' => serialize(array())
            ));
        }
    }
}

/**
 * Add an action menu to the admin panel
 *
 * @return void
 */
function mief_admin_menu() {
    add_plugins_page('Mief Slideshow options', 'Slideshow', 'manage_options', 'mief_slideshow_plugin', 'mief_plugin_options');
}

/**
 * The options screen for uploading new images to the slideshow
 *
 * @return void
 */
function mief_plugin_options() {
    /** @var wpdb */
    global $wpdb;

    mief_slideshow_detect_upload();
    mief_slideshow_detect_form();

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


    $page = mief_get_page();

    switch ($page) {
        case 'upload':
            $photos = mief_slideshow_get_images(mief_get_slideshow_mid());
            require_once(plugin_dir_path(__FILE__) . '/templates/upload.php');
            break;
        default:
            $slideshows = mief_get_all_slideshows();
            require_once(plugin_dir_path(__FILE__) . '/templates/index.php');
    }
}

/**
 * Return all slideshows (without images)
 *
 * @return mixed
 */
function mief_get_all_slideshows() {
    global $wpdb;
    $sql = sprintf(
        'SELECT * FROM %s',
        MIEF_SLIDESHOW_IDX_TABLE
    );
    $result = $wpdb->get_results($sql);

    if ($result) {
        foreach ($result as &$slideshow) {
            $slideshow->settings = unserialize($slideshow->settings);
        }
    }
    return $result;
}

/**
 * Get allowed page for this plugin
 *
 * @return string
 */
function mief_get_page() {
    $pages = array('default', 'upload');
    $page = 'default';

    if (isset($_GET['mp'])) {
        $raw = strip_tags($_GET['mp']);
        if (in_array($raw, $pages)) {
            if ($raw == 'upload') {
                if (mief_get_slideshow_mid() !== false) {
                    $page = $raw;
                }
            } else {
                $page = $raw;
            }
        }
    }
    return $page;
}

function mief_get_slideshow_mid() {
    $return = false;
    if (isset($_GET['mid'])) {
        $mid = (int) $_GET['mid'];
        if ($mid > 0) {
            $return = $mid;
        }
    }
    return $return;
}

global $mief_slideshow_db_version;
$mief_slideshow_db_version = "1.1";

/**
 * Implement wordpress install hook
 *
 * Add a new table to keep track of new photos and their sorting information
 *
 * @return void
 */
function mief_slideshow_install() {
    global $wpdb, $mief_slideshow_db_version;

    $installed_ver = get_option("mief_slideshow_db_version");

    if ($installed_ver != $mief_slideshow_db_version) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix . "mief_slideshow";

        switch ($mief_slideshow_db_version) {
            case '1.1':
                $options_table = $table_name . '_slideshow';
                $sql = sprintf(
                    'CREATE TABLE %s (
                        slideshow_id bigint NOT NULL AUTO_INCREMENT,
                        title VARCHAR(160) NOT NULL,
                        settings TEXT,
                        UNIQUE KEY slideshow_id (slideshow_id)
                    );',
                    $options_table
                );
                $wpdb->query($sql);
                $sql = sprintf(
                  'ALTER TABLE %s ADD slideshow_id bigint NOT NULL',
                    $table_name
                );
                $wpdb->query($sql);
                $sql = sprintf(
                  'INSERT INTO %s (title,settings) VALUES ("%s", "%s")',
                    $options_table,
                    'First slideshow',
                    serialize(array())
                );
                $wpdb->query($sql);
                $sql = sprintf(
                    'UPDATE %s SET slideshow_id = %d',
                    $table_name,
                    1
                );
                $wpdb->query($sql);
                break;
            default:
                $sql = sprintf(
                    'CREATE TABLE %s (
	                    id mediumint(9) NOT NULL AUTO_INCREMENT,
	                    filename text NOT NULL,
					    url VARCHAR(55) DEFAULT "" NOT NULL,
						  weight mediumint(9) NOT NULL,
						  UNIQUE KEY id (id)
						);',
                    $table_name
                );

                dbDelta($sql);
                add_option("mief_slideshow_db_version", $mief_slideshow_db_version);
        }

        update_option("mief_slideshow_db_version", $mief_slideshow_db_version);
    }
}

/**
 * Checking current database version
 *
 * @return void
 */
function mief_slideshow_update_db_check() {
    global $mief_slideshow_db_version;
    if (get_site_option('mief_slideshow_db_version') != $mief_slideshow_db_version) {
        mief_slideshow_install();
    }
}
