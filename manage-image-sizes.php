<?php
/**
 * Manage Image Sizes
 *
 * @package     Manage_Image_Sizes
 * @version     1.0.0
 * @author      Greg Sweet <greg@ccdzine.com>
 * @copyright   Copyright © 2019, Greg Sweet
 * @link        https://github.com/ControlledChaos/manage-image-sizes
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class
 *
 * Defines constants, gets the initialization class file
 * plus the activation and deactivation classes.
 *
 * @since  1.0.0
 * @access public
 */

// First check for other classes with the same name.
if ( ! class_exists( 'Manage_Image_Sizes' ) ) :
	final class Manage_Image_Sizes {

		/**
		 * Instance of the class
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object Returns the instance.
		 */
		public static function instance() {

			// Varialbe for the instance to be used outside the class.
			static $instance = null;

			if ( is_null( $instance ) ) {

				// Set variable for new instance.
				$instance = new self;

				// Define plugin constants.
				$instance->constants();

				// Require the core plugin class files.
				$instance->dependencies();

			}

			// Return the instance.
			return $instance;

		}

		/**
		 * Constructor method
		 *
		 * @since  1.0.0
		 * @access protected
		 * @return void Constructor method is empty.
		 *              Change to `self` if used.
		 */
		protected function __construct() {}

		/**
		 * Define plugin constants
		 *
		 * Change the prefix, the text domain, and the default meta image
		 * to that which suits the needs of your website.
		 *
		 * Change the version as appropriate.
		 *
		 * @since  1.0.0
		 * @access private
		 * @return void
		 */
		private function constants() {

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
			 * Plugin folder path
			 *
			 * @since  1.0.0
			 * @return string Returns the filesystem directory path (with trailing slash)
			 *                for the plugin __FILE__ passed in.
			 */
			if ( ! defined( 'MISP_PATH' ) ) {
				define( 'MISP_PATH', plugin_dir_path( __FILE__ ) );
				// define( 'MISP_PATH', dirname( __FILE__ ) . '/' );
			}

			/**
			 * Plugin folder URL
			 *
			 * @since  1.0.0
			 * @return string Returns the URL directory path (with trailing slash)
			 *                for the plugin __FILE__ passed in.
			 */
			if ( ! defined( 'MISP_URL' ) ) {
				// define( 'MISP_URL', plugin_dir_url( __FILE__ ) );
				define( 'MISP_URL', plugins_url( basename( dirname( __FILE__ ) ) ) . '/' );
			}

		}

		/**
		 * Require the core plugin class files.
		 *
		 * @since  1.0.0
		 * @access private
		 * @return void Gets the file which contains the core plugin class.
		 */
		private function dependencies() {

			require_once( MISP_PATH . 'php/log.php' );

			/**
			 * Get the PTE Extras files
			 *
			 * @todo Rename directory.
			 */
			require_once MISP_PATH . 'extras/extras.php';

			// The hub of all other dependency files.
			require_once MISP_PATH . 'includes/class-init.php';

			// Include the activation class.
			require_once MISP_PATH . 'includes/class-activate.php';

			// Include the deactivation class.
			require_once MISP_PATH . 'includes/class-deactivate.php';

		}

	}
	// End core plugin class.

	/**
	 * Put an instance of the plugin class into a function.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object Returns the instance of the `Manage_Image_Sizes` class.
	 */
	function misp_core() {

		return Manage_Image_Sizes::instance();

	}

	// Begin plugin functionality.
	misp_core();

// End the check for the plugin class.
endif;

/**
 * Register the activaction & deactivation hooks.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
register_activation_hook( __FILE__, '\misp_activate_plugin' );
register_deactivation_hook( __FILE__, '\misp_deactivate_plugin' );

/**
 * The code that runs during plugin activation.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function misp_activate_plugin() {

	// Run the activation class.
	misp_activate();

}

/**
 * The code that runs during plugin deactivation.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function misp_deactivate_plugin() {

	// Run the deactivation class.
	misp_deactivate();

}

// Bail out now if the core class was not run.
if ( ! function_exists( 'misp_core' ) ) {
	return;
}

/*
 * Option Functionality
 */
function misp_get_option_name() {

	global $current_user;

	if ( ! isset( $current_user ) ) {
		get_currentuserinfo();
	}

	return "misp-option-{$current_user->ID}";

}

