<?php
global $post, $misp_iframe;
function u( $path ){
   printf( "%s%s?%s",
	   MISP_PLUGINURL,
	   $path,
	   MISP_VERSION
   );
}

?>

<!--
<base href="/wp-admin/"/>
-->
<script type="text/javascript" charset="utf-8">
   var post_id     = <?php echo $post->ID; ?>
     , post_width  = <?php echo $meta['width']; ?>
     , post_height = <?php echo $meta['height']; ?>
     , misp_nonce   = "<?php echo wp_create_nonce("misp-resize-{$post->ID}"); ?>"
     , misp_options_nonce = "<?php echo wp_create_nonce("misp-options"); ?>"
     , mispI18n     = <?php echo json_encode(
			  array( 'no_t_selected' => __( 'No thumbnails selected', MISP_DOMAIN )
			  , 'no_c_selected' => __( 'No crop selected', MISP_DOMAIN )
			  , 'crop_problems' => __( 'Cropping will likely result in skewed imagery', MISP_DOMAIN )
			  , 'save_crop_problem' => __( 'There was a problem saving the crop...', MISP_DOMAIN )
			  , 'cropSave' => __( 'Crop and Save', MISP_DOMAIN )
			  , 'crop' => __( 'Crop', MISP_DOMAIN )
			  , 'fitCrop_save' => __( 'Save', MISP_DOMAIN )
			  , 'fitCrop_transparent' => __( 'Set transparent', MISP_DOMAIN )
			  , 'transparent' => __( 'transparent/white', MISP_DOMAIN )
		  ));
?>;
<?php if ( $misp_iframe ) {
	echo 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";';
}?>
</script>

<link rel="stylesheet" href="<?php u( 'apps/font-awesome/css/font-awesome.min.css' ) ?>"/>
<link rel="stylesheet" href="<?php u( 'apps/jcrop/css/jquery.Jcrop.css' ) ?>"/>
<link rel="stylesheet" href="<?php u( 'css/misp.css' ) ?>"/>

