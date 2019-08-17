<?php

global $post;

$open_link = sprintf(
	'<a class="featured_post_link" href="%s" title="%s">',
	get_permalink( $post->ID ),
	esc_attr( apply_filters( 'the_title', $post->post_title, $post->ID ) )
);
$close_link = '</a>';
$fi = NelioFPSettings::use_feat_image_if_available();
?>

<article class="post-<?php echo $post->ID; ?> post type-post status-publish entry<?php if ( $fi ) echo ' includes-feat-image'; ?>" itemscope="itemscope"><?php
	if ( $fi ) { ?>
	<div class="featured-image alignleft">
		<?php
		echo $open_link;
		echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
		echo $close_link;
		?>
	</div>
	<?php
	} ?>
	<div class="entry-title"><?php
		echo $open_link;
		echo apply_filters( 'the_title', $post->post_title, $post->ID );
		echo $close_link;
	?></div>
</article>

