<?php
/**
 * Plugin options
 *
 * @package    Manage_Image_Sizes
 * @subpackage Options
 * @since      1.0.0
 */

function misp_options_init() {

	add_filter( 'option_page_capability_misp_options', 'misp_edit_posts_cap' );

	register_setting(
		'misp_options',
		MISP\get_option_name(),
		'misp_options_validate'
	);

	add_settings_section(
		'misp_main',
		 __( 'User Options', MISP_DOMAIN ),
		 'misp_noop',
		 'misp'
	);

	/*
	add_settings_field(
		'misp_debug',
		__( 'Debug', MISP_DOMAIN ),
		'misp_debug_display',
		'misp',
		'misp_main'
	);
	*/

	add_settings_field(
		'misp_crop_save',
		__( 'Crop and Save', MISP_DOMAIN ),
		'misp_crop_save_display',
		'misp',
		'misp_main'
	);

	add_settings_field(
		'misp_imgedit_max_size',
		__( 'Crop Picture Size', MISP_DOMAIN ),
		'misp_imgedit_size_display',
		'misp',
		'misp_main'
	);

	add_settings_field(
		'misp_reset',
		__( 'Reset to defaults', MISP_DOMAIN ),
		'misp_reset_display',
		'misp',
		'misp_main'
	);

	// Only show for admins.
	if ( current_user_can( 'manage_options' ) ) {

		register_setting(
			'misp_options',
			'misp-site-options',
			'misp_site_options_validate'
		);

		add_settings_section(
			'misp_site',
			__( 'Site Options', MISP_DOMAIN ),
			'misp_site_options_html',
			'misp'
		);

		add_settings_field(
			'misp_sizes',
			__( 'Thumbnails', MISP_DOMAIN ),
			'misp_sizes_display',
			'misp',
			'misp_site'
		);

		add_settings_field(
			'misp_jpeg_compression',
			__( 'JPEG Compression', MISP_DOMAIN ),
			'misp_jpeg_compression_display',
			'misp',
			'misp_site'
		);

		add_settings_field(
			'misp_cache_buster',
			__( 'Cache Buster', MISP_DOMAIN ),
			'misp_cache_buster_display',
			'misp',
			'misp_site'
		);
	}
	// End manage_options only.

}

function misp_options_page() {

?>
	<style type="text/css" media="screen">
	.sub-option {
		margin-left: 30px;
		margin-top: 10px;
		font-size: smaller;
	}
	</style>
	<div class="wrap">

		<h2><?php _e( 'Manage Image Sizes', MISP_DOMAIN ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'misp_options'); ?>

			<?php do_settings_sections( 'misp'); ?>

			<p class="submit">
				<input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes', MISP_DOMAIN ); ?>" />
			</p>
		</form>
	</div>
<?php
}

/*********** Internal to options **************************************/

function misp_site_options_validate( $input ){
	//$sizes = misp_get_alternate_sizes(false);
	if ( !current_user_can( 'manage_options' ) ){
		add_settings_error( 'misp_options_site'
			, 'misp_options_error'
			, __( "Only users with the 'manage_options' capability may make changes to these settings.", MISP_DOMAIN ) );
		return misp_get_site_options();
	}
	$sizes = get_intermediate_image_sizes();

	$misp_hidden_sizes = array();

	foreach ( $sizes as $size ){
		// Hidden
		if ( isset($input['misp_hidden_sizes']) && is_array( $input['misp_hidden_sizes'] )
			&& in_array( $size, $input['misp_hidden_sizes'] ) ){
				$misp_hidden_sizes[] = $size;
			}
	}

	$output = array( 'misp_hidden_sizes' => $misp_hidden_sizes );

	// Check the JPEG Compression value
	if ( isset($input['misp_jpeg_compression']) && $input['misp_jpeg_compression'] != "" ){
		$tmp_jpeg_compression = (int) preg_replace( "/[\D]/", "", $input['misp_jpeg_compression'] );
		if ( ! is_int( $tmp_jpeg_compression )
			|| $tmp_jpeg_compression < 0
			|| $tmp_jpeg_compression > 100 )
		{
			add_settings_error( 'misp_options_site'
				, 'misp_options_error'
				, __( "JPEG Compression needs to be set from 0 to 100.", MISP_DOMAIN ) . $tmp_jpeg_compression . "/" . $input['misp_jpeg_compression']);
		}
		$output['misp_jpeg_compression'] = $tmp_jpeg_compression;
	}

	// Cache Buster
	$output['cache_buster'] = isset( $input['misp_cache_buster'] );

	return $output;
}

function misp_options_validate( $input ){
	$options = MISP\misp_get_user_options();

	if ( isset( $input['reset'] ) ){
		return array();
	}
	$checkboxes = array(
		'misp_debug',
		'misp_debug_out_chrome',
		'misp_debug_out_file',
		'misp_crop_save',
		'misp_imgedit_disk',
	);

	foreach ($checkboxes as $opt) {
		if (isset( $input[$opt] ) )
			$options[$opt] = true;
		else if (isset($options[$opt]))
			unset($options[$opt]);
	}

	// Check the imgedit_max_size value
	if ( $input['misp_imgedit_max_size'] != "" ){
		$tmp_size = (int) preg_replace( "/[\D]/", "", $input['misp_imgedit_max_size'] );
		if ( $tmp_size < 0 || $tmp_size > 10000 ) {
			add_settings_error( MISP\get_option_name()
				, 'misp_options_error'
				, __( "Crop Size must be between 0 and 10000.", MISP_DOMAIN ) );
		}
		$options['misp_imgedit_max_size'] = $tmp_size;
	}
	else{
		unset( $options['misp_imgedit_max_size'] );
	}

	return $options;
}

