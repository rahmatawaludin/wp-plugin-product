<?php
/*
Plugin Name: Product Post Type
Plugin URI: http://www.pajajaransolutions.com
Description: Plugin to show posting with type product developed for Petrakomindo
Version: 1.0
Author: Rahmat Awaludin
Author URI: http://rahmatawaludin.wordpress.com/
*/

/**
 * Copyright (c) 2012 Rahmat Awaludin. All rights reserved.
 * To access this plugin :
 * 1. Copy to plugin folder
 * 2. Activate Plugin
 * 3. Create new product
 * 4. Create page, type shortcode [prd]
 */

add_action( 'init', 'create_product_post_types' );

/**
 * Create hook at init to create new post type (product).
 */
function create_product_post_types() {
	register_post_type( 'product', 
		array(
			'labels' => array(
				'name' => __( 'Products' ),
				'singular_name' => __( 'Product' ),
				'add_new' => __('Add New'),
				'add_new_item' => __( 'Add New Product' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( ' Edit Product' ),
				'new_item' => __( 'New Product'),
				'view' => __( 'View Product' ),
				'view_item' => __( 'View Product' ),
				'search_item' => __( 'Search Product' ),
				'not_found' => __( 'No product found' ),
				'not_found_in_trash' => __( 'No product found in Trash' ),
				'parent' => __( 'Parent Product' )
			),
			'description' => __( 'Product posted here will be published in product page.'),
			'public' => true,

			/* Menu icon */
			'menu_icon' => plugins_url( 'images/pil_icon.png', __FILE__ ),

			/* to make query_posts() */
			'query_var' => true,

			/* dialog which will show up when edit product */
			'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attibutes'),

			/* will make url be www.domain.com/products/nama-product */
			'rewrite' => array ( 'slug'=>'product', 'with_front'=>false),

			/* Show category dialog. TODO: change it into product category taxonomy */
			'taxonomies' => array ('category')
		)
	);
}

add_shortcode( 'prd', 'product_display' );

/**
 * callback for shortcode [prd]
 */
function product_display() {
	wp_enqueue_script( 'select2', plugins_url( 'js/select2/select2.min.js', __FILE__ ), array('jquery'), '3.2', false );
	
	// Embed javascript to make ajax request
	wp_enqueue_script( 'ajax_get_category', plugins_url( 'js/ajax_get_category.js',__FILE__ ), array('jquery'), '1.0', false );

	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script( 'ajax_get_category', 'categoryAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	/* Create drop down */
	$criteria = array(
		'parent' => 0, // only direct child from category 0 (top parent)
		'hide_empty' => 0, // show category even when there is no post
		// 'hierarchical' => true, // Tree
		'exclude' => 1, //  exclude category with id 0 (uncategorized)
		'echo' => 0, // throw output to variable
		'show_option_none' => __('None'),
		'name' => 'selectcat1' // select form name
	);
	echo wp_dropdown_categories($criteria);

	echo "<div id='product_container'></div>";

}

/**
 * Easier debug for print_r() function
 * @param  array $arr array of variable
 */
function print_r_html ($arr) {
        ?><pre><?php
        print_r($arr);
        ?></pre><?php
}

/* create ajax call product_get_category */
add_action('wp_ajax_product_get_category', 'product_get_category'); // ajax access from authenticated user
add_action('wp_ajax_nopriv_product_get_category', 'product_get_category'); // ajax access from unauthenticated user

function product_get_category() {
	global $wpdb; // this is how you get access to the database
	$cat_id = intval($_POST['cat_id']);
	$form_name = 'selectcat'.$cat_id;
	
	if (category_has_children($cat_id)) {
		$cat_criteria = array(
			'parent' => $cat_id, 
			'hide_empty' => 0, 
			// 'hierarchical' => true,
			'exclude' => 1, 
			'echo' => 0, 
			'show_option_none' => __('None'),
			'name' => $form_name 
		);
		echo wp_dropdown_categories($cat_criteria); // sent select tag based on category id
	} else {
		$post_criteria = array(
			'cat' => $cat_id,
			'post_type' => 'product'
		);

		$posts = query_posts( $post_criteria );
		if ($posts) {
			echo '<div id="content_product">';
			foreach ($posts as $post) {
				echo "<a href='#product_container' class='link_product' value='$post->ID'>$post->post_title</a> <br>";
			}
			echo '</div>';
		}
		
	}

	die(); // this is required to return a proper result
}

/**
 * Check whether category has children
 * @param  int $cat_id category id
 * @return boolean  children status
 */
function category_has_children($cat_id) {
	global $wpdb;
	$category_children_check = $wpdb->get_results(" SELECT * FROM wp_term_taxonomy WHERE parent = '$cat_id' ");
	     if ($category_children_check) {
	          return true;
	     } else {
	          return false;
	     }
}

/* create ajax call product_get_content */
add_action('wp_ajax_product_get_content', 'product_get_content');
add_action('wp_ajax_nopriv_product_get_content', 'product_get_content');

/**
 * Callback for ajax call to product_get_content
 */
function product_get_content() {
	global $wpdb; // this is how you get access to the database
	$product_id = intval($_POST['product_id']);
	
	$post_criteria = array (
		'post_type' => 'product',
		'p' => $product_id // only this product_id
	);

	$posts = query_posts($post_criteria);

	echo get_the_post_thumbnail($posts[0]->ID, 'thumbnail'); // get thumbnail
	echo '<br/>';
	echo $posts[0]->post_content;
}
?>