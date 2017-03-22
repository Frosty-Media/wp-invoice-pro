<?php $settings = get_option( 'wp_invoice_settings' ); ?>

<div class="project-user">
    
    <div class="wrapper body">
        
        <?php $users = wp_invoice_get_users();
		
		//$user = wp_invoice_get_user(); print '<pre>'; print_r( $user ); print '</pre>'; unset( $user ); return;
		 
		//print '<pre>'; print_r( $users ); print '</pre>'; //return;
		
		if ( !empty( $users ) ) { ?>
        
            <ul>
            
            <?php foreach ( $users as $user ) : ?>
            
                <?php $user_data = wp_invoice_get_user_meta( null, $user->ID ); ?>
                
                <?php //print '<pre>'; print_r( $user_data ); print '</pre>'; ?>
                
                <?php $user_data['company'] = empty( $user_data['company'] ) ? $user_data['company'] : ' with ' . $user_data['company']; ?>
                
                <?php $mb->the_field('user'); ?>
                
                <?php $style = ( $user->display_name == wp_get_current_user()->data->display_name ) ? ' style="color: #aaa"' : ''; ?>
                
                <?php $you = ( $user->display_name == wp_get_current_user()->data->display_name ) ? ' (you)' : ''; ?>
                
                <li>
                    <input type="radio" name="<?php $mb->the_name(); ?>" value="<?php echo $user->ID; ?>" <?php checked( $user->ID, $mb->get_the_value(), true ); ?> />
                    <label><span<?php echo $style; ?>><?php echo esc_attr( $user->display_name ); ?></span><em><?php echo esc_attr( $user_data['company'] ); ?></em><span<?php echo $style; ?>><?php echo $you; ?></span></label>
                </li>
            
            <?php endforeach; ?>
            
            </ul>
            
		<?php }	?>
        
    </div>

</div>