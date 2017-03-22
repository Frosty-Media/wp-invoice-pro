<?php $settings = get_option( 'wp_invoice_settings' ); ?>

<div class="project-email">
    
    <div class="wrapper body">
        
        <p class="view-email">
        <a href="<?php echo add_query_arg( 'email', 'view', get_permalink( $post->ID ) ); ?>" class="button" id="<?php $mb->the_name('view_email'); ?>" target="wp-preview"><?php _e( 'View Email', 'wp-invoice' ); ?></a>
        </p>
        
        <p class="send-email">
        <a href="<?php echo add_query_arg( 'email', 'send', get_permalink( $post->ID ) ); ?>" class="button disabled" id="<?php $mb->the_name('send_email'); ?>"><?php _e( 'Send Email', 'wp-invoice' ); ?></a>
        </p>
        
        <?php
        $user = wp_invoice_get_user( $post->ID );
		$user = ( $user->data->display_name != wp_get_current_user()->data->display_name ) ? $user : 'self';
		$client_only = ( $settings['send_invoice'] == 'client' ) ? __( ' only', 'wp-invoice' ) : __( ' and yourself', 'wp-invoice' );
		
		//print '<pre>'; print_r( $post->post_type ); print '</pre>';
		
		if ( !empty( $user ) && 'self' != $user ) {
			
			$client = sprintf( __( '<a href="%s">%s</a>', 'wp-invoice' ),
				esc_url( add_query_arg( array( 'user_id' => $user->data->ID, 'wp_http_refer' => urlencode(stripslashes($_SERVER['REQUEST_URI'])) ), 'user-edit.php' ) ),
				esc_attr( $user->data->display_name )
			);
			echo '<p class="description">' . __( 'Email will be sent to ', 'wp-invoice' ) . $client . $client_only . '</p>';
		} elseif( 'self' == $user ) {
			echo '<div class="haserror"><p class="description">' . __( 'You have selected your profile.', 'wp-invoice' ) . '</p></div>'; 
		} else {
			echo '<div class="haserror"><p class="description">' . __( 'No client selected', 'wp-invoice' ) . '</p></div>'; 
		} ?>
        
        <p class="hidden">
        <?php $mb->the_field('permalink'); ?>
        <input type="hidden" id="<?php $mb->the_name(); ?>" name="<?php $mb->the_name(); ?>" value="<?php echo $settings['permalink']; ?>" />
        
        <?php $mb->the_field('client'); ?>
        <input type="hidden" id="<?php $mb->the_name(); ?>" name="<?php $mb->the_name(); ?>" value="<?php if ( !empty( $user ) ) echo true; else echo false; ?>" />
        </p>
        
        <?php if ( isset( $settings['require_login'] ) && $settings['require_login'] ) : ?>
            <p>
            <?php $mb->the_field('disable_login'); ?>
            <label><?php _e( 'Disable Required Login:', 'wp-invoice' ); ?></label><br>
            <input type="checkbox" name="<?php $mb->the_name(); ?>" value="true"<?php echo ( true == $mb->get_the_value() ) ? ' checked="checked"' : ''; ?> />
            <span class="description"><?php _e( 'Override the default setting for this post.', 'wp-invoice' ); ?></span>
            </p>
        <?php else : ?>	
            <p>
            <?php $mb->the_field('require_login'); ?>
            <label><?php _e( 'Require Login:', 'wp-invoice' ); ?></label><br>
            <input type="checkbox" name="<?php $mb->the_name(); ?>" value="true"<?php echo ( true == $mb->get_the_value() ) ? ' checked="checked"' : ''; ?> />
            <span class="description"><?php _e( 'Override the default setting for this post.', 'wp-invoice' ); ?></span>
            </p>	
        <?php endif; ?>
        
    </div>

</div>