<?php
	function enable_errors()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

	function verify_settings()
	{
		return is_plugin_active( 'featured-image-admin-thumb-fiat/featured-image-admin-thumb.php' );
	}

	function iii_admin_requirement_error()
	{
		echo "This plugin requires Advanced Custom fields Pro and Featured Image Admin Thumb to be installed and active";
	}

	function my_custom_fonts() 
	{
		echo "<style>.attachment-60x60{height:200px;width:auto}</style>";
	}

	function iii_dashboard_header($images)
	{
		?>
			<h1>Instagram Image Importer</h1>
		<?php
		if(iii_new_images($images)){
			?>
				<h3>Import new <?php $i=iii_new_images_number($images);echo $i; ?> instagram posts:</h3>
				<form method='post' action=''>
					<button type="submit" name="importer" id='iii_importer' value="import">Import</button>
				</form>
			<?php
		}
		else{
			?>
				<span id="console">No instagram images to import.</span>
			<?php
		}
		if(isset($_POST['importer']) && $_POST['importer']=="import")
		{
			?>
				<span id="console"><?= iii_import($images)." instagram images imported to posts." ?></span>
			<?php
		}
	}

	function iii_new_images($images)
	{
		foreach ($images['data'] as $index => $image) {
			if(!iii_post_exists($image["node"]["id"]))
			{
				return true;
			}
		}
		return false;
	}

	function iii_new_images_number($images)
	{
		$i=0;
		foreach ($images['data'] as $index => $image) {
	
			if(!iii_post_exists($image["node"]["id"]))
			{
				$i++;
			}
		}
		return $i;
	}

	function iii_drafted_posts()
    {
        $i=0;
        $args = array(
	        "post_status"=>"draft",
            "post_type"=>"iii"
        );

        $query=new WP_Query($args);
	        while($query->have_posts())
            {
                $i++;
	            $query->the_post();
            }
        return $i;
    }

	function iii_import($images)
	{
		$i=0;
		foreach ($images['data'] as $index => $image) {
			if(!iii_post_exists($image["node"]["id"]))
			{
				$i++;
				global $user_ID;
				$new_post = array(
				  'post_title' => $image["node"]['id'],
				  'post_content' => '',
				  'post_status' => 'draft',
				  'post_author' => $user_ID,
				  'post_type' => 'iii'
				);
				$post_id = wp_insert_post($new_post);
				$attachment_id=upload_image_from_url($image["node"]['display_url'],$image["node"]['id']);
				set_post_thumbnail($post_id, $attachment_id);
				update_field("iii_id",$image["node"]['id'],$post_id);

				update_field( "iii_location", iii_get_insta_post_location("https://www.instagram.com/p/".$image["node"]['shortcode']), $post_id );

				update_field("iii_created_time",date("Y-m-d",$image["node"]['taken_at_timestamp']),$post_id);

				update_field("iii_link","https://www.instagram.com/p/".$image["node"]['shortcode'],$post_id);

				update_field( "iii_image_link_low_res", $image["node"]['thumbnail_src'], $post_id );
                update_field( "iii_image_link_standard_res", $image["node"]['display_url'], $post_id );

				$value = $image["node"]['owner']['id'];
				update_field( "iii_user_id", $value, $post_id );
			}
		}
		return $i;
	}

	function iii_get_insta_post_location($post)
    {
        ini_set('default_socket_timeout', 900);
        $content=file_get_contents($post);
        $data = mb_convert_encoding(preg_replace('/\s+/', ' ', $content), "UTF-8");
        $finalResult = "";

        $searchStart = '"has_public_page":true,"name":"';
        $searchStop = '","slug"';
        if(strpos($data,$searchStart))
        {
            $searchStartIndex = strpos($data,$searchStart)+strlen($searchStart);
            $searchStopIndex = strpos($data,$searchStop,$searchStartIndex);
            $searchResult = substr($data,$searchStartIndex,$searchStopIndex-$searchStartIndex);
            $finalResult .= $searchResult ." ,";
        }

        $searchStart = '\"city_name\": \"';
        $searchStop = '\"';
        if(strpos($data,$searchStart))
        {
            $searchStartIndex = strpos($data,$searchStart)+strlen($searchStart);
            $searchStopIndex = strpos($data,$searchStop,$searchStartIndex);
            $searchResult = substr($data,$searchStartIndex,$searchStopIndex-$searchStartIndex);
            $finalResult .= $searchResult ." ";
        }
        return $finalResult;
    }

	function upload_image_from_url( $imageurl , $ipost_id)
	{
	    require_once( ABSPATH . 'wp-admin/includes/image.php' );
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	    require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $file = $imageurl;
        $filename = $ipost_id.".jpg";
        $upload_file = wp_upload_bits($filename, null, file_get_contents($file));
        if (!$upload_file['error']) {
            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], null );
            if (!is_wp_error($attachment_id)) {
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            }
        }

        return $attachment_id;

/*
	    // Get the file extension for the image
	    $fileextension = image_type_to_extension( exif_imagetype( $imageurl ) );

	    // Save as a temporary file
	    $tmp = download_url( $imageurl );

	    // Check for download errors
	    if ( is_wp_error( $tmp ) ) 
	    {
	        @unlink( $file_array[ 'tmp_name' ] );
	        return $tmp;
	    }

	    // Image base name:
	    $name = basename( $imageurl );

	    // Take care of image files without extension:
	    $path = pathinfo( $tmp );
	    if( ! isset( $path['extension'] ) ):
	        $tmpnew = $tmp . '.tmp';
	        if( ! rename( $tmp, $tmpnew ) ):
	            return '';
	        else:
	            $ext  = pathinfo( $imageurl, PATHINFO_EXTENSION );
	            $name = pathinfo( $imageurl, PATHINFO_FILENAME )  . $fileextension;
	            $tmp = $tmpnew;
	        endif;
	    endif;

	    // Upload the image into the WordPress Media Library:
	    $file_array = array(
	        'name'     => $name,
	        'tmp_name' => $tmp
	    );
	    $id = media_handle_sideload( $file_array, 0 );

	    // Check for handle sideload errors:
	    if ( is_wp_error( $id ) )
	    {
	        @unlink( $file_array['tmp_name'] );
	        return $id;
	    }

	    // Get the attachment url:
	    $attachment_url = wp_get_attachment_url( $id );

	    return $id;
*/
	}  

	function iii_import_posts()
	{
		$images=iii_get_new_images();
		if(iii_new_images($images)){
			$i=iii_import($images);
		}
	}

	function iii_get_new_images()
	{
		//$images=file_get_contents( plugin_dir_path( __FILE__ ) ."/images.json");
        $src = file_get_contents("https://www.instagram.com/explore/tags/puppy/?__a=1");
        $images = array();
        $srct = (array)json_decode( $src , true );
        $images['data'] = $srct['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
   		return $images;
	}

	function iii_display_notice()
	{
		add_action('admin_notices','iii_notice_core');
	}

	function iii_notice_core()
	{
		?>
			<div class="notice notice-success is-dismissible">
				<p>New Instagram images have been imported to your draft.</p>
			</div>
		<?php
	}

	function iii_post_exists($title)
	{
		$args = array("s"=>$title."","post_status"=>"draft");
		$args2 = array("s"=>$title."","post_status"=>"publish");
		
		$query = new WP_Query( $args );
		$query2 = new WP_Query( $args2 );

		return $query->have_posts() || $query2->have_posts();
	}

?>