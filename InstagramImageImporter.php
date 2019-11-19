<?php
   /*
   Plugin Name: Instagram Image Importer
   Plugin URI: https://www.cosavostra.com
   description: A plugin that imports instagram images to your media library.
   Version: 1.0
   Author: Fares Jaber #CVExisteur
   */

   require_once(ABSPATH .'/wp-load.php' );
   require_once(ABSPATH .'/wp-includes/pluggable.php' );
   require_once(ABSPATH . 'wp-admin/includes/post.php' );
   require_once("iii_functions.php");
   require_once("iii_custom_post_type.php");

enable_errors();

   //Start cron job for alerting user about new instagram images if detected
   register_activation_hook( __FILE__, "iii_plugin_start" );

   //Stop cron job when plugin is desabled
   register_deactivation_hook( __FILE__, 'iii_plugin_stop' );

   //add menu item to admin panel
   //add_action('admin_menu','iii_admin');

   if(!post_type_exists( "iii" ))
   {
      add_action( 'init', 'iii_create_post_type' );
      add_action('init', 'iii_post_type');
   }
   //plugin admin page content
   add_action('admin_head','my_custom_fonts');  

   add_action( "iii_email_notice_event", "iii_email_notice_send" );

   add_action( "iii_post_import_event", "iii_import_posts" );

?>