<?php

class PostThumbnailExtras {
	public function __construct() {
		// Wordpress hooks and settings
		add_action( 'init', array( $this, 'i18n' ) );

		/*
		 * Load sub-objects
		 */
		$this->load_requires();
	}

	/**
	 * Internationalization and Localization
	 */
	public function i18n() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'manage-image-sizes' );
		load_textdomain( 'manage-image-sizes'
			, WP_LANG_DIR.'/'.'manage-image-sizes'.'/'.'manage-image-sizes'.'-'.$locale.'.mo' );
		load_plugin_textdomain( 'manage-image-sizes'
			, FALSE
			, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
	}

	private $requires = array( 'php/shortcode.php'
		, 'php/options.php'
	);

	private function load_requires() {
		$path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		foreach ( $this->requires as $require ){
			require( $path . $require );
		}
	}

}

$ptx = new PostThumbnailExtras();