<div class="wrap ng-cloak" ng-init="currentThumbnailBarPosition='<?php echo $options['misp_thumbnail_bar'];?>'" ng-controller="PteCtrl">
   <?php if ( !isset( $_GET['title'] ) || $_GET['title'] != 'false' ) : ?>
   <h1><?php _e( 'Manage Image Sizes', MISP_DOMAIN );?></h1>
   <p class="description"><?php _e( 'Crop images by size and by aspect ratio.', MISP_DOMAIN );?></p>
   <hr />
   <?php echo sprintf(
	   '<h2>%1s %2s</h2>',
	   __( 'Editing ', MISP_DOMAIN ),
	   $post->post_title
   ); ?>
   <?php endif; ?>
   <h3 class="nav-tab-wrapper">
      <a ng-href="" ng-class="pageClass('crop')" ng-click="changePage('crop')" class="nav-tab"><?php _e("Crop", MISP_DOMAIN); ?></a>
      <a ng-href="" ng-class="pageClass('view')" ng-click="changePage('view')" class="nav-tab"><?php _e("View", MISP_DOMAIN); ?></a>
   </h3>
   <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-1">
         <div id="post-body-content">
            <div class="error-message" ng-show="errorMessage">
               <i class="fa-times" ng-click="errorMessage = null"></i>
               <i class="fa-warning"></i>
               {{ errorMessage }}
            </div>
            <div class="info-message" ng-show="infoMessage">
               <i class="fa-times" ng-click="infoMessage = null"></i>
               <i class="fa-info-circle"></i>
               {{ infoMessage }}
            </div>

			<!-- LOADING SPINNER -->
			<div class="misp-page-switcher" ng-show="page.loading">
				<div class="misp-loading">
					<img src="<?php echo( site_url( "wp-includes/images/wpspin-2x.gif" ) ); ?>"/>
				</div>
			</div>
			<!-- END LOADING -->

            <div class="misp-page-switcher" ng-show="page.crop">
            <div id="misp-image" ng-controller="CropCtrl">
               <img id="misp-preview" src="<?php
					echo $editor_image;
               ?>"/>

               <div id="misp-crop-controls">
						<a ng-click="toggleOptions()" class="button button-secondary" ng-href=""><?php
							_e( "Options", MISP_DOMAIN ); ?>
							<i class="fa-caret-down" ng-hide="cropOptions"></i>
							<i class="fa-caret-up" ng-show="cropOptions"></i>
						</a>
						<a ng-disabled="cropInProgress" class="button button-primary" ng-href="" ng-click="submitCrop()">
							<span ng-hide="cropInProgress">{{ cropText() }}</span>
							<i ng-show="cropInProgress" class="fa-spin fa-spinner"></i>
						</a>
               </div>
					<div style="position: relative">
						<div id="misp-crop-settings" ng-show="cropOptions">
							<i class="fa-times" ng-click="toggleOptions()"></i>
							<form name="test">
							<ul>
								<li>
									<!--ui-event="{blur : 'aspectRatioBlur()'}"-->
									<label for="misp-aspect-ratio"><?php _e( "Aspect Ratio", MISP_DOMAIN ); ?>: </label>
									<input id="misp-aspect-ratio"
											type="number"
											placeholder="<?php _e( "width/height", MISP_DOMAIN ); ?>"
											ng-model="aspectRatio" ng-change="changeAR()" name="misp-aspect-ratio"/>
									<!--ng-pattern="aspectRatioPattern"/>-->
									<i class="fa-undo" ng-click="aspectRatio = null"></i>
								</li>
								<li>
									<label for="misp-crop-and-save"><?php _e("Crop and save", MISP_DOMAIN); ?></label>
									<input ng-model="mispCropSave"
											ng-init="mispCropSave = <?php print( ( $options['misp_crop_save'] ) ? 'true':'false' ); ?>"
											ng-change=""
											type="checkbox"
											name="misp-crop-and-save"
											id="misp-crop-and-save"/>
								</li>
								<li>
                                    <?php _e( "Change the current thumbnails position:", MISP_DOMAIN ); ?>&nbsp;<button ng-click="toggleCurrentThumbnailBarPosition()">{{ currentThumbnailBarPosition }}</button>
								</li>
								<?php if ( $post->post_mime_type == "image/jpeg" ): # is JPEG file ?>
								<li><label for="misp-jpg-compression"><?php _e( "JPEG Compression", MISP_DOMAIN ); ?></label>&nbsp;
									<input id="misp-jpg-compression"
										type="number"
										ng-model="mispJpgCompression"
										placeholder="<?php printf( __( "0 to 100 (Default: %d)" ), $options['misp_jpeg_compression'] ); ?>"
										min="0"
										max="100"
										name="misp-jpg-compression"/>
								</li>
								<?php endif; ?>
								<li>
								<span ng-hide="aspectRatio">
									<label for="mispFitCrop">
										<?php _e( "Fit crop to thumbnail by adding border" ); ?>
									</label>
									<input id="mispFitCrop"
												name="misp-fit-crop"
												type="checkbox"
												ng-model="mispFitCrop"
												ng-click="fitToCrop()"/>
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
                           <input type="checkbox" ng-model="tableSelector" ng-change="toggleAll()"/>
                        </th>
                        <th><?php _e( "Thumbnails" ); ?></th>
						<th class="align-right" title="<?php _e("width"); ?>"><?php _e( "W" ); ?></th>
						<th class="align-right" title="<?php _e("height"); ?>"><?php _e( "H" ); ?></th>
						<th title="<?php _e("crop"); ?>"><?php _e( "C" ); ?></th>
                        <th class="center">
                           <span class="misp-thumbnails-menu">
                              <i ng-show="anyProposed()"
                                 ng-click="save()"
                                 id="misp-save-all"
                                 title="<?php _e( "Save all", MISP_DOMAIN ); ?>"
                                 class="fa-save"></i>
                              <i ng-show="anyProposed()"
                                 ng-click="trashAll(); $event.stopPropagation()"
                                 id="misp-reset-all"
                                 title="<?php _e( "Reset all", MISP_DOMAIN ); ?>"
                                 class="fa-trash-o"></i>
                              <i ng-click="view(anyProposed());"
                                 id="misp-view-modified"
                                 title="<?php _e( 'View all/modified', MISP_DOMAIN ); ?>"
                                 class="fa-search"></i>
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
                           <input type="checkbox"
                              ng-click="$event.stopPropagation()"
                              ng-model="thumbnail.selected"
                              ng-change="updateSelected()"/>

                        </td>
                        <td>{{ thumbnail.display_name || thumbnail.name }}</td>
                        <td class="align-right">{{ thumbnail.width }}</td>
                        <td class="align-right">{{ thumbnail.height }}</td>
                        <td>{{ thumbnail.crop }}</td>
                        <td class="center misp-thumbnail-options">
                           <span class="misp-thumbnail-menu">
                              <i ng-show="thumbnail.proposed"
                                 ng-click="save(thumbnail)"
                                 title="<?php _e( "Save", MISP_DOMAIN ); ?>" class="fa-save"></i>
                              <i ng-show="thumbnail.proposed"
                                 ng-click="trash(thumbnail); $event.stopPropagation()"
                                 title="<?php _e( "Reset", MISP_DOMAIN ); ?>" class="fa-trash-o"></i>
                              <i ng-show="thumbnail.proposed"
                                 ng-click="changePage('view'); view(thumbnail.name); $event.stopPropagation();"
                                 title="<?php _e( "Compare/View", MISP_DOMAIN ); ?>" class="fa-search"></i>
                           </span>
                        </td>
                     </tr>
                  </tbody>
               </table>
               <div id="aspect-ratio-selector" ng-show="aspectRatios.length">
                  <?php _e( "These thumbnails have an aspect ratio set:", MISP_DOMAIN ); ?>
                  <ul>
                     <li ng-repeat="aspectRatio in aspectRatios | orderBy:size">
                        <a ng-click="selectAspectRatio(aspectRatio)" ng-href="">
                           <i class="fa-chevron-right"></i>
                           {{ aspectRatio.thumbnails.toString().replace(",",", ") }}</a></li>
                  </ul>
               </div>
               <div ng-class="currentThumbnailBarPosition" id="misp-remember" ng-show="anySelected()">
                   <h4><?php _e( "Current Thumbnails", MISP_DOMAIN ); ?></h4>
                   <ul id="misp-remember-list">
                       <li ng-repeat="thumbnail in thumbnails | filter:{selected:true}">
                           <img ng-src="{{ thumbnail.current.url | randomizeUrl }}"
                                   ng-show="thumbnail.current"
                                   alt="{{ thumbnail.name }}"
                                   title="{{ thumbnail.name }}"/>
                           <span title="{{ thumbnail.name }}" class="no-current-image" ng-hide="thumbnail.current">
                               <i class="fa-exclamation-circle"></i>
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
                     <div class="misp-display-thumbnail-menu" ng-show="thumbnail.proposed">
                        <button ng-click="thumbnail.showProposed = !thumbnail.showProposed"><i class="fa-refresh"></i></button>
                        <br/>
                        <button ng-click="save(thumbnail)" ng-show="thumbnail.showProposed"><i class="fa-save"></i></button>
                        <br/>
                        <button ng-click="trash(thumbnail); $event.stopPropagation()" ng-show="thumbnail.showProposed"><i class="fa-trash-o"></i></button>
                     </div>
                     <div
                        ng-dblclick="changePage('crop');$event.stopPropagation();"
                        ng-click="thumbnail.selected = !thumbnail.selected;updateSelected();"
                        ng-hide="thumbnail.showProposed">
                        <span ng-show="thumbnail.proposed"><strong><?php _e( "Original", MISP_DOMAIN ); ?>: {{ thumbnail.name }}</strong><br/></span>
                        <img ng-src="{{ thumbnail.current.url | randomizeUrl }}"
                              ng-show="thumbnail.current"
                              alt="{{ thumbnail.name }}"
                              title="{{ thumbnail.name }}"/>
                        <span class="no-current-image" ng-hide="thumbnail.current">
                           <i class="fa-exclamation-circle"></i>
                           <?php _e( "No image has been generated yet for image: ", MISP_DOMAIN ) ?> '{{ thumbnail.name }}'
                        </span>
                     </div>
                     <div
                        ng-dblclick="changePage('crop');$event.stopPropagation();"
                        ng-click="thumbnail.selected = !thumbnail.selected;updateSelected();"
                        ng-show="thumbnail.showProposed">
                        <span><strong><?php _e( "Proposed", MISP_DOMAIN ); ?>: {{ thumbnail.name }}</strong><br/></span>
                              <!--ng-click="selectThumb(thumbnail)"-->
                        <img ng-src="{{ thumbnail.proposed.url | randomizeUrl }}"
                              ng-show="thumbnail.showProposed"
                              alt="{{ thumbnail.name }}"
                              title="{{ thumbnail.name }}"/>
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

function enqueue_script_filter($tag, $handle) {
	if ('misp-require' !== $handle)
		return $tag;
	return str_replace(' src', ' data-main="' . MISP_PLUGINURL . 'js/main" src', $tag);
}

function enqueue_last() {
	wp_enqueue_script(
		'misp-require',
		MISP_PLUGINURL . "apps/requirejs/require.js",
		null,
		MISP_VERSION,
		true
	);
}

$options = misp_get_options();

if ( $options['misp_debug'] ) {
	add_action('wp_print_footer_scripts', 'enqueue_last', 1, 0);
	add_action('admin_print_footer_scripts', 'enqueue_last', 1, 0);
	add_filter('script_loader_tag', 'enqueue_script_filter', 10, 2);
}
else {
	wp_enqueue_script(
		'misp-min-js',
		MISP_PLUGINURL . "js-build/main.js",
		null,
		MISP_VERSION,
		true
	);
}
