<?php

class WP_Invoice_Comments {
	
	var $name,
		$dir,
		$plugin_dir,
		$plugin_path;
	
	/**
	 * Invoice Constructor
	 *
	 * @since 1.0.0
	 * 
	 * @param object: wp_invoice_invoice to find parent variables.
	 **/
	function __construct( $parent )
	{
		$this->name			= $parent->name;								// Plugin Name
		$this->dir 			= dirname( plugin_dir_path( __FILE__ ) );	// This directory
		$this->plugin_dir 		= $parent->dir;								// Plugin directory
		$this->plugin_path 	= $parent->path;								// Plugin Absolute Path	
		
		// Filter
		add_filter( 'pre_get_comments', 						array( $this, 'hide_comments_comment_page' ), 19 );		
		
		// Set fields
		add_filter( 'comment_form_default_fields',			array( $this, 'comment_form_default_fields' ) );
		
		// Default template
		add_filter( 'comments_template',						array( $this, 'comments_template' ), 99 ); // Late to avoid conflicts
		
		// AJAX
		add_action( 'wp_ajax_nopriv_wp-invoice-comments', 	array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_wp-invoice-comments', 			array( $this, 'ajax_handler' ) );
		
		add_action( 'comment_post',							array( $this, 'comment_reply' ) );
	}
	
	/**
	 * Hide Invoice comments from the comment page
	 *
	 * @since 2.0.0
	 */
	function hide_comments_comment_page( $query ) 
	{
		global $pagenow;
		
		if ( !empty( $pagenow ) && 'edit-comments.php' == $pagenow || !is_admin() ) :						
			$post_types = get_post_types( array( 'public' => true ), 'names' ); 
			foreach ( $post_types as $post_type ) {
				if ( 'invoice' == $post_type && !is_singular('invoice') )
					unset( $post_types[$post_type] );
			}
			$query->query_vars['post_type'] = $post_types;
		endif;
				
		return $query;
	}

	/**
	 * Remove URL from the comment field
	 *
	 * @since	2.0.0
	 */
	function comment_form_default_fields( $fields ) {
		if ( 'invoice' == get_post_type() || defined( 'DOING_AJAX' ) && DOING_AJAX )
			unset( $fields['url'] );
			
		return $fields;
	}

	/**
	 * Custom template for Invoices
	 *
	 * @since	2.0.0
	 */
	function comments_template( $template ) {
		if ( 'invoice' == get_post_type() )
			$template = trailingslashit( WP_INVOICE_DIR ) . 'template/comments.php';
			
		return $template;
	}

	/**
	 * WP Invoice Comment callback
	 *
	 * @since	2.0.0
	 */
	function wp_invoice_comment_callback( $comment, $args, $depth ) {
		global $post, $comment; ?>
	
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
	
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php
							$avatar_size = 40;
							if ( '0' != $comment->comment_parent )
								$avatar_size = 30;
	
							echo get_avatar( $comment, $avatar_size );
	
							/* translators: 1: comment author, 2: date and time */
							printf( __( '%1$s on %2$s <span class="says">said:</span>', 'wp-invoice-pro' ),
								sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
								sprintf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* translators: 1: date, 2: time */
									sprintf( __( '%1$s at %2$s', 'wp-invoice-pro' ), get_comment_date(), get_comment_time() )
								)
							);
						?>
	
						<?php edit_comment_link( __( 'Edit', 'wp-invoice-pro' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .comment-author .vcard -->
	
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wp-invoice-pro' ); ?></em>
						<br />
					<?php endif; ?>
	
				</footer>
	
				<div class="comment-content"><?php comment_text(); ?></div>
	
				<div class="reply" style="display:none;">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply <span>&darr;</span>', 'wp-invoice-pro' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			</article><!-- #comment -->
	
		<?php /* No closing </li> is needed.  WordPress will know where to add it. */
	}
	
	/**
	 * Load the comment_form()
	 *
	 */
	function ajax_handler() {
		check_ajax_referer( WP_INVOICE_BASENAME, 'nonce' );		
		global $post;
		
		$post_id = empty( $post ) ? $_GET['post_id'] : $post->ID;
		comment_form( '', $post_id );
		exit();
	}
	
	/**
	 * Comment reply notification.
	 *
	 * @ref		http://wordpress.stackexchange.com/questions/85601/notify-comment-author-upon-reply
	 */
	function comment_reply( $comment_reply_id )	{
		$comment	= get_comment( $comment_reply_id );
		$post		= get_post( $comment->comment_post_ID );
		
		/* Bail early if not an invoice */
		if ( $post->post_type != 'invoice' )
			return;
		
		if ( $comment->comment_parent != 0 ) 	{
			
			$old_comment = get_comment( $comment->comment_parent );
			
			if ( $old_comment->user_id == 0 )	{
				
				$email		= $old_comment->comment_author_email;
				$name		= $comment->comment_author;
				$content	= $comment->comment_content;
				$title		= $post->post_title;
				$link		= get_permalink( $comment->comment_post_ID );
				$blogname	= wp_invoice_get_invoice_client_name();
				$subject	= sprintf( '[%1$s] Comment reply on "%2$s"', $blogname, $title );
				
				$notify_message  = sprintf( '%1$s replied to a comment you left on: %2$s', $name, $title ) . "\r\n";
				$notify_message .= 'Comment: ' . "\r\n" . $content . "\r\n\r\n";
				$notify_message .= 'You can reply to the comment here: ' . "\r\n";
				$notify_message .= esc_url( add_query_arg( 'comment', 'load', $link ) ) . "\r\n\r\n";
				
				$from     = wp_invoice_get_invoice_email();
				$headers  = 'From: ' . $from . "\n";
				$headers .= 'Reply-To: ' . $from . "\n";
				$headers .= 'MIME-Version: 1.0' . "\n";
				$headers .= 'Content-type: text/html; charset=' . get_option('blog_charset') . "\n";
				
				wp_mail( $email, $subject, $notify_message, $headers );
				
			}			
		} 		
	}
	
}