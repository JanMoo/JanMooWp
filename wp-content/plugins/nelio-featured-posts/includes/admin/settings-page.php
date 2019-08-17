<?php

class NelioFPSettingsPage {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		add_posts_page(
			'Nelio Featured Posts',
			__( 'Featured by Nelio', 'nelio-featured-posts' ),
			'manage_options',
			'neliofp-settings',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = NelioFPSettings::get_settings();
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php esc_html_e( 'Featured Posts by Nelio', 'nelio-featured-posts' ); ?></h2>
			<br />
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'neliofp_settings_group' );
				do_settings_sections( 'neliofp-settings' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {

		require_once( NELIOFP_ADMIN_DIR . '/ajax.php' );
		add_action( 'wp_ajax_neliofp_search_posts',   'neliofp_search_posts' ) ;
		add_action( 'wp_ajax_neliofp_get_post_by_id', 'neliofp_get_post_by_id' ) ;

		if ( isset( $_GET['page'] ) && 'neliofp-settings' === $_GET['page'] ) {
			// Post Searcher
			wp_enqueue_style( 'neliofp_style_css',
				neliofp_asset_link( '/admin/style.min.css' ) );
			wp_enqueue_style( 'neliofp_select2_css',
				neliofp_asset_link( '/admin/lib/select2-3.5.0/select2.min.css' ) );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'neliofp_select2',
				neliofp_asset_link( '/admin/lib/select2-3.5.0/select2.min.js' ) );
			wp_enqueue_style( 'neliofp_post_searcher_css',
				neliofp_asset_link( '/admin/post-searcher.min.css' ) );
			wp_enqueue_script( 'neliofp_post_searcher',
				neliofp_asset_link( '/admin/post-searcher.min.js' ) );
		}

		register_setting(
			'neliofp_settings_group',
			'neliofp_settings',
			array( 'NelioFPSettings', 'sanitize' )
		);

		add_settings_section(
			'feat_posts_section',
		// ================================================================
			__( 'Featured Posts', 'nelio-featured-posts' ),
		// ================================================================
			array( $this, 'print_feat_post_section' ),
			'neliofp-settings'
		);

		add_settings_section(
			'advanced_section',
		// ================================================================
			__( 'Advanced Settings', 'nelio-featured-posts' ),
		// ================================================================
			array( $this, 'print_section_info' ),
			'neliofp-settings'
		);

		add_settings_field(
			'use_feat_image',
			__( 'Print Featured Image', 'nelio-featured-posts' ),
		// ----------------------------------------------------------------
			array( $this, 'use_feat_image_callback' ),
			'neliofp-settings',
			'advanced_section'
		);

	}

	public function print_feat_post_section() {
		$fn = 'feat_posts';
		neliofp_the_post_searcher( 'neliofp-searcher' ); ?>
		<a id="neliofp-add-first" class="button button-primary"><?php esc_html_e( 'Add First', 'nelio-featured-posts' ); ?></a>
		<a id="neliofp-add-last" class="button"><?php esc_html_e( 'Add Last', 'nelio-featured-posts' ); ?></a>

		<br><br>
		<h4><?php esc_html_e( 'These are your featured posts:', 'nelio-featured-posts' ); ?></h4>
		<div id="neliofp-list-of-feat-posts">
		</div>

		<?php
		$fn = 'list_of_feat_post_ids';
		printf(
			'<input type="hidden" id="%1$s" name="neliofp_settings[%1$s]" value="%2$s" />',
			$fn, urlencode( json_encode( NelioFPSettings::get_list_of_feat_post_ids() ) )
		); ?>

		<script type="text/javascript">
		var xxx;
		(function($) {
			$( document ).ready( function() {
				$( '#neliofp-list-of-feat-posts' ).sortable( { handle : '.handler', cursor: 'move' } );
			});
			var nofp = '<span class="no-nelio-fp">' + <?php
				echo wp_json_encode( __( 'None.<br><br><em>Add your first featured post using the selector above.</em>', 'nelio-featured-posts' ) );
			?> + '</span>';
			function addFeatPost( id, position ) {
				$( '.no-nelio-fp' ).remove();
				var node = '<div class="result-content"><span class="spinner is-active"></span></div>';
				node = $(node);
				if ( 'first' == position )
					$("#neliofp-list-of-feat-posts").prepend(node);
				else
					$("#neliofp-list-of-feat-posts").append(node);
				jQuery.ajax( {
					type:     'POST',
					async:    true,
					url:      ajaxurl,
					dataType: "json",
					data: {
						action: 'neliofp_get_post_by_id',
						id:     id,
					},
					success: function( data ) {
						if ( data.id <= 0 ) {
							return;
						}
						var content = '<i class="handler dashicons-before dashicons-menu"><br></i><div class="result-image">';
						content += data.thumbnail;
						content += '</div><div class="result-item">';
						content += '<div class="result-title"><span class="select2-match">';
						content += '<a href="' + data.link + '" target="_blank">';
						content += data.title;
						content += '</a>';
						content += '</div><div class="row-actions"><span class="delete" data-post_id="';
						content += data.id;
						content += '"><a href="#">' + <?php echo wp_json_encode( __( 'Delete', 'nelio-featured-posts' ) ); ?> + '</a>';
						content += '</span>';
						node.html( content );
						node.find( '.delete' ).click(function() {
							node.remove();
							if ( $( '#neliofp-list-of-feat-posts .result-item' ).length === 0 ) {
								$( '#neliofp-list-of-feat-posts' ).html( nofp );
							}
						});
					},
				});
			}

			var aux = JSON.parse( decodeURIComponent(
				$("#list_of_feat_post_ids").attr( 'value' )
			) );
			for ( var i = 0; i < aux.length; ++i ) {
				addFeatPost(aux[i]);
			}

			if ( aux.length === 0 ) {
				$("#neliofp-list-of-feat-posts").html( nofp );
			}

			$("#neliofp-add-first").click( function() {
				var id = jQuery("#neliofp-searcher").attr('value');
				if ( id > 0 ) {
					try {
						addFeatPost(parseInt(id), 'first');
					} catch (e) {}
				}
			});

			$("#neliofp-add-last").click( function() {
				var id = jQuery("#neliofp-searcher").attr('value');
				if ( id > 0 ) {
					try {
						addFeatPost(parseInt(id), 'last');
					} catch (e) {}
				}
			});

			$(document).ready(function() {
				$("input[type=submit]").click(function() {
					NelioFPList = [];
					$("#neliofp-list-of-feat-posts .delete").each(function() {
						NelioFPList.push( parseInt( $(this).data('post_id') ) );
					});
					$("#list_of_feat_post_ids").attr( 'value',
						encodeURIComponent( JSON.stringify( NelioFPList ) )
						.replace("'","%27") );
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	public function print_section_info() {
	}

	public function use_feat_image_callback() {
		$fn = 'use_feat_image'; ?>
		<input type="checkbox" id="<?php echo $fn; ?>" name="neliofp_settings[<?php echo $fn; ?>]"
			<?php checked( NelioFPSettings::use_feat_image_if_available() ); ?> /><?php
	}

}

if ( is_admin() ) {
	$my_settings_page = new NelioFPSettingsPage();
}