function misp_get_user_options() {

	$misp_options = get_option( misp_get_option_name() );

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

	// WORDPRESS DEBUG overrides user setting...
	return array_merge( $defaults, $misp_options );

}

function misp_get_site_options() {

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

function misp_get_options() {

	global $misp_options, $current_user;

	if ( isset( $misp_options ) ) {
		return $misp_options;
	}

	$misp_options = array_merge( misp_get_user_options(), misp_get_site_options() );

	if ( WP_DEBUG ) {
		$misp_options['misp_debug'] = true;
	}

	if ( ! isset( $misp_options['misp_jpeg_compression'] ) ) {
		$misp_options['misp_jpeg_compression'] = apply_filters( 'jpeg_quality', 90, 'misp_options' );
	}

	return $misp_options;

}

function misp_update_user_options() {

	require_once( MISP_PATH . 'php/options.php' );

	$options = misp_get_user_options();

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

	update_option( misp_get_option_name(), $options );

}

/**
 * Get the URL for the PTE interface
 *
 * @param $id the post id of the attachment to modify
 */
function misp_url( $id, $iframe=false ) {

	if ( $iframe ) {
		$misp_url = admin_url( 'admin-ajax.php' ) . "?action=misp_ajax&misp-action=iframe&misp-id={$id}" . '&TB_iframe=true';
	} else {
		$misp_url = admin_url( 'upload.php' ) . "?page=misp-edit&misp-id={$id}";
	}

	return $misp_url;

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

/*
 * Put Hooks and immediate hook functions in this file
 */

/** For the "Edit Image" stuff **/
/* Hook into the Edit Image page */
add_action( 'dbx_post_advanced', 'misp_edit_form_hook_redirect' );

/* Slight redirect so this isn't called on all versions of the media upload page */
function misp_edit_form_hook_redirect() {
	add_action( 'add_meta_boxes', 'misp_admin_media_scripts' );
}

add_action( 'media_upload_library', 'misp_admin_media_scripts_editor' );
add_action( 'media_upload_gallery', 'misp_admin_media_scripts_editor' );
add_action( 'media_upload_image', 'misp_admin_media_scripts_editor' );

function misp_admin_media_scripts_editor() {
	misp_admin_media_scripts( 'attachment' );
}

function misp_admin_media_scripts( $post_type ) {

	$options = misp_get_options();
	misp_add_thickbox();

	if ( $post_type == "attachment" ) {

		wp_enqueue_script( 'misp', MISP_URL . 'apps/coffee-script.js', [ 'underscore' ], MISP_VERSION );
		add_action( 'admin_print_footer_scripts', 'misp_enable_editor_js', 100 );

	} else {
		//add_action( 'admin_print_footer_scripts', 'misp_enable_media_js', 100 );
		wp_enqueue_script( 'misp', MISP_URL . 'js/snippets/misp_enable_media.js', [ 'media-views' ], MISP_VERSION, true);
		wp_enqueue_style( 'misp', MISP_URL . 'css/misp-media.css', null, MISP_VERSION);
	}

	wp_localize_script(
		'misp',
		'mispL10n',
		[
			'PTE'         => __( 'Manage Image Sizes', MISP_DOMAIN ),
			'url'         => misp_url( '<%= id %>', true ),
			'fallbackUrl' => misp_url( '<%= id %>' )
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
	//$options = json_encode( misp_get_options() );
	echo <<<EOT
<script type="text/coffeescript">
$coffee
</script>
EOT;
}



// Add the PTE link to the featured image in the post screen
// Called in wp-admin/includes/post.php
add_filter( 'admin_post_thumbnail_html', 'misp_admin_post_thumbnail_html', 10, 2 );

function misp_admin_post_thumbnail_html( $content, $post_id ) {

	misp_add_thickbox();
	$thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id == null ) {
		return $content;
	}

	return $content .= '<p id="misp-link" class="hide-if-no-js"><a class="thickbox" href="'
		. misp_url( $thumbnail_id, true )
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
		array( 'media-upload' ),
		MISP_VERSION
	);
}


/* For all purpose needs */
add_action( 'wp_ajax_misp_ajax', 'misp_ajax' );
function misp_ajax() {
	// Move all adjuntant functions to a separate file and include that here
	require_once( MISP_PATH . 'php/functions.php' );
	PteLogger::debug( 'PARAMETERS: ' . print_r( $_REQUEST, true ) );

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
			misp_update_user_options();
			break;
	}

	die();

}

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
add_action( 'load-upload.php', 'misp_media_library_boot' );

function misp_media_library_boot() {
    add_action( 'wp_enqueue_media', 'misp_load_media_library' );
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
			'url'         => misp_url( '<%= id %>', true ),
			'fallbackUrl' => misp_url( '<%= id %>' )
		]
	);

}

