<?php
/**
 * Plugin deactivation class.
 *
 * This file must not be namespaced.
 *
 * @package    Manage_Image_Sizes
 * @subpackage Includes
 *
 * @since      1.0.0
 * @author     Greg Sweet <greg@ccdzine.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin deactivation class.
 *
 * @since  1.0.0
 * @access public
 */
class Manage_Image_Sizes_Deactivate {

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

			// Deactivation function
			$instance->deactivate();

		}

		// Return the instance.
		return $instance;

	}

	/**
	 * Constructor method
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void Constructor method is empty.
	 *              Change to `self` if used.
	 */
	public function __construct() {}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function deactivate() {}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function misp_deactivate() {

	return Manage_Image_Sizes_Deactivate::instance();

}