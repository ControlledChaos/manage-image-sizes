<?php
/**
 * Settings init
 *
 * @package    Manage_Image_Sizes
 * @subpackage Includes
 * @category   Settings
 * @since      1.0.0
 */

namespace MISP\Settings;

/**
 * Execute functions
 *
 * @since  1.0.0
 * @return void
 */
function setup() {

	// Return namespaced function.
	$ns = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};
}
