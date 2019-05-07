<?php
/**
 * Admin functiontionality and settings.
 *
 * @package    Manage_Image_Sizes
 * @subpackage Admin
 *
 * @since      1.0.0
 * @author     Greg Sweet <greg@ccdzine.com>
 */

namespace MIS_Plugin\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin functiontionality and settings.
 *
 * @since  1.0.0
 * @access public
 */
final class Admin {

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

			// Require the class files.
			$instance->dependencies();

		}

		// Return the instance.
		return $instance;

	}

	/**
	 * Constructor method
	 *
	 * @since  1.0.0
	 * @access public
	 * @return self
	 */
	public function __construct() {

		// Add image sizes to insert media UI.
		add_filter( 'image_size_names_choose', [ $this, 'insert_custom_image_sizes' ] );

	}

	/**
	 * Class dependency files.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function dependencies() {

		// The core settings class for the plugin.
		require_once MISP_PATH . 'admin/class-settings.php';

	}

	/**
	 * Add image sizes to media UI
	 *
	 * Adds custom image sizes to "Insert Media" user interface
	 * and adds custom class to the `<img>` tag.
	 *
	 * @since  1.0.0
	 * @access public
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

		// Return the modified array of sizes.
		return $sizes;

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function misp_admin() {

	return Admin::instance();

}

// Run an instance of the class.
misp_admin();