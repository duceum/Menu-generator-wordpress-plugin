<?php

/**
 * The form to be loaded on the plugin's admin page
 */
	if( current_user_can( 'edit_users' ) ) {		
		
		
		// Generate a custom nonce value.
		$nds_add_meta_nonce = wp_create_nonce( 'nds_add_user_meta_form_nonce' ); 
		
		// Build the Form
?>				
		<h2><?php _e( 'Menu Generator', $this->plugin_name ); ?></h2>		
		<div class="nds_add_user_meta_form">
					
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="nds_add_user_meta_ajax_form" >			

			
			<input type="hidden" name="action" value="nds_form_response">
			<input type="hidden" name="nds_add_user_meta_nonce" value="<?php echo $nds_add_meta_nonce ?>" />			
			<div>
				<br>
				<label for="<?php echo $this->plugin_name; ?>-menu_name"> <?php _e('Enter a name for new menu', $this->plugin_name); ?> </label><br>
				<input required id="<?php echo $this->plugin_name; ?>-menu_name" type="text" name="menu_name" value="" placeholder="<?php _e('Enter a name for new menu', $this->plugin_name);?>" /><br>
			</div>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Submit Form"></p>
		</form>
		<br/><br/>
		<div id="nds_form_feedback"></div>
		<br/><br/>			
		</div>
	<?php    
	}
	else {  
	?>
		<p> <?php __("You are not authorized to perform this operation.", $this->plugin_name) ?> </p>
	<?php   
	}
