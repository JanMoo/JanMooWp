<?php

if ( !function_exists( 'neliofp_search_posts' ) ) {
	/**
	 * This function is an AJAX callback. It returns a list of up to 20 posts (or
	 * pages). It is used by the select2 widget (an item selector that looks more
	 * beautiful than regular the "select>option" combo.
	 *
	 * Accepted POST params are:
	 *   term: {string}
	 *         the (part of the) string used to look for items.
	 *   type: {'post'|'page'|'post-or-page'}
	 *         what type of element are we looking.
	 */
	function neliofp_search_posts() {
		$term = false;
		if ( isset( $_POST['term'] ) )
			$term = $_POST['term'];

		$type = false;
		if ( isset( $_POST['type'] ) )
			$type = $_POST['type'];

		if ( 'page-or-post' == $type )
			$type = array( 'page', 'post' );

		$default_thumbnail = sprintf(
			'<img src="data:image/gif;%s" class="%s" alt="%s" />',
			'base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			'attachment-thumbnail wp-post-image neliofp-no-thumbnail',
			__( 'No featured image available', 'nelio-featured-posts' )
		);

		$args = array(
			's'              => $term,
			'post_type'      => $type,
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		);

		$result = array();
		$my_query = new WP_Query( $args );
		if ( $my_query->have_posts() ) {
			global $post;

			while ( $my_query->have_posts() ) {
				$my_query->the_post();
				$thumbnail = get_the_post_thumbnail( $post->ID, 'thumbnail' );
				if ( $thumbnail === '' )
					$thumbnail = $default_thumbnail;
				$item = array(
					'id'        => $post->ID,
					'type'      => $post->post_type,
					'title'     => $post->post_title,
					'status'    => $post->post_status,
					'date'      => $post->post_date,
					'author'    => get_the_author(),
					'thumbnail' => $thumbnail,
				);
				array_push( $result, $item );
			}
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die();
	}
}

if ( !function_exists( 'neliofp_get_post_by_id' ) ) {
	function neliofp_get_post_by_id() {

		$default_thumbnail = sprintf(
			'<img src="data:image/gif;%s" class="%s" alt="%s" />',
			'base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			'attachment-thumbnail wp-post-image neliofp-no-thumbnail',
			__( 'No featured image available', 'nelio-featured-posts' )
		);

		header( 'Content-Type: application/json' );
		$result = array(
			'id'        => 0,
			'link'      => '',
			'thumbnail' => $default_thumbnail,
			'title'     => '',
		);

		$id = false;
		if ( $_POST['id'] )
			$id = $_POST['id'];

		$post = get_post( $id );
		if ( !$post ) {
			echo json_encode( $result );
			die();
		}

		$result['id']    = $id;
		$result['title'] = $post->post_title;
		$result['link']  = get_the_permalink( $id );
		$thumbnail = get_the_post_thumbnail( $id, 'thumbnail' );
		if ( '' !== $thumbnail )
			$result['thumbnail'] = $thumbnail;

		echo json_encode( $result );
		die();
	}
}

