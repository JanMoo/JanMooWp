<?php

// Creating the widget
class NelioFP_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'NelioFP_Widget',

			// Widget name will appear in UI
			__('Featured Posts by Nelio', 'nelio-featured-posts'),

			// Widget description
			array( 'description' => __( 'Display a list of your featured posts.', 'nelio-featured-posts' ), )
		);

		if ( !is_admin() ) {
			wp_enqueue_style( 'neliofp_style_css',
				neliofp_asset_link( '/style.min.css' ) );
		}
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		$template = NELIOFP_DIR . '/featured-post-template.php';
		if ( isset( $instance['template'] ) && !empty( $instance['template'] ) ) {
			$aux = get_stylesheet_directory() . '/neliofp/' . $instance['template'] . '.php';
			if ( file_exists( $aux ) )
				$template = $aux;
		}

		// This is where you run the code and display the output
		$fps = NelioFPSettings::get_list_of_feat_posts();
		global $post;
		$ori_post = $post;
		if ( count( $fps ) > 0 ) {
			echo '<nav>';
			foreach ( $fps as $post )
				include( $template );
			echo '</nav>';
		}
		else {
			echo '<p class="neliofp-none">' . esc_html__( 'No featured posts.', 'nelio-featured-posts' ) . '</p>';
		}
		$post = $ori_post;
		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) $title = $instance['title'];
		else $title = __( 'Featured Posts', 'nelio-featured-posts' );
		if ( isset( $instance['template'] ) ) $template = $instance['template'];
		else $template = '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label>
			<input
				class="widefat" type="text"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"><?php esc_html_e( 'Template (without the \'.php\' extension):', 'nelio-featured-posts' ); ?></label>
			<input
				class="widefat" type="text" placeholder="<?php esc_attr_e( 'Default', 'nelio-featured-posts' ); ?>"
				id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>"
				value="<?php echo esc_attr( $template ); ?>" />
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['template'] = ( ! empty( $new_instance['template'] ) ) ? strip_tags( $new_instance['template'] ) : '';
		return $instance;
	}
}

// Register and load the widget
add_action( 'widgets_init', 'neliofp_load_widget' );
function neliofp_load_widget() {
	register_widget( 'NelioFP_Widget' );
}

