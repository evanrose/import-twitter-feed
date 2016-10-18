<?php
/*
Plugin Name: Import Twitter Feed
Plugin URI: http://github.com/evanrose
Description: Import Twitter JSON Feed
Author: Evan Rose
Version: 1.1
Author URI: evan@evanrose.com
*/

defined( 'ABSPATH' ) or die();

register_activation_hook(__FILE__, 'er_itf_activation' );

function er_itf_activation() {
	wp_schedule_event( time(), 'hourly', 'er_itf_hourly_event' );
}

add_action('er_etf_hourly_event', 'er_itf_fetch_posts');

//function is_user_logged_in() {}
//er_itf_fetch_posts_test();
//function er_itf_fetch_posts_test() {
function er_itf_fetch_posts() {

	require 'includes/itf_functions.php';

	$feed_url 			= '';
	$feed_items_path 	= array();
	$meta_key_id 		= '_tweet_id';
	$post_author 		= 1;
	$post_type			= 'tweet';
	$feed_keys 			= [
		
		'meta_id' => 		'id_str',
		'datetime' => 		'created_at',
		'title'	=> 			'text',
		'content' => 		'text',
		'meta_permalink' => 'id_str',
	];

	$feed_items = er_itf_return_feed_items();

	foreach( $feed_items as $feed_item ) {

		$item = er_itf_get_item_values( $feed_item, $feed_keys );
		$item = er_itf_format_item( $item );

		//check to see if meta_id is already in post_meta table
		$args = array(
			
			'meta_query' => array(
		
				array(
					'key'   => $meta_key_id,
					'value' => $item['meta_id'],
				)
			),
			'post_type'		=> $post_type,
		);

		//if there is no post medadata, post_meta will be empty and thus a new post is coming
		$post_meta = get_posts( $args );

		if ( empty( $post_meta ) ) {

			$post = array(

				'post_author'	=> $post_author,
				'post_content'  => $item['content'],
				'post_date_gmt'	=> $item['datetime'],
				'post_status'   => 'publish',
				'post_title'    => $item['title'], 
				'post_type'		=> $post_type,
			);

			//create post and set post id for post_meta values
			$post_id = wp_insert_post( $post );
		
			add_post_meta( $post_id, $meta_key_id, $item['meta_id'] );
			add_post_meta( $post_id, '_meta_permalink', $item['meta_permalink'] );
		}
	}	
}

/*
/* Deactivate chron on plugin deactivation
*/
register_deactivation_hook(__FILE__, 'er_itf_deactivation' );

function er_itf_deactivation() {
	wp_clear_scheduled_hook( 'er_itf_hourly_event' );
}