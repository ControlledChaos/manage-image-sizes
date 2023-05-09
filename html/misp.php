<?php

use function MISP\get_plugin_options;

global $post, $misp_iframe;

function asset( $path ) {
   printf( '%s%s?%s',
	   MISP_URL,
	   $path,
	   MISP_VERSION
   );
}

?>

<!--
<base href="/wp-admin/" />
-->
<script type="text/javascript" charset="utf-8">
	var post_id     = <?php echo $post->ID; ?>,
   		post_width  = <?php echo $meta['width']; ?>,
		post_height = <?php echo $meta['height']; ?>,
		misp_nonce  = "<?php echo wp_create_nonce( "misp-resize-{$post->ID}" ); ?>",
		misp_options_nonce = "<?php echo wp_create_nonce( 'misp-options' ); ?>",
		mispI18n    = <?php echo json_encode(
			[
				'no_t_selected' => __( 'No thumbnails selected', MISP_DOMAIN ),
				'no_c_selected' => __( 'No crop selected', MISP_DOMAIN ),
				'crop_problems' => __( 'Cropping will likely result in skewed imagery', MISP_DOMAIN ),
				'save_crop_problem' => __( 'There was a problem saving the crop...', MISP_DOMAIN ),
				'cropSave'      => __( 'Crop and Save', MISP_DOMAIN ),
				'crop'          => __( 'Crop', MISP_DOMAIN ),
				'fitCrop_save'  => __( 'Save', MISP_DOMAIN ),
				'fitCrop_transparent' => __( 'Set transparent', MISP_DOMAIN ),
				'transparent'   => __( 'transparent/white', MISP_DOMAIN )
			]
		);
?>;
<?php if ( $misp_iframe ) {
	echo 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";';
}?>
</script>

<link rel="stylesheet" href="<?php asset( 'apps/font-awesome/css/font-awesome.min.css' ); ?>" />
<link rel="stylesheet" href="<?php asset( 'apps/jcrop/css/jquery.Jcrop.css' ); ?>" />
<link rel="stylesheet" href="<?php asset( 'assets/css/misp.css' ); ?>" />