/* Adds the Thumbnail option to the media library list */
add_filter( 'media_row_actions', 'misp_media_row_actions', 10, 3 ); // priority: 10, args: 3

function misp_media_row_actions( $actions, $post, $detached ) {

	// Add capability check.
	if ( ! misp_check_id( $post->ID ) ) {
		return $actions;
	}

	$options  = misp_get_options();
	$misp_url = misp_url( $post->ID );

	$actions['misp'] = "<a href='${misp_url}' title='" . __( 'Crop Image Sizes', MISP_DOMAIN ) . "'>" . __( 'Crop Sizes', MISP_DOMAIN ) . "</a>";

	return $actions;

}


/* Add Settings Page */
add_action( 'load-settings_page_misp', 'misp_options' );

/* Add Settings Page -> Submit/Update options */
add_action( 'load-options.php', 'misp_options' );

function misp_options() {

	require_once( MISP_PATH . 'php/options.php' );
	misp_options_init();

}

/* Add SubMenus/Pages */
add_action( 'admin_menu', 'misp_admin_menu' );

/**
 * These pages are linked into the hook system of wordpress, this means
 * that almost any wp_admin page will work as long as you append "?page=misp"
 * or "?page=misp-edit".  Try the function `'admin_url("index.php") . '?page=misp';`
 *
 * The function referred to here will output the HTML for the page that you want
 * to display. However if you want to hook into enqueue_scripts or styles you
 * should use the page-suffix that is returned from the function. (e.g.
 * `add_action("load-".$hook, hook_func);`)
 *
 * There is also another hook with the same name as the hook that's returned.
 * I don't remember in which order it is launched, but I believe the pertinent
 * code is in admin-header.php.
 */
function misp_admin_menu() {

	add_options_page(
		__( 'Manage Image Sizes', MISP_DOMAIN ),
		__( 'Image Sizes', MISP_DOMAIN ),
		'edit_posts',
		'misp',
		'misp_launch_options_page'
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

function misp_launch_options_page() {

	require_once( MISP_PATH . 'php/options.php' );
	misp_options_page();

}

/**
 * This runs after headers have been sent, see the misp_edit_setup for the
 * function that runs before anything is sent to the browser
 */
function misp_edit_page() {

	// This is set via the misp_edit_setup function
	global $misp_body;

	echo( $misp_body );

}

/* Admin Edit Page: setup*/
/**
 * This hook (load-media_page_misp-edit)
 *    depends on which page you use in the admin section
 * (load-media_page_misp-edit) : wp-admin/upload.php?page=misp-edit
 * (dashboard_page_misp-edit)  : wp-admin/?page=misp-edit
 * (posts_page_misp-edit)      : wp-admin/edit.php?page=misp-edit
 */
add_action( 'load-media_page_misp-edit', 'misp_edit_setup' );

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

	include_once( MISP_PATH . 'php/functions.php' );

	// Add the scripts and styles.
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'iris' );
	wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$misp_body = misp_body( $post->ID );

}

/**
 * This code creates the image used for the crop
 *
 * By overwriting the wordpress code (same functions), we can change the default size
 * to our own option.
 */
add_action( 'wp_ajax_misp_imgedit_preview','misp_wp_ajax_imgedit_preview_wrapper' );

function misp_wp_ajax_imgedit_preview_wrapper() {
	require_once( MISP_PATH . 'php/overwrite_imgedit_preview.php' );
	misp_wp_ajax_imgedit_preview();
}


/** End Settings Hooks **/

load_plugin_textdomain( MISP_DOMAIN, false, basename( MISP_PATH ) . DIRECTORY_SEPARATOR . 'i18n' );

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
function misp_settings_links( $links ) {

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

// Filter the default settings links with new array.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'misp_settings_links' );