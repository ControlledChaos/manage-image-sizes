<?php
/**
 * Plugin options
 *
 * @package    Manage_Image_Sizes
 * @subpackage Includes
 * @category   Settings
 * @since      1.0.0
 */

namespace MISP\Fields;

use MISP\Classes\Log as Log_Class;

use function MISP\get_plugin_options;
use function MISP\get_option_name;
use function MISP\get_user_options;
use function MISP\get_site_options;

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

	add_action( 'load-settings_page_misp', $ns( 'options_init' ) );
	add_action( 'load-options.php', $ns( 'options_init' ) );
	add_action( 'admin_init', $ns( 'options_media' ) );
}

function options_init() {

	add_filter( 'option_page_capability_misp_options', __NAMESPACE__ . '\edit_posts_cap' );

	register_setting(
		'misp_options',
		get_option_name(),
		__NAMESPACE__ . '\options_validate'
	);

	add_settings_section(
		'misp_main',
		 __( 'User Options', 'manage-image-sizes' ),
		 __NAMESPACE__ . '\noop',
		 'misp'
	);

	/*
	add_settings_field(
		'misp_debug',
		__( 'Debug', 'manage-image-sizes' ),
		__NAMESPACE__ . '\debug_display',
		'misp',
		'misp_main'
	);
	*/

	add_settings_field(
		'misp_crop_save',
		__( 'Crop and Save', 'manage-image-sizes' ),
		__NAMESPACE__ . '\crop_save_display',
		'misp',
		'misp_main',
		[
			__( 'Bypass the image crop verification.', 'manage-image-sizes' )
		]
	);

	add_settings_field(
		'misp_imgedit_max_size',
		__( 'Crop Picture Size', 'manage-image-sizes' ),
		__NAMESPACE__ . '\imgedit_size_display',
		'misp',
		'misp_main'
	);

	add_settings_field(
		'misp_reset',
		__( 'Reset to defaults', 'manage-image-sizes' ),
		__NAMESPACE__ . '\reset_display',
		'misp',
		'misp_main'
	);

	// Only show for admins.
	if ( current_user_can( 'manage_options' ) ) {

		register_setting(
			'misp_options',
			'misp-site-options',
			__NAMESPACE__ . '\site_options_validate'
		);

		add_settings_section(
			'misp_site',
			__( 'Site Options', 'manage-image-sizes' ),
			__NAMESPACE__ . '\site_options_html',
			'misp'
		);

		add_settings_field(
			'misp_sizes',
			__( 'Thumbnails', 'manage-image-sizes' ),
			__NAMESPACE__ . '\sizes_display',
			'misp',
			'misp_site'
		);

		add_settings_field(
			'misp_jpeg_compression',
			__( 'JPEG Compression', 'manage-image-sizes' ),
			__NAMESPACE__ . '\jpeg_compression_display',
			'misp',
			'misp_site'
		);

		add_settings_field(
			'misp_cache_buster',
			__( 'Cache Buster', 'manage-image-sizes' ),
			__NAMESPACE__ . '\cache_buster_display',
			'misp',
			'misp_site'
		);
	} // End manage_options only.
}



function site_options_validate( $input ) {

	// $sizes = misp_get_alternate_sizes( false );
	if ( ! current_user_can( 'manage_options' ) ) {

		add_settings_error(
			'misp_options_site',
			'misp_options_error',
			__( "Only users with the 'manage_options' capability may make changes to these settings.", 'manage-image-sizes' )
		);
		return get_site_options();
	}
	$sizes = get_intermediate_image_sizes();

	$misp_hidden_sizes = [];

	foreach ( $sizes as $size ) {
		// Hidden.
		if ( isset( $input['misp_hidden_sizes'] ) && is_array( $input['misp_hidden_sizes'] )
			&& in_array( $size, $input['misp_hidden_sizes'] ) ) {
				$misp_hidden_sizes[] = $size;
			}
	}

	$output = [ 'misp_hidden_sizes' => $misp_hidden_sizes ];

	// Check the JPEG Compression value.
	if ( isset( $input['misp_jpeg_compression'] ) && $input['misp_jpeg_compression'] != '' ){
		$tmp_jpeg_compression = (int) preg_replace( "/[\D]/", "", $input['misp_jpeg_compression'] );

		if ( ! is_int( $tmp_jpeg_compression )
			|| $tmp_jpeg_compression < 0
			|| $tmp_jpeg_compression > 100 )
		{
			add_settings_error(
				'misp_options_site',
				'misp_options_error',
				__( "JPEG Compression needs to be set from 0 to 100.", 'manage-image-sizes' ) . $tmp_jpeg_compression . "/" . $input['misp_jpeg_compression']
			);
		}
		$output['misp_jpeg_compression'] = $tmp_jpeg_compression;
	}

	// Cache buster.
	$output['cache_buster'] = isset( $input['misp_cache_buster'] );

	return $output;
}

