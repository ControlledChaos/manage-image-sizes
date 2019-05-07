<?php
/**
 * Initialize the plugin
 *
 * @package    Manage_Image_Sizes
 * @subpackage Includes
 *
 * @since      1.0.0
 * @author     Greg Sweet <greg@ccdzine.com>
 */

namespace MIS_Plugin\Includes;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Begin the core functionality of the plugin.
 *
 * @since  1.0.0
 * @access public
 */
final class Init {

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

			// Get class dependencies.
			$instance->dependencies();

		}

		// Return the instance.
		return $instance;

	}

	/**
	 * Constructor method
	 *
	 * @since  1.0.0
	 * @access private
	 * @return self
	 */
	private function __construct() {



	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function dependencies() {

		// Admin/backend functionality, scripts and styles.
		require_once MISP_PATH . 'admin/class-admin.php';

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function misp_init() {

	return Init::instance();

}

// Run an instance of the class.
misp_init();