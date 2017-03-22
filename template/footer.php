		<div class="clearfix"></div>
	</section><!-- #page -->
    
    <?php if ( is_singular() && comments_open() ) : ?>
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
			global $withcomments; $withcomments = 1; ?>
    		<section id="comments-wrapper">  
        		<?php comments_template(); ?> 
				
                <a href="<?php echo add_query_arg( 'comment', 'load', get_permalink( $post->ID ) ); ?>" class="btn comment"><?php _e( 'Comment', 'wp-invoice-pro' ); ?></a>
                        
    		</section><!-- #comments-wrapper -->           
        <?php endwhile; endif; ?>
    <?php endif; ?>
   
   	<footer>
		<p>Powered by <a href="http://extendd.com/plugin/wordpress-invoice-pro" target="_blank">WP Invoice</a></p>
	</footer>

<?php wp_footer(); ?>
</body>
</html>