function options_validate( $input ) {

	$options = get_user_options();

	if ( isset( $input['reset'] ) ) {
		return [];
	}
	$checkboxes = [
		'misp_debug',
		'misp_debug_out_chrome',
		'misp_debug_out_file',
		'misp_crop_save',
		'misp_imgedit_disk',
	];

	foreach ( $checkboxes as $opt ) {

		if ( isset( $input[$opt] ) ) {
			$options[$opt] = true;
		} elseif ( isset( $options[$opt] ) ) {
			unset( $options[$opt] );
		}
	}

	// Check the imgedit_max_size value.
	if ( $input['misp_imgedit_max_size'] != "" ) {

		$tmp_size = (int) preg_replace( "/[\D]/", "", $input['misp_imgedit_max_size'] );

		if ( $tmp_size < 0 || $tmp_size > 10000 ) {

			add_settings_error(
				get_option_name(),
				'misp_options_error',
				__( 'Crop Size must be between 0 and 10000.', 'manage-image-sizes' )
			);
		}
		$options['misp_imgedit_max_size'] = $tmp_size;
	} else {
		unset( $options['misp_imgedit_max_size'] );
	}
	return $options;
}

function debug_display() {

	$options = get_user_options();
	$label   = get_option_name();
?>
	<span><input type="checkbox" name="<?php
	print $label;
	?>[misp_debug]" <?php
	if ( $options['misp_debug'] ): print "checked"; endif;
	?> id="misp_debug"/>&nbsp;<label for="misp_debug"><?php _e( 'Enable debugging', 'manage-image-sizes' ); ?></label>
<?php if ( WP_DEBUG ) {
	print( "<br/><em>" );
	_e( "WP_DEBUG is currently set to true and will override this setting. (debug is enabled)" );
	print( "</em>" );
}?>
	</span>
	<div class="sub-option"><input type="checkbox" name="<?php echo $label; ?>[misp_debug_out_chrome]" <?php
		if ( $options['misp_debug_out_chrome'] ): print "checked"; endif; ?> id="misp_debug_out_chrome"/>
		&nbsp;
		<label for="misp_debug_out_chrome"><?php printf( __( 'Use <a href="%s">ChromePhp</a> for log output'),
			'https://github.com/ccampbell/chromephp'
		); ?></label>
	</div>
	<div class="sub-option"><input type="checkbox" name="<?php echo $label; ?>[misp_debug_out_file]" <?php
		if ( $options['misp_debug_out_file'] ): print "checked"; endif; ?> id="misp_debug_out_file"/>
		&nbsp;
		<label for="misp_debug_out_file"><?php printf(
			__( 'Write log output to a <a href="%s">file</a>'),
			Log_Class\PteLogFileHandler :: getLogFileUrl()
		); ?></label>
	</div>
<?php
}

function crop_save_display( $args ) {

	$options  = get_user_options();
	$field_id = 'misp_crop_save';
	$option   = $options[ $field_id] ;
	$name     = get_option_name() . "[$field_id]";
	$checked  = checked( 1, $option, false );

	$html = '<fieldset>';
	$html .= sprintf(
		'<legend class="screen-reader-text">%s</legend>',
		__( 'Crop and Save', 'manage-image-sizes' )
	);
	$html .= sprintf(
		'<label for="%s">',
		$field_id
	);
	$html .= sprintf(
		'<input type="checkbox" id="%s" name="%s" value="1" %s /> %s',
		$field_id,
		$name,
		checked( 1, $option, false ),
		$args[0]
	);
	$html .= '</label>';
	$html .= '</fieldset>';

	echo $html;
}

