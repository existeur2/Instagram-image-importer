<?php
	require_once( plugin_dir_path( __FILE__ ) . "iii_base_functions.php");

	function iii_admin()
	{
		if(verify_settings())
		{
			add_menu_page( "Instagram Image Importer dashboard", "III", "manage_options", "iii", "iii_admin_fnc");
		}
		else
		{
			add_menu_page( "Instagram Image Importer dashboard", "III", "manage_options", "iii", "iii_admin_requirement_error" );
		}
	}

	function new_posts_notice(){
		?>
			<div class="updated notice">
			    <p>New Instagram images to be imported. <a href="<?= admin_url().'admin.php?page=iii' ?>">Click here</a>.</p>
			</div>
		<?php
	}

   	function iii_admin_fnc()
   	{
		$images_decoded=iii_get_new_images();
		//Plugin dashboard header
		iii_dashboard_header($images_decoded);
	}

	function iii_email_notice_send()
	{
		$users = get_users( [ 'role__in' => [ 'administrator' ] ] );
		$images=file_get_contents( plugin_dir_path( __FILE__ ) ."/images.json");
   		$images_decoded=(array)json_decode( $images , true );
        $number=iii_drafted_posts();
        if($number>0)
        {
            foreach ($users as $user) {
		        $message = '<h1>Hello '.$user->user_nicename.'</h1>
                        <p>
                            We have new Instagram posts to validate.
                            <a href="'.get_site_url()."/wp-admin/edit.php?post_type=iii".'">Click here</a> to check.
                        </p>
                        <p>Have a good day.</p>';
		        $headers = "MIME-Version: 1.0\r\n";
		        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		        mail($user->user_email,"Instagram image importer",$message,$headers);
	        }
        }
	}

	function iii_plugin_start()
	{
		require_once(plugin_dir_path( __FILE__ ).'WPConfigTransformer.php');
		$config_transformer = new WPConfigTransformer( '../wp-config.php' );
		$config_transformer->remove( 'constant', 'DISABLE_WP_CRON' );
		if(!wp_next_scheduled( 'iii_email_notice_event' ))
		{
			wp_schedule_event( time(), "daily", "iii_email_notice_event" );
		}
		if(!wp_next_scheduled( 'iii_post_import_event' ))
		{
			wp_schedule_event( time(), "hourly", "iii_post_import_event" );
		}
	}

	function iii_plugin_stop(){
		require_once(plugin_dir_path( __FILE__ ).'WPConfigTransformer.php');
		$config_transformer = new WPConfigTransformer( '../wp-config.php' );
		$config_transformer->add( 'constant', 'DISABLE_WP_CRON', 'true');
		$config_transformer->update( 'constant', 'DISABLE_WP_CRON', 'true', array( 'raw' => true, 'normalize' => true ) );
		wp_clear_scheduled_hook('iii_email_notice_event');
		wp_clear_scheduled_hook('iii_post_import_event');
	}
   	
?>