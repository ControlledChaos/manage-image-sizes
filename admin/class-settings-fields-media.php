<?php
/**
 * Settings fields for media options.
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
 * Settings fields for media options.
 *
 * @since  1.0.0
 * @access public
 */
class Settings_Fields_Media {

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

        // Media settings.
        add_action( 'admin_init', [ $this, 'settings' ], 9 );

        // Hard crop default image sizes.
        add_action( 'after_setup_theme', [ $this, 'crop' ] );

    }

    /**
	 * Media settings.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function settings() {

        /**
         * Image crop settings.
         */
        add_settings_field( 'misp_hard_crop_medium', __( 'Medium crop', 'controlled-chaos-plugin' ), [ $this, 'medium_crop' ], 'media', 'default', [ __( 'Crop Medium size to exact dimensions', 'controlled-chaos-plugin' ) ] );

        add_settings_field( 'misp_hard_crop_large', __( 'Large crop', 'controlled-chaos-plugin' ), [ $this, 'large_crop' ], 'media', 'default', [ __( 'Crop Large size to exact dimensions', 'controlled-chaos-plugin' ) ] );

        register_setting(
            'media',
            'misp_hard_crop_medium'
        );

        register_setting(
            'media',
            'misp_hard_crop_large'
        );

    }

    /**
     * Medium crop field.
     *
     * @since  1.0.0
	 * @access public
	 * @return string
     */
    public function medium_crop( $args ) {

        $html = '<p><input type="checkbox" id="misp_hard_crop_medium" name="misp_hard_crop_medium" value="1" ' . checked( 1, get_option( 'misp_hard_crop_medium' ), false ) . '/>';

        $html .= '<label for="misp_hard_crop_medium"> '  . $args[0] . '</label></p>';

        echo $html;

    }

    /**
     * Large crop field.
     *
     * @since  1.0.0
	 * @access public
	 * @return string
     */
    public function large_crop( $args ) {

        $html = '<p><input type="checkbox" id="misp_hard_crop_large" name="misp_hard_crop_large" value="1" ' . checked( 1, get_option( 'misp_hard_crop_large' ), false ) . '/>';

        $html .= '<label for="misp_hard_crop_large"> '  . $args[0] . '</label></p>';

        echo $html;

    }

    /**
     * Update crop options.
     *
     * @since  1.0.0
	 * @access public
	 * @return void
     */
    public function crop() {

        if ( get_option( 'misp_hard_crop_medium' ) ) {
            update_option( 'medium_crop', 1 );
        } else {
            update_option( 'medium_crop', 0 );
        }

        if ( get_option( 'misp_hard_crop_large' ) ) {
            update_option( 'large_crop', 1 );
        } else {
            update_option( 'large_crop', 0 );
        }

    }

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function misp_settings_fields_media() {

	return Settings_Fields_Media::instance();

}

// Run an instance of the class.
misp_settings_fields_media();