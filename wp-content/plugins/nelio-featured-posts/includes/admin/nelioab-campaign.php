<?php

if ( !function_exists( 'nelioab_campaign_notice' ) ) {

	add_action( 'wp_ajax_nelioab_campaign_dismiss_notice',
		'nelioab_campaign_dismiss_notice' );
	function nelioab_campaign_dismiss_notice() {
		update_option( 'nelioab_campaign_last_dismiss', time() );
		die();
	}

	$INTERVAL = 2592000; // 1 MONTH in seconds
	if ( get_option( 'nelioab_campaign_last_dismiss', 0 ) + $INTERVAL < time() )
		add_action( 'admin_notices', 'nelioab_campaign_notice' );
	function nelioab_campaign_notice() {
		$nelioab_plugin = WP_PLUGIN_DIR . '/nelio-ab-testing/main.php';
		if ( file_exists( $nelioab_plugin ) )
			return;

		$message = '';
		$messages = array();

		$messages[0] = sprintf(
			__( '<strong>Nelio A/B Testing</strong> will help you deliver to every reader a <a href="%s">more engaging and relevant reading experience</a>.', 'nelio-featured-posts' ),
			esc_url( 'https://neliosoftware.com/testing/publishers/?mailing=neliofp' )
		);

		$messages[1] = sprintf(
			__( 'Do you want to get more money out of your site? <strong>Nelio A/B Testing</strong> is a <a href="%s">native conversion optimization service for WordPress</a> that will help you improve your site.', 'nelio-featured-posts' ),
			esc_url( 'https://neliosoftware.com/testing/business/?mailing=neliofp' )
		);

		$messages[2] = __( 'Do you want to improve your headlines and get more readers? <strong>Nelio A/B Testing</strong> will help you decide which headlines are more appealing!', 'nelio-featured-posts' );

		$messages[3] = __( 'If you don\'t know which is the best featured image for a post, use <strong>Nelio A/B Testing</strong>. It\'ll tell you which one is more appealing to your users!', 'nelio-featured-posts' );

		$messages[4] = sprintf(
			__( 'Have you ever wondered what your visitors do in your site? Subscribe to <strong>Nelio A/B Testing</strong> for free and get insightful <a href="%s">Heatmaps and Clickmaps</a>.', 'nelio-featured-posts' ),
			esc_url( 'https://neliosoftware.com/blog/heatmaps-teach-us/?mailing=neliofp' )
		);

		$message = $messages[mt_rand( 0, count( $messages )-1 )];
		if ( wp_count_posts( 'post' )->publish > 5 * wp_count_posts( 'page' )->publish )
			if ( mt_rand( 0, 100 ) < 30 )
				$message = $messages[0];

		$message .= ' ' . sprintf(
			__( '<strong>Check our <a href="%s">subscription plans</a> and try it for free</strong>.', 'nelio-featured-posts' ),
			esc_url( 'https://neliosoftware.com/testing/pricing/?mailing=neliofp' )
		);

		?>
		<div class="updated">
			<p style="float:right;font-size:10px;text-align:right;">
				<a id="dismiss-nelioab-campaign" href="#"><?php _e( 'Dismiss' ); ?></a>
			</p>
			<p style="font-size:15px;"><?php echo $message; ?></p>
			<script style="display:none;" type="text/javascript">
			(function($) {
				$('a#dismiss-nelioab-campaign').on('click', function() {
					$.post( ajaxurl, {action:'nelioab_campaign_dismiss_notice'} );
					$(this).parent().parent().fadeOut();
				});
			})(jQuery);
			</script>
		</div>
		<?php
	}

}

