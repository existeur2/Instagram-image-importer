<?php
	function iii_create_post_type() {
		register_post_type( 'iii',
			array(
				'labels' => array(
					'name' => __( 'iii' ),
					'singular_name' => __( 'Instagram images' )
				),
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'iii'),
				)
			);
	}


	function iii_post_type() {
 
		$supports = array(
			'title', // post title
			'editor', // post content
			'author', // post author
			'thumbnail', // featured images
			'excerpt', // post excerpt
			'custom-fields', // custom fields
			'post-formats', // post formats
		);
		 
		$labels = array(
			'name' => _x('iii', 'plural'),
			'singular_name' => _x('iii', 'singular'),
			'menu_name' => _x('iii', 'admin menu'),
			'name_admin_bar' => _x('iii', 'admin bar'),
			'add_new' => _x('Add New', 'add new'),
			'add_new_item' => __('Add iii'),
			'new_item' => __('New iii'),
			'edit_item' => __('Edit iii'),
			'view_item' => __('View iii'),
			'all_items' => __('All iii'),
			'search_items' => __('Search iii'),
			'not_found' => __('No iii found.'),
		);
		 
		$args = array(
			'supports' => $supports,
			'labels' => $labels,
			'public' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'iii'),
			'has_archive' => true,
			'hierarchical' => false,
		);
		register_post_type('iii', $args);
	}
?>