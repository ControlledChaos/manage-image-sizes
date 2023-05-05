<?php
/**
 * Admin init
 *
 * @package    Manage_Image_Sizes
 * @subpackage Includes
 * @category   Admin
 * @since      1.0.0
 */

namespace MISP\Admin;

use function MISP\misp_check_id;

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

	add_action( 'admin_menu',  $ns( 'admin_menu' ) );
	add_action( 'load-media_page_misp-edit', $ns( 'misp_edit_setup' ) );
}

/**
 * Plugin options page
 *
 * Displays as a submenu page under Settings.
 *
 * @since  1.0.0
 * @return void
 */
function admin_menu() {

	add_options_page(
		__( 'Manage Image Sizes', 'manage-image-sizes' ),
		__( 'Image Sizes', 'manage-image-sizes' ),
		'edit_posts',
		'misp',
		__NAMESPACE__ . '\options_page'
	);

	// Does not add a menu item.
	add_submenu_page(
		null,
		__( 'Manage Image Sizes', 'manage-image-sizes' ),
		__( 'Image Sizes', 'manage-image-sizes' ),
		'edit_posts',
		'misp-edit',
		__NAMESPACE__ . '\misp_edit_page'
	);
}

/**
 * This runs after headers have been sent, see the misp_edit_setup for the
 * function that runs before anything is sent to the browser
 */
function misp_edit_page() {

	// This is set via the misp_edit_setup function'.
	global $misp_body;
	echo( $misp_body );
}

/**
 * This hook (load-media_page_misp-edit)
 *    depends on which page you use in the admin section
 * (load-media_page_misp-edit) : wp-admin/upload.php?page=misp-edit
 * (dashboard_page_misp-edit)  : wp-admin/?page=misp-edit
 * (posts_page_misp-edit)      : wp-admin/edit.php?page=misp-edit
 */
function misp_edit_setup() {

	global $post, $title, $misp_body;

	$post_id = (int) $_GET['misp-id'];

	if (
		! isset( $post_id ) ||
		! is_int( $post_id ) ||
		! wp_attachment_is_image( $post_id ) ||
		! misp_check_id( $post_id )
	) {

		// die( "POST: $post_id IS_INT:" . is_int( $post_id ) . " ATTACHMENT: " . wp_attachment_is_image( $post_id ) );
		wp_redirect( admin_url( 'upload.php' ) );
		exit();
	}

	$post  = get_post( $post_id );
	$title = __( 'Manage Image Sizes', 'manage-image-sizes' );

	// Add the scripts and styles.
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'iris' );
	wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$misp_body = misp_body( $post->ID );
}

function options_page() {

?>
	<style type="text/css" media="screen">
	.sub-option { margin-top: 0.5em; }
	</style>
	<div class="wrap">

		<h1><?php _e( 'Manage Image Sizes', 'manage-image-sizes' ); ?></h1>

		<form action="options.php" method="post">

			<?php settings_fields( 'misp_options'); ?>
			<?php do_settings_sections( 'misp'); ?>

			<p class="submit">
				<input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes', 'manage-image-sizes' ); ?>" />
			</p>
		</form>
	</div>
<?php
}
