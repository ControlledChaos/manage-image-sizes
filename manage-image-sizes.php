<?php
/**
 * Manage Image Sizes
 *
 * @package     Manage_Image_Sizes
 * @version     1.0.0
 * @author      Greg Sweet <greg@ccdzine.com>
 * @copyright   Copyright © 2019, Greg Sweet
 * @link        https://github.com/ControlledChaos/manage-image-sizes
 * @license     GPL-3.0+ http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Plugin Name:  Manage Image Sizes
 * Plugin URI:   https://github.com/ControlledChaos/manage-image-sizes
 * Description:  A ClassicPress/WordPress plugin to add and manually edit image sizes.
 * Version:      1.0.0
 * Author:       Controlled Chaos Design
 * Author URI:   http://ccdzine.com/
 * License:      GPL-3.0+
 * License URI:  https://www.gnu.org/licenses/gpl.txt
 * Text Domain:  manage-image-sizes
 * Domain Path:  /languages
 * Tested up to: 5.1.1
 */

/**
 * License & Warranty
 *
 * Manage Image Sizes is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Manage Image Sizes is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Manage Image Sizes. If not, see {URI to Plugin License}.
 */

namespace MISP;

use MISP\Classes\Log as Log_Class,
	MISP\Options     as Options;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Constant: Plugin base name
 *
 * @since 1.0.0
 * @var   string The base name of this plugin file.
 */
