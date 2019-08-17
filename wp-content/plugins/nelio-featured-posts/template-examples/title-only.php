<?php

/**
 * You must declare this variable in your templates. It contains the current
 * related post that is about to be displayed.
 */
global $post;

// Open link A tag that points to the post itself
$open_link = sprintf(
		'<a class="related_post_link" href="%s" title="%s">',
		get_permalink( $post->ID ),
		esc_attr( apply_filters( 'the_title', $post->post_title, $post->ID ) )
	);
$close_link = '</a>';

?>

<article class="post-<?php echo $post->ID; ?> post type-post status-publish entry" itemscope="itemscope">
	<h2 class="entry-title"><?php
		echo $open_link;
		echo apply_filters( 'the_title', $post->post_title, $post->ID );
		echo $close_link;
	?></h2>
</article>