function imgedit_size_display() {

	$options = get_user_options();
	$size_id = 'misp_imgedit_max_size';
	$disc_id = 'misp_imgedit_disk';
	$name    = get_option_name();
?>
	<span><input class="small-text" type="text"
			name="<?php print $name; ?>[misp_imgedit_max_size]"
			value="<?php if ( isset( $options['misp_imgedit_max_size'] ) ) { print $options['misp_imgedit_max_size']; }?>"
			id="misp_imgedit_max_size">&nbsp;
	<?php _e( 'Set the maximum image width for the crop palette.', 'manage-image-sizes' ); ?>
	<br/><em><?php _e( 'No entry defaults to 600', 'manage-image-sizes' ); ?></em>
	</span>
	<div class="sub-option">
	<span><input type="checkbox"
			name="<?php print $name; ?>[misp_imgedit_disk]"
			<?php if ( $options['misp_imgedit_disk'] ): print "checked"; endif; ?>
			id="misp_imgedit_disk">&nbsp;<label for="misp_imgedit_disk">
		<?php _e( 'Check this to save the generated working image to disk instead of creating on the fly (experimental)', 'manage-image-sizes' ); ?>
	</label>
	</span>
	</div>
<?php
}

function reset_display(){
?>
	<input class="button-secondary" name="<?php
	echo( get_option_name() );
	?>[reset]" type="submit" value="<?php esc_attr_e( 'Reset User Options', 'manage-image-sizes' ); ?>" />
<?php
}

function gcd($a, $b){
	if ( $a == 0 ) return b;
	while( $b > 0 ){
		if ( $a > $b ){
			$a = $a - $b;
		}
		else {
			$b = $b - $a;
		}
	}
	if ( $a < 0 or $b < 0 ){
		return null;
	}
	return $a;
}

function sizes_display() {
	$options = get_plugin_options();

	// Table Header
?>
	<table><tr><th><?php _e("Post Thumbnail", 'manage-image-sizes' ); ?></th>
		<th><?php _e( "Hidden", 'manage-image-sizes' ); ?></th>
		</tr>
<?php
	// End table header

	$sizes = misp_get_alternate_sizes(false);

	foreach ( $sizes as $size => $size_data ){
		$hidden = ( in_array( $size, $options['misp_hidden_sizes'] ) ) ?
			"checked":"";

		$name = isset( $size_data['display_name'] )? $size_data['display_name'] : $size;

		print( "<tr><td><label for='{$size}'>{$name}</label></td>"
			. "<td><input type='checkbox' id='{$size}' name='misp-site-options[misp_hidden_sizes][]'"
			. " value='{$size}' {$hidden}></td>"
			. "</tr>"
		);
	}

	print( '</table>' );
}

function jpeg_compression_display(){
	$options = get_site_options();
?>
	<span><input class="small-text" type="text"
			 name="misp-site-options[misp_jpeg_compression]"
			 value="<?php if ( isset( $options['misp_jpeg_compression'] ) ){ print $options['misp_jpeg_compression']; }?>"
			 id="misp_jpeg_compression" placeholder="90">&nbsp;
	<?php _e("Set the compression level for resizing jpeg images (0 to 100).", 'manage-image-sizes' ); ?>
	<br/><em><?php _e("No entry defaults to using the 'jpeg_quality' filter or 90", 'manage-image-sizes' ); ?></em>
	</span>
<?php
}

function cache_buster_display(){
	$options = get_site_options();
?>
	<span><input type="checkbox" name="misp-site-options[misp_cache_buster]" <?php
	if ( $options['cache_buster'] ): print "checked"; endif;
?> id="misp_cache_buster"/>&nbsp;
<label for="misp_cache_buster"><?php
	_e( 'Append timestamp to filename. Useful for solving caching problems.', 'manage-image-sizes' );
?></label>
	</span>
<?php
}

// Anonymous Functions that can't be anonymous thanks to
// some versions of PHP
function noop() {}
function edit_posts_cap( $capability ) { return 'edit_posts'; }
function site_options_html() {
	_e( "These site-wide settings can only be changed by an administrator", 'manage-image-sizes' );
}

/**
 * Options in Media Settings
 *
 * @since  1.0.0
 * @return void
 */
function options_media() {

	add_settings_field(
		'hard_crop_medium',
		__( 'Medium crop', 'manage-image-sizes' ),
		__NAMESPACE__ . '\hard_crop_medium_callback',
		'media',
		'default',
		[
			__( 'Crop medium size to exact dimensions.', 'manage-image-sizes' )
		]
	);

	add_settings_field(
		'hard_crop_large',
		__( 'Large crop', 'manage-image-sizes' ),
		__NAMESPACE__ . '\hard_crop_large_callback',
		'media',
		'default',
		[
			__( 'Crop large size to exact dimensions.', 'manage-image-sizes' )
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
		__( 'Medium crop', 'manage-image-sizes' )
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
		__( 'Large crop', 'manage-image-sizes' )
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
