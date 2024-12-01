<?php
/*
This file generates the header area with title and background images
*/

function subpage_header() {

	if(!is_front_page()) {

		$cbi = custom_background_image();

		printf( '<section class="subpage-header" style="background-image:url(' . $cbi . ');">' );

			printf( '<div class="wrap"><article>' );
		
				breadcrumb_hook();
		
				if(is_single()) {
					printf( '<header class="entry-header"><h1 class="entry-title" itemprop="headline">'. get_the_title() . '</h1></header>' );
				}
				elseif( is_search() ) {
					$title = sprintf( '<header class="entry-header"><h1 class="entry-title">%s "%s"</h1></header>', apply_filters( 'genesis_search_title_text', __( 'Search for:', 'genesis' ) ), get_search_query() );
					echo apply_filters( 'genesis_search_title_output', $title ) . "\n";
				}
				elseif( ( is_category() || is_tag() || is_tax() ) ) {
					$cat_title = single_cat_title('', false);
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">' . $cat_title . '</h1></header>';
				}
				elseif( is_home() ) {
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">News</h1></header>';
				}
				elseif( is_post_type_archive('ttshowcase') ) {
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">Testimonials</h1></header>';
				}
				elseif( is_archive() ) {
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">' . get_the_archive_title() . '</h1></header>';
				}
				elseif( is_404() ) {
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">Error 404: Not Found</h1></header>';
				}
				else {
					do_action( 'genesis_entry_header' );
				}
				

			printf( '</article></div>');

		printf( '</section>');
		
	}

}
add_action( 'genesis_before_loop', 'subpage_header' );