function misp_debug_display(){
	$options = MISP\misp_get_user_options();
	$option_label = MISP\get_option_name();
?>
	<span><input type="checkbox" name="<?php
	print $option_label;
	?>[misp_debug]" <?php
	if ( $options['misp_debug'] ): print "checked"; endif;
	?> id="misp_debug"/>&nbsp;<label for="misp_debug"><?php _e( 'Enable debugging', MISP_DOMAIN ); ?></label>
<?php if ( WP_DEBUG ) {
	print( "<br/><em>" );
	_e( "WP_DEBUG is currently set to true and will override this setting. (debug is enabled)" );
	print( "</em>" );
}?>
	</span>
	<div class="sub-option"><input type="checkbox" name="<?php echo $option_label; ?>[misp_debug_out_chrome]" <?php
		if ( $options['misp_debug_out_chrome'] ): print "checked"; endif; ?> id="misp_debug_out_chrome"/>
		&nbsp;
		<label for="misp_debug_out_chrome"><?php printf( __( 'Use <a href="%s">ChromePhp</a> for log output'),
			'https://github.com/ccampbell/chromephp'
		); ?></label>
	</div>
	<div class="sub-option"><input type="checkbox" name="<?php echo $option_label; ?>[misp_debug_out_file]" <?php
		if ( $options['misp_debug_out_file'] ): print "checked"; endif; ?> id="misp_debug_out_file"/>
		&nbsp;
		<label for="misp_debug_out_file"><?php printf(
			__( 'Write log output to a <a href="%s">file</a>'),
			PteLogFileHandler::getLogFileUrl()
		); ?></label>
	</div>
<?php
}

function misp_crop_save_display(){
	$options = MISP\misp_get_user_options();
	$option_label = MISP\get_option_name();
?>
	<span><input type="checkbox" name="<?php
	print $option_label;
	?>[misp_crop_save]" <?php
	if ( $options['misp_crop_save'] ): print "checked"; endif;
	?> id="misp_crop_save"/>&nbsp;<label for="misp_crop_save"><?php _e( 'I know what I\'m doing, bypass the image verification.', MISP_DOMAIN ); ?></label>
		</span>
<?php
}

function misp_imgedit_size_display(){
	$options = MISP\misp_get_user_options();
	$option_label = MISP\get_option_name();
?>
	<span><input class="small-text" type="text"
			name="<?php print $option_label; ?>[misp_imgedit_max_size]"
			value="<?php if ( isset( $options['misp_imgedit_max_size'] ) ){ print $options['misp_imgedit_max_size']; }?>"
			id="misp_imgedit_max_size">&nbsp;
	<?php _e("Set the max size for the crop image.", MISP_DOMAIN ); ?>
	<br/><em><?php _e("No entry defaults to 600", MISP_DOMAIN ); ?></em>
	</span>
	<div class="sub-option">
	<span><input type="checkbox"
			name="<?php print $option_label; ?>[misp_imgedit_disk]"
			<?php if ($options['misp_imgedit_disk'] ): print "checked"; endif; ?>
			id="misp_imgedit_disk">&nbsp;<label for="misp_imgedit_disk">
		<?php _e("Check this to save the generated working image to disk instead of creating on the fly (experimental)", MISP_DOMAIN ); ?>
	</label>
	</span>
	</div>
<?php
}

function misp_reset_display(){
?>
	<input class="button-secondary" name="<?php
	echo( MISP\get_option_name() );
	?>[reset]" type="submit" value="<?php esc_attr_e( 'Reset User Options', MISP_DOMAIN ); ?>" />
<?php
}

function misp_gcd($a, $b){
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

function misp_sizes_display(){
	require_once( 'functions.php' );
	$options = MISP\misp_get_options();

	// Table Header
?>
	<table><tr><th><?php _e("Post Thumbnail", MISP_DOMAIN ); ?></th>
		<th><?php _e( "Hidden", MISP_DOMAIN ); ?></th>
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

function misp_jpeg_compression_display(){
	$options = MISP\misp_get_site_options();
?>
	<span><input class="small-text" type="text"
			 name="misp-site-options[misp_jpeg_compression]"
			 value="<?php if ( isset( $options['misp_jpeg_compression'] ) ){ print $options['misp_jpeg_compression']; }?>"
			 id="misp_jpeg_compression">&nbsp;
	<?php _e("Set the compression level for resizing jpeg images (0 to 100).", MISP_DOMAIN ); ?>
	<br/><em><?php _e("No entry defaults to using the 'jpeg_quality' filter or 90", MISP_DOMAIN ); ?></em>
	</span>
<?php
}

function misp_cache_buster_display(){
	$options = MISP\misp_get_site_options();
?>
	<span><input type="checkbox" name="misp-site-options[misp_cache_buster]" <?php
	if ( $options['cache_buster'] ): print "checked"; endif;
?> id="misp_cache_buster"/>&nbsp;
<label for="misp_cache_buster"><?php
	_e( 'Append timestamp to filename. Useful for solving caching problems.', MISP_DOMAIN );
?></label>
	</span>
<?php
}

// Anonymous Functions that can't be anonymous thanks to
// some versions of PHP
function misp_noop(){}
function misp_edit_posts_cap( $capability ){ return 'edit_posts'; }
function misp_site_options_html(){
	_e( "These site-wide settings can only be changed by an administrator", MISP_DOMAIN );
}
