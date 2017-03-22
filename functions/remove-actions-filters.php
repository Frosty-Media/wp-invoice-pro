<?php

	 
/**
 * Remove SEO Link from the_content if 'SEO Smart Links'
 * is installed.
 *
 * @since 2.1.2
 */
function wp_invoice_remove_SEOLinks_the_content_filter() {

	$post_type = get_query_var( 'post_type' );
	
	if ( $post_type === 'invoice' ) {
		if ( class_exists( 'SEOLinks' ) ) {
			global $SEOLinks;
			if ( isset( $SEOLinks ) ) {
				remove_filter( 'the_content',  array( $SEOLinks, 'SEOLinks_the_content_filter' ), 10 );
			}
			else {
				remove_filter( 'the_content',  array( 'SEOLinks', 'SEOLinks_the_content_filter' ), 10 );
			}
		}
	}

}
add_action( 'wp_head', 'wp_invoice_remove_SEOLinks_the_content_filter', 99 );