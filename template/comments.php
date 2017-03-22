<h3 id="comments-number"><?php comments_number( __( 'No Responses', 'wp-invoice-pro' ), __( 'One Response', 'wp-invoice-pro' ), __( '% Responses', 'wp-invoice-pro' ) ); ?></h3>

<ul class="commentlist">        
    <?php wp_list_comments( array(
        'style' 			=> 'ul',
        'avatar_size' 	=> '40',
        'callback' 		=> array( WP_INVOICE_PRO()->comments, 'wp_invoice_comment_callback' ),
        'type' 			=> 'comment',
        'format'			=> 'html5',
    ) ); ?>
</ul>

<noscript>JavaScript is required to load the comments.</noscript>
<div id="comment-form-wrapper"></div>
<div class="clearfix"></div>