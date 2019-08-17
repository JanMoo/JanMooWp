<?php

/**
 * This function returns the URL of the given resource, appending the current
 * version of the plugin. The resource has to be a file in NELIOFP_ASSETS_DIR
 */
function neliofp_asset_link( $resource ) {
	$link = NELIOFP_ASSETS_URL . $resource;
	$link = add_query_arg( array( 'version' => NELIOFP_PLUGIN_VERSION ), $link );
	return $link;
}

function neliofp_split_nth( $str, $delim, $n ) {
	$arr = explode( $delim, $str );
	$arr = array_slice( $arr, 0, min( $n, count( $arr ) ) );
	return implode( $delim, $arr );
}

function neliofp_get_the_post_searcher( $field_id, $classes = array() ) {
	ob_start();
	neliofp_the_post_searcher( $field_id, $classes );
	$value = ob_get_contents();
	ob_end_clean();
	return $value;
}

function neliofp_the_post_searcher( $field_id, $classes = array() ) {
	$placeholder = __( 'Select a post...', 'nelio-featured-posts' );
	$searcher_type = 'post-searcher post';
	?>
	<input
		id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>"
		data-type="post"
		data-placeholder="<?php echo $placeholder; ?>"
		type="hidden" class="<?php
			echo $searcher_type; ?> <?php
			echo implode( ' ', $classes ); ?>" />
		<script type="text/javascript">
		(function($) {
			var field = $("#<?php echo $field_id; ?>");
			NelioFPPostSearcher.buildSearcher(field, "post");
		})(jQuery);
		</script>
	<?php
}