<div class="wrap ng-cloak" ng-init="currentThumbnailBarPosition='<?php echo $options['misp_thumbnail_bar']; ?>'" ng-controller="PteCtrl">
	<?php if ( ! isset( $_GET['title'] ) || $_GET['title'] != 'false' ) : ?>

	<h1><?php _e( 'Manage Image Sizes', MISP_DOMAIN ); ?></h1>
	<p class="description"><?php _e( 'Crop images by size and by aspect ratio.', MISP_DOMAIN ); ?></p>

	<?php echo sprintf(
		'<h2>%1s %2s</h2>',
		__( 'Editing ', MISP_DOMAIN ),
		$post->post_title
	); ?>
	<?php endif; ?>
	<h3 class="nav-tab-wrapper">
		<a ng-href="" ng-class="pageClass('crop')" ng-click="changePage('crop')" class="nav-tab"><?php _e( 'Crop', MISP_DOMAIN ); ?></a>
		<a ng-href="" ng-class="pageClass('view')" ng-click="changePage('view')" class="nav-tab"><?php _e( 'View', MISP_DOMAIN ); ?></a>
	</h3>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content">
			<div class="error-message" ng-show="errorMessage">
				<span class="fa fa-times" ng-click="errorMessage = null"></span>
				<span class="fa fa-warning"></span>
				{{ errorMessage }}
			</div>
			<div class="info-message" ng-show="infoMessage">
				<span class="fa fa-times" ng-click="infoMessage = null"></span>
				<span class="fa fa-info-circle"></span>
				{{ infoMessage }}
			</div>

			<!-- Loading spinner -->
			<div class="misp-page-switcher" ng-show="page.loading">
				<div class="misp-loading">
					<img src="<?php echo( site_url( 'wp-includes/images/wpspin-2x.gif' ) ); ?>" />
				</div>
			</div>

			<div class="misp-page-switcher" ng-show="page.crop">
				<div id="misp-image" ng-controller="CropCtrl">
					<img id="misp-preview" src="<?php echo $editor_image; ?>" />

					<div id="misp-crop-controls">
						<a ng-click="toggleOptions()" class="button button-secondary" ng-href=""><?php _e( 'Options', MISP_DOMAIN ); ?>
							<span class="fa fa-caret-down" ng-hide="cropOptions"></span>
							<span class="fa fa-caret-up" ng-show="cropOptions"></span>
						</a>
						<a ng-disabled="cropInProgress" class="button button-primary" ng-href="" ng-click="submitCrop()">
							<span ng-hide="cropInProgress">{{ cropText() }}</span>
							<span ng-show="cropInProgress" class="fa fa-spin fa-spinner"></span>
						</a>
					</div>
					<div style="position: relative">
						<div id="misp-crop-settings" ng-show="cropOptions">
							<span class="fa fa-times" ng-click="toggleOptions()"></span>
							<form name="test">
								<ul>
									<li>
										<!--ui-event="{blur : 'aspectRatioBlur()'}"-->
										<label for="misp-aspect-ratio"><?php _e( 'Aspect Ratio', MISP_DOMAIN ); ?>: </label>
										<input id="misp-aspect-ratio"
												type="number"
												placeholder="<?php _e( 'width/height', MISP_DOMAIN ); ?>"
												ng-model="aspectRatio" ng-change="changeAR()" name="misp-aspect-ratio" />
										<!--ng-pattern="aspectRatioPattern" />-->
										<span class="fa fa-undo" ng-click="aspectRatio = null"></span>
									</li>
									<li>
										<label for="misp-crop-and-save"><?php _e( 'Crop and save', MISP_DOMAIN ); ?></label>
										<input ng-model="mispCropSave"
												ng-init="mispCropSave = <?php print( ( $options['misp_crop_save'] ) ? 'true' : 'false' ); ?>"
												ng-change=""
												type="checkbox"
												name="misp-crop-and-save"
												id="misp-crop-and-save" />
									</li>
									<li>
										<?php _e( 'Change the current thumbnails position:', MISP_DOMAIN ); ?>&nbsp;<button ng-click="toggleCurrentThumbnailBarPosition()">{{ currentThumbnailBarPosition }}</button>
									</li>
									<?php if ( $post->post_mime_type == 'image/jpeg' ): # is JPEG file ?>
									<li>
										<label for="misp-jpg-compression"><?php _e( 'JPEG Compression', MISP_DOMAIN ); ?></label>&nbsp;
										<input id="misp-jpg-compression"
											type="number"
											ng-model="mispJpgCompression"
											placeholder="<?php printf( __( '0 to 100 (Default: %d)' ), $options['misp_jpeg_compression'] ); ?>"
											min="0"
											max="100"
											name="misp-jpg-compression" />
									</li>
									<?php endif; ?>
									<li>
									<span ng-hide="aspectRatio">
										<label for="mispFitCrop">
											<?php _e( 'Fit crop to thumbnail by adding border' ); ?>
										</label>
										<input id="mispFitCrop"
												name="misp-fit-crop"
												type="checkbox"
												ng-model="mispFitCrop"
												ng-click="fitToCrop()" />
										<span ng-click="fitToCrop()">{{ mispFitCropColor }}</span>
									</span>
									</li>
								</ul>
							</form>
						</div>
					</div>
				</div>
				<div id="misp-thumbnail-column" ng-controller="TableCtrl">
					<table id="misp-thumbnail-table" class="wp-list-table widefat" >
						<thead>
							<tr>
							<th class="center">
								<input type="checkbox" ng-model="tableSelector" ng-change="toggleAll()" />
							</th>
							<th><?php _e( 'Thumbnails', MISP_DOMAIN ); ?></th>
							<th class="align-right" title="<?php _e( 'width', MISP_DOMAIN ); ?>"><?php _e( 'W', MISP_DOMAIN ); ?></th>
							<th class="align-right" title="<?php _e( 'height', MISP_DOMAIN ); ?>"><?php _e( 'H', MISP_DOMAIN ); ?></th>
							<th title="<?php _e( 'crop', MISP_DOMAIN ); ?>"><?php _e( 'C', MISP_DOMAIN ); ?></th>
							<th class="center">
								<span class="misp-thumbnails-menu">
									<span ng-show="anyProposed()"
										ng-click="save()"
										id="misp-save-all"
										title="<?php _e( 'Save all', MISP_DOMAIN ); ?>"
										class="fa fa-save"></span>
									<span ng-show="anyProposed()"
										ng-click="trashAll(); $event.stopPropagation()"
										id="misp-reset-all"
										title="<?php _e( 'Reset all', MISP_DOMAIN ); ?>"
										class="fa fa-trash-o"></span>
									<span ng-click="view(anyProposed());"
										id="misp-view-modified"
										title="<?php _e( 'View all/modified', MISP_DOMAIN ); ?>"
										class="fa fa-search"></span>
								</span>
							</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-class="'selected-'+thumbnail.selected"
								ng-click="toggleSelected(thumbnail)"
								ng-class-odd="'alternate'"
								ng-repeat="thumbnail in thumbnails">
								<td class="center">
									<input
										type="checkbox"
										ng-click="$event.stopPropagation()"
										ng-model="thumbnail.selected"
										ng-change="updateSelected()" />

								</td>
								<td>{{ thumbnail.display_name || thumbnail.name }}</td>
								<td class="align-right">{{ thumbnail.width }}</td>
								<td class="align-right">{{ thumbnail.height }}</td>
								<td>{{ thumbnail.crop }}</td>
								<td class="center misp-thumbnail-options">
									<span class="misp-thumbnail-menu">
										<span ng-show="thumbnail.proposed"
											ng-click="save(thumbnail)"
											title="<?php _e( 'Save', MISP_DOMAIN ); ?>" class="fa fa-save"></span>
										<span ng-show="thumbnail.proposed"
											ng-click="trash(thumbnail); $event.stopPropagation()"
											title="<?php _e( 'Reset', MISP_DOMAIN ); ?>" class="fa fa-trash-o"></span>
										<span ng-show="thumbnail.proposed"
											ng-click="changePage('view'); view(thumbnail.name); $event.stopPropagation();"
											title="<?php _e( 'Compare/View', MISP_DOMAIN ); ?>" class="fa fa-search"></span>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
					<div id="aspect-ratio-selector" ng-show="aspectRatios.length">
						<h4><?php _e( 'These thumbnails have an aspect ratio set:', MISP_DOMAIN ); ?></h4>
						<ul>
							<li ng-repeat="aspectRatio in aspectRatios | orderBy:size">
							<a ng-click="selectAspectRatio(aspectRatio)" ng-href="">
								<span class="fa fa-chevron-right"></span>
								{{ aspectRatio.thumbnails.toString().replace(",",", ") }}</a></li>
						</ul>
					</div>
					<div ng-class="currentThumbnailBarPosition" id="misp-remember" ng-show="anySelected()">
						<h4><?php _e( 'Current Thumbnails', MISP_DOMAIN ); ?></h4>
						<ul id="misp-remember-list">
							<li ng-repeat="thumbnail in thumbnails | filter:{selected:true}">
								<img ng-src="{{ thumbnail.current.url | randomizeUrl }}"
										ng-show="thumbnail.current"
										alt="{{ thumbnail.name }}"
										title="{{ thumbnail.name }}" />
								<span title="{{ thumbnail.name }}" class="no-current-image not-generated-list" ng-hide="thumbnail.current">
									<span class="fa fa-exclamation-circle"></span>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="misp-page-switcher" ng-show="page.view" ng-controller="ViewCtrl">
				<div class="misp-display-thumbnail"
						ng-repeat="thumbnail in thumbnails | filter:viewFilterFunc | orderBy:orderBy">
					<div class="misp-display-thumbnail-image" ng-class="thumbnailClass(thumbnail)">
						<div
						ng-dblclick="changePage('crop');$event.stopPropagation();"
						ng-click="thumbnail.selected = !thumbnail.selected;updateSelected();"
						ng-hide="thumbnail.showProposed">
						<span ng-show="thumbnail.proposed"><strong><?php _e( 'Original', MISP_DOMAIN ); ?>: {{ thumbnail.name }}</strong><br/></span>
						<img ng-src="{{ thumbnail.current.url | randomizeUrl }}"
								ng-show="thumbnail.current"
								alt="{{ thumbnail.name }}"
								title="{{ thumbnail.name }}" />
						<span class="no-current-image not-generated-message" ng-hide="thumbnail.current">
							<span class="fa fa-exclamation-circle"></span>
							<?php _e( 'No image has been generated yet for size ', MISP_DOMAIN ); ?> '{{ thumbnail.name }}'
						</span>
						</div>
						<div
						ng-dblclick="changePage('crop');$event.stopPropagation();"
						ng-click="thumbnail.selected = !thumbnail.selected;updateSelected();"
						ng-show="thumbnail.showProposed">
						<span><strong><?php _e( 'Proposed crop for', MISP_DOMAIN ); ?> {{ thumbnail.name }} <?php _e( 'size', MISP_DOMAIN ); ?></strong><br/></span>
						<!--ng-click="selectThumb(thumbnail)"-->
						<img ng-src="{{ thumbnail.proposed.url | randomizeUrl }}"
								ng-show="thumbnail.showProposed"
								alt="{{ thumbnail.name }}"
								title="{{ thumbnail.name }}" />
						</div>
						<div class="misp-display-thumbnail-menu" ng-show="thumbnail.proposed">
						<button ng-click="thumbnail.showProposed = !thumbnail.showProposed"><span class="fa fa-refresh"></span></button>
						<button ng-click="save(thumbnail)" ng-show="thumbnail.showProposed"><span class="fa fa-save"></span></button>
						<button ng-click="trash(thumbnail); $event.stopPropagation()" ng-show="thumbnail.showProposed"><span class="fa fa-trash-o"></span></button>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
<div id="misp-iris-dialog">
	<input type="text" name="mispIris" id="mispIris" value="" />
</div>
<?php

function enqueue_script_filter( $tag, $handle ) {

	if ( 'misp-require' !== $handle ) {
		return $tag;
	}
	return str_replace( ' src', ' data-main="' . MISP_URL . 'js/main" src', $tag );
}

function enqueue_last() {
	wp_enqueue_script( 'misp-require', MISP_URL . 'apps/requirejs/require.js', null, MISP_VERSION, true );
}

$options = get_plugin_options();

if ( $options['misp_debug'] ) {
	add_action( 'wp_print_footer_scripts', 'enqueue_last', 1, 0 );
	add_action( 'admin_print_footer_scripts', 'enqueue_last', 1, 0 );
	add_filter( 'script_loader_tag', 'enqueue_script_filter', 10, 2 );
} else {
	wp_enqueue_script( 'misp-min-js', MISP_URL . 'js-build/main.js', null, MISP_VERSION, true );
}