define( 'MISP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Text domain
 *
 * @since  1.0.0
 * @return string Returns the text domain of the plugin.
 *
 * @todo   Replace all strings with constant.
 */
if ( ! defined( 'MISP_DOMAIN' ) ) {
	define( 'MISP_DOMAIN', 'manage-image-sizes' );
}

/**
 * Plugin version
 *
 * Keeping the version at 1.0.0 as this is a starter plugin but
 * you may want to start counting as you develop for your use case.
 *
 * @since  1.0.0
 * @return string Returns the latest plugin version.
 */
if ( ! defined( 'MISP_VERSION' ) ) {
	define( 'MISP_VERSION', '1.0.0' );
}

/**
 * Plugin folder path
 *
 * @since  1.0.0
 * @return string Returns the filesystem directory path (with trailing slash)
 *                for the plugin __FILE__ passed in.
 */
if ( ! defined( 'MISP_PATH' ) ) {
	define( 'MISP_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin folder URL
 *
 * @since  1.0.0
 * @return string Returns the URL directory path (with trailing slash)
 *                for the plugin __FILE__ passed in.
 */
if ( ! defined( 'MISP_URL' ) ) {
	define( 'MISP_URL', plugin_dir_url( __FILE__ ) . '/' );
}

/**
 * Load text domain
 *
 * @since  1.0.0
 * @return void
 */
function load_plugin_textdomain() {

	// Standard plugin installation.
	\load_plugin_textdomain(
		'manage-image-sizes',
		false,
		dirname( MISP_BASENAME ) . '/languages'
	);

	// If this plugin is in the must-use plugins directory.
	\load_muplugin_textdomain(
		'manage-image-sizes',
		dirname( MISP_BASENAME ) . '/languages'
	);
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_plugin_textdomain' );

// Get plugins path.
$get_plugin = ABSPATH . 'wp-admin/includes/plugin.php';
if ( file_exists( $get_plugin ) ) {
	include_once( $get_plugin );
}

/**
 * Add links to the plugin settings pages on the plugins page.
 *
 * Change the links to those which fill your needs.
 *
 * Uses the universal slug partial for admin pages. Set this
 * slug in the core plugin file.
 *
 * @param  array  $links Default plugin links on the 'Plugins' admin page.
 * @param  object $file Reference the root plugin file with header.
 * @since  1.0.0
 * @return mixed[] Returns HTML strings for the settings pages link.
 *                 Returns an array of custom links with the default plugin links.
 * @link   https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
 */
function settings_link( $links ) {

	if ( is_admin() ) {

		$url = admin_url( 'options-general.php?page=misp' );

		// Create new settings link array as a variable.
		$about_page = [
			sprintf(
				'<a href="%1s" class="misp-settings-link">%2s</a>',
				$url,
				esc_attr( 'Settings', MISP_DOMAIN )
			),
		];

		// Merge the new settings array with the default array.
		return array_merge( $about_page, $links );
	}
}
add_action( 'plugins_loaded', function() {
	add_filter( 'plugin_action_links_' . MISP_BASENAME, __NAMESPACE__ . '\settings_link' );
} );

/**
 * Options in Media Settings
 *
 * @since  1.0.0
 * @return void
 */
function options_media() {

	add_settings_field(
		'hard_crop_medium',
		__( 'Medium crop', MISP_DOMAIN ),
		__NAMESPACE__ . '\hard_crop_medium_callback',
		'media',
		'default',
		[
			__( 'Crop medium size to exact dimensions.', MISP_DOMAIN )
		]
	);

	add_settings_field(
		'hard_crop_large',
		__( 'Large crop', MISP_DOMAIN ),
		__NAMESPACE__ . '\hard_crop_large_callback',
		'media',
		'default',
		[
			__( 'Crop large size to exact dimensions.', MISP_DOMAIN )
		]
	);

	register_setting(
		'media',
		'hard_crop_medium'
	);

	register_setting(
		'media',
		'hard_crop_large'
	);
}

/**
 * Sanitize Medium Size Crop field
 *
 * @since  1.0.0
 * @return boolean
 */
function hard_crop_medium_sanitize() {

	$option = get_option( 'hard_crop_medium', true );
	if ( true == $option ) {
		$option = true;
	} else {
		$option = false;
	}
	return apply_filters( 'misp_hard_crop_medium', $option );
}

/**
 * Sanitize Large Size Crop field
 *
 * @since  1.0.0
 * @return boolean
 */
function hard_crop_large_sanitize() {

	$option = get_option( 'hard_crop_large', true );
	if ( true == $option ) {
		$option = true;
	} else {
		$option = false;
	}
	return apply_filters( 'misp_hard_crop_large', $option );
}

/**
 * Medium crop field
 *
 * @since  1.0.0
 * @return string
 */
function hard_crop_medium_callback( $args ) {

	$option   = hard_crop_medium_sanitize();
	$field_id = 'hard_crop_medium';

	$html = '<fieldset>';
	$html .= sprintf(
		'<legend class="screen-reader-text">%s</legend>',
		__( 'Medium crop', MISP_DOMAIN )
	);
	$html .= sprintf(
		'<label for="%s">',
		$field_id
	);
	$html .= sprintf(
		'<input type="checkbox" id="%s" name="%s" value="1" %s /> %s',
		$field_id,
		$field_id,
		checked( 1, $option, false ),
		$args[0]
	);
	$html .= '</label>';
	$html .= '</fieldset>';

	echo $html;
}

/**
 * Large crop field
 *
 * @since  1.0.0
 * @return string
 */
function hard_crop_large_callback( $args ) {

	$option   = hard_crop_large_sanitize();
	$field_id = 'hard_crop_large';

	$html = '<fieldset>';
	$html .= sprintf(
		'<legend class="screen-reader-text">%s</legend>',
		__( 'Large crop', MISP_DOMAIN )
	);
	$html .= sprintf(
		'<label for="%s">',
		$field_id
	);
	$html .= sprintf(
		'<input type="checkbox" id="%s" name="%s" value="1" %s /> %s',
		$field_id,
		$field_id,
		checked( 1, $option, false ),
		$args[0]
	);
	$html .= '</label>';
	$html .= '</fieldset>';

	echo $html;
}

/**
 * Update default hard crop options
 *
 * @since  1.0.0
 * @return void
 */
function default_sizes_crop() {

	if ( get_option( 'hard_crop_medium', true ) ) {
		update_option( 'medium_crop', true );
	} else {
		update_option( 'medium_crop', false );
	}

	if ( get_option( 'hard_crop_large', true ) ) {
		update_option( 'large_crop', true );
	} else {
		update_option( 'large_crop', false );
	}
}

/**
 * Add image sizes to media UI
 *
 * Adds custom image sizes to "Insert Media" user interface
 * and adds custom class to the `<img>` tag.
 *
 * @since  1.0.0
 * @param  array $sizes Gets the array of image size names.
 * @global array $_wp_additional_image_sizes Gets the array of custom image size names.
 * @return array $sizes Returns an array of image size names.
 */
function insert_custom_image_sizes( $sizes ) {

	// Access global variables.
	global $_wp_additional_image_sizes;

	// Return default sizes if no custom sizes.
	if ( empty( $_wp_additional_image_sizes ) ) {
		return $sizes;
	}

	// Capitalize custom image size names and remove hyphens.
	foreach ( $_wp_additional_image_sizes as $id => $data ) {

		if ( ! isset( $sizes[$id] ) ) {
			$sizes[$id] = ucwords( str_replace( '-', ' ', $id ) );
		}
	}
	return $sizes;
}

/*
 * Option Functionality
 */
function get_option_name() {

	global $current_user;

	if ( ! isset( $current_user ) ) {
		get_currentuserinfo();
	}
	return "misp-option-{$current_user->ID}";
}

function get_user_options() {

	$misp_options = get_option( get_option_name() );

	if ( ! is_array( $misp_options ) ) {
		$misp_options = [];
	}

	$defaults = [
		'misp_debug'            => false,
		'misp_crop_save'        => false,
		'misp_thumbnail_bar'    => 'horizontal',
		'misp_imgedit_disk'     => false,
		'misp_imgedit_max_size' => 600,
		'misp_debug_out_chrome' => false,
		'misp_debug_out_file'   => false
	];

	// WordPress debug overrides user setting.
	return array_merge( $defaults, $misp_options );
}

function get_site_options() {

	$misp_site_options = get_option( 'misp-site-options' );

	if ( ! is_array( $misp_site_options ) ){
		$misp_site_options = [];
	}

	$defaults = [
		'misp_hidden_sizes' => [],
		'cache_buster'      => true
	];

	return array_merge( $defaults, $misp_site_options );
}

function get_plugin_options() {

	global $misp_options, $current_user;

	if ( isset( $misp_options ) ) {
		return $misp_options;
	}

	$misp_options = array_merge( get_user_options(), get_site_options() );

	if ( WP_DEBUG ) {
		$misp_options['misp_debug'] = true;
	}

	if ( ! isset( $misp_options['misp_jpeg_compression'] ) ) {
		$misp_options['misp_jpeg_compression'] = apply_filters( 'jpeg_quality', 90, 'misp_options' );
	}

	return $misp_options;
}

function update_user_options() {

	require_once MISP_PATH . 'php/options.php';

	$options = get_user_options();

	// Check nonce
	if ( ! check_ajax_referer( "misp-options", 'misp-nonce', false ) ){
		return misp_json_error( "CSRF Check failed" );
	}

	if ( isset( $_REQUEST['misp_crop_save'] ) ) {

		if ( strtolower( $_REQUEST['misp_crop_save'] ) === 'true' ) {
			$options['misp_crop_save'] = true;
		} else {
			$options['misp_crop_save'] = false;
		}
	}

	if ( isset( $_REQUEST['misp_thumbnail_bar'] ) ) {

		if ( strtolower( $_REQUEST['misp_thumbnail_bar'] ) == 'vertical' ) {
			$options['misp_thumbnail_bar'] = 'vertical';
		} else {
			$options['misp_thumbnail_bar'] = 'horizontal';
		}
	}

	update_option( get_option_name(), $options );
}

/**
 * Get the URL for the PTE interface
 *
 * @param $id the post id of the attachment to modify
 */
function crop_ui_url( $id, $iframe=false ) {

	if ( $iframe ) {
		$url = admin_url( 'admin-ajax.php' ) . "?action=misp_ajax&misp-action=iframe&misp-id={$id}" . '&TB_iframe=true';
	} else {
		$url = admin_url( 'upload.php' ) . "?page=misp-edit&misp-id={$id}";
	}

	return $url;
}

/**
 * Used in functions.php, log.php & options.php to get pseudo-TMP file paths
 */
function misp_tmp_dir() {

	$uploads 	  = wp_upload_dir();
	$MISP_TMP_DIR = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'misptmp' . DIRECTORY_SEPARATOR;
	$MISP_TMP_URL = $uploads['baseurl'] . "/misptmp/";

	return compact( 'MISP_TMP_DIR', 'MISP_TMP_URL' );
}

/**
 * For the "Edit Image" stuff.
 * Hook into the Edit Image page.
 */
add_action( 'add_meta_boxes', __NAMESPACE__ . '\misp_edit_form_hook_redirect' );

// Slight redirect so this isn't called on all versions of the media upload page.
function misp_edit_form_hook_redirect() {
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\misp_admin_media_scripts' );
}
add_action( 'media_upload_library', __NAMESPACE__ . '\misp_admin_media_scripts_editor' );
add_action( 'media_upload_gallery', __NAMESPACE__ . '\misp_admin_media_scripts_editor' );
add_action( 'media_upload_image', __NAMESPACE__ . '\misp_admin_media_scripts_editor' );

function misp_admin_media_scripts_editor() {
	misp_admin_media_scripts( 'attachment' );
}

function misp_admin_media_scripts( $post_type ) {

	$options = get_plugin_options();
	misp_add_thickbox();

	if ( $post_type == "attachment" ) {

		wp_enqueue_script( 'misp', MISP_URL . 'apps/coffee-script.js', [ 'underscore' ], MISP_VERSION );
		add_action( 'admin_print_footer_scripts', __NAMESPACE__ . '\misp_enable_editor_js', 100 );

	} else {
		// add_action( 'admin_print_footer_scripts', __NAMESPACE__ . '\misp_enable_media_js', 100 );
		wp_enqueue_script( 'misp', MISP_URL . 'js/snippets/misp_enable_media.js', [ 'media-views' ], MISP_VERSION, true);
		wp_enqueue_style( 'misp', MISP_URL . 'css/misp-media.css', null, MISP_VERSION);
	}

	wp_localize_script(
		'misp',
		'mispL10n',
		[
			'PTE'         => __( 'Manage Image Sizes', MISP_DOMAIN ),
			'url'         => crop_ui_url( '<%= id %>', true ),
			'fallbackUrl' => crop_ui_url( '<%= id %>' )
		]
	);
}

function misp_enable_editor_js() {
	injectCoffeeScript( MISP_PATH . 'js/snippets/editor.coffee' );
}

function misp_enable_media_js() {
	injectCoffeeScript( MISP_PATH . 'js/snippets/media.coffee' );
}

function injectCoffeeScript( $coffeeFile ) {

	$coffee = @file_get_contents( $coffeeFile );
	// $options = json_encode( get_plugin_options() );
	echo <<<EOT
<script type="text/coffeescript">
$coffee
</script>
EOT;
}



// Add the PTE link to the featured image in the post screen
// Called in wp-admin/includes/post.php
add_filter( 'admin_post_thumbnail_html', __NAMESPACE__ . '\misp_admin_post_thumbnail_html', 10, 2 );

function misp_admin_post_thumbnail_html( $content, $post_id ) {

	misp_add_thickbox();
	$thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id == null ) {
		return $content;
	}

	return $content .= '<p id="misp-link" class="hide-if-no-js"><a class="thickbox" href="'
		. crop_ui_url( $thumbnail_id, true )
		. '">'
		. esc_html__( 'Manage Image Sizes', MISP_DOMAIN )
		. '</a></p>';
}

/* Fix wordpress ridiculousness about making a thickbox max width=720 */
function misp_add_thickbox() {

	add_thickbox();
	wp_enqueue_script(
		'misp-fix-thickbox',
		MISP_URL . 'js/snippets/misp-fix-thickbox.js',
		[ 'media-upload' ],
		MISP_VERSION
	);
}


/* For all purpose needs */
function misp_ajax() {

	// Move all adjuntant functions to a separate file and include that here
	require_once MISP_PATH . 'php/functions.php';

	Log_Class\PteLogger :: debug( 'PARAMETERS: ' . print_r( $_REQUEST, true ) );

	//header('Content-type: application/json');
	switch ( $_GET['misp-action'] ) {
		case 'iframe':
			misp_init_iframe();
			break;
		case 'resize-images':
			misp_resize_images();
			break;
		case 'confirm-images':
			misp_confirm_images();
			break;
		case 'delete-images':
			misp_delete_images();
			break;
		case 'get-thumbnail-info':
			$id = (int) $_GET['id'];
			if ( misp_check_id( $id ) )
				print( json_encode( misp_get_all_alternate_size_information( $id ) ) );
			break;
		case 'change-options':
			update_user_options();
			break;
	}
	die();
}
add_action( 'wp_ajax_misp_ajax', __NAMESPACE__ . '\misp_ajax' );

/**
 * Perform the capability check
 *
 * @param $id References the post that the user needs to have permission to edit
 * @returns boolean true if the current user has permission else false
 */
function misp_check_id( $id ) {

	if ( ! $post = get_post( $id ) ) {
		return false;
	}

	if ( current_user_can( 'edit_post', $id ) || current_user_can( 'misp_edit', $id ) ) {
		return apply_filters( 'misp-capability-check', true, $id );
	}

	return apply_filters( 'misp-capability-check', false, $id );

}

/**
 * Upload.php (the media library page) fires:
 * - 'load-upload.php' (wp-admin/admin.php)
 * - GRID VIEW:
 *   + 'wp_enqueue_media' (upload.php:wp-includes/media.php:wp_enqueue_media)
 * - LIST VIEW:
 *   + 'media_row_actions' (filter)(class-wp-media-list-table.php)
 */
add_action( 'load-upload.php', __NAMESPACE__ . '\misp_media_library_boot' );

function misp_media_library_boot() {
    add_action( 'wp_enqueue_media', __NAMESPACE__ . '\misp_load_media_library' );
}

function misp_load_media_library() {

	global $mode;

    if ( 'grid' !== $mode ) {
		return;
	}

	wp_enqueue_script( 'misp', MISP_URL . 'js/snippets/misp_enable_media.js', null, MISP_VERSION, true);
	wp_localize_script(
		'misp',
		'mispL10n',
		[
			'PTE'         => __( 'Crop Sizes', MISP_DOMAIN ),
			'url'         => crop_ui_url( '<%= id %>', true ),
			'fallbackUrl' => crop_ui_url( '<%= id %>' )
		]
	);

}

function misp_media_row_actions( $actions, $post, $detached ) {

	// Add capability check.
	if ( ! misp_check_id( $post->ID ) ) {
		return $actions;
	}

	$options = get_plugin_options();
	$url     = crop_ui_url( $post->ID );

	$actions['misp'] = "<a href='${url}' title='" . __( 'Crop Image Sizes', MISP_DOMAIN ) . "'>" . __( 'Crop Sizes', MISP_DOMAIN ) . "</a>";

	return $actions;
}
add_filter( 'media_row_actions', __NAMESPACE__ . '\misp_media_row_actions', 10, 3 );

function misp_options() {
	require_once MISP_PATH . 'php/options.php';
	Options\options_init();
}
add_action( 'load-settings_page_misp',  __NAMESPACE__ . '\misp_options' );
add_action( 'load-options.php',  __NAMESPACE__ . '\misp_options' );

/**
 * Plugin options page
 *
 * Displays as a submenu page under Settings.
 *
 * @since  1.0.0
 * @return void
 */
function admin_menu() {

	add_options_page(
		__( 'Manage Image Sizes', MISP_DOMAIN ),
		__( 'Image Sizes', MISP_DOMAIN ),
		'edit_posts',
		'misp',
		__NAMESPACE__ . '\misp_launch_options_page'
	);

	// The submenu page function does not put a menu item in the wordpress sidebar.
	add_submenu_page(
		null,
		__( 'Manage Image Sizes', MISP_DOMAIN ),
		__( 'Image Sizes', MISP_DOMAIN ),
		'edit_posts',
		'misp-edit',
		'misp_edit_page'
	);


}
add_action( 'admin_menu',  __NAMESPACE__ . '\admin_menu' );

function misp_launch_options_page() {
	require_once MISP_PATH . 'php/options.php';
	Options\options_page();
}

/**
 * This runs after headers have been sent, see the misp_edit_setup for the
 * function that runs before anything is sent to the browser
 */
function misp_edit_page() {

	// This is set via the misp_edit_setup function'.
	global $misp_body;
	echo( $misp_body );
}

/**
 * This hook (load-media_page_misp-edit)
 *    depends on which page you use in the admin section
 * (load-media_page_misp-edit) : wp-admin/upload.php?page=misp-edit
 * (dashboard_page_misp-edit)  : wp-admin/?page=misp-edit
 * (posts_page_misp-edit)      : wp-admin/edit.php?page=misp-edit
 */
function misp_edit_setup() {

	global $post, $title, $misp_body;

	$post_id = (int) $_GET['misp-id'];

	if ( ! isset( $post_id ) || ! is_int( $post_id ) || ! wp_attachment_is_image( $post_id ) || ! misp_check_id( $post_id ) ) {

		//die("POST: $post_id IS_INT:" . is_int( $post_id ) . " ATTACHMENT: " . wp_attachment_is_image( $post_id ));
		wp_redirect( admin_url( 'upload.php' ) );
		exit();
	}

	$post  = get_post( $post_id );
	$title = __( 'Manage Image Sizes', MISP_DOMAIN );

	include_once MISP_PATH . 'php/functions.php';

	// Add the scripts and styles.
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'iris' );
	wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$misp_body = misp_body( $post->ID );
}
add_action( 'load-media_page_misp-edit',  __NAMESPACE__ . '\misp_edit_setup' );

/**
 * This code creates the image used for the crop
 *
 * By overwriting the wordpress code (same functions), we can change the default size
 * to our own option.
 */
function misp_wp_ajax_imgedit_preview_wrapper() {
	require_once MISP_PATH . 'php/overwrite_imgedit_preview.php';
	misp_wp_ajax_imgedit_preview();
}
add_action( 'wp_ajax_misp_imgedit_preview',  __NAMESPACE__ . '\misp_wp_ajax_imgedit_preview_wrapper' );

require_once MISP_PATH . 'init.php';

/**
 * Initialize plugin
 *
 * @since  1.0.0
 * @return void
 */
function init() {

	add_action( 'admin_init', __NAMESPACE__ . '\options_media', 9 );
	add_action( 'after_setup_theme',  __NAMESPACE__ . '\default_sizes_crop' );
	add_filter( 'image_size_names_choose',  __NAMESPACE__ . '\insert_custom_image_sizes', 10, 1 );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Include Imsanity
 *
 * @todo Merge settings pages.
 */
if ( ! is_plugin_active( 'imsanity/imsanity.php' ) ) {
	include_once MISP_PATH . 'imsanity/imsanity.php';
}
