<?php
/**
 * Plugin initialization
 *
 * @package    Manage_Image_Sizes
 * @subpackage Init
 * @category   Core
 * @since      1.0.0
 */

namespace MISP;

// Load required files.
foreach ( glob( MISP_PATH . 'includes/core/*.php' ) as $filename ) {
	require $filename;
}
foreach ( glob( MISP_PATH . 'includes/settings/*.php' ) as $filename ) {
	require $filename;
}
foreach ( glob( MISP_PATH . 'includes/backend/*.php' ) as $filename ) {
	require $filename;
}

Admin\setup();
Fields\setup();

require_once MISP_PATH . 'php/log.php';

/**
 * Get the PTE Extras files
 *
 * @todo Rename directory.
 */
require_once MISP_PATH . 'extras/extras.php';
