<?php

defined( 'ABSPATH' ) or die();

function er_itf_return_feed_items() {

    require 'oauth/oauth.php';
    require 'itf_config.php';    

    $tmhOAuth = new tmhOAuth(array(

        'consumer_key' => $consumer_key,      
        'consumer_secret' => $consumer_secret,    
        'user_token' => $user_token,      
        'user_secret' => $user_secret     

    ));

    $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
        'screen_name' => $twitter_id,
        'count' => $max_tweets,
        'include_rts' => true,
        'include_entities' => true
    ));

    $data  = $tmhOAuth->response['response'];
    return $feed_items = json_decode( $data, true);
}

function er_itf_get_array_value_utility( $array, $key ) {
    foreach ( $array as $k => $v ) {
        if ( $key == $k ) {
            return $v;
        }
        else {
        	if( is_array( $v ) ) {
            	$v = er_itf_get_array_value_utility( $v, $key );
            	if ( $v != null ) return $v; 
        	}
        }
    }
}

// Get the feed and the array of keys, then create an array with those values
function er_itf_get_item_values( $array, $keys ) {
    $values = array();
    foreach( $keys as $key => $value ){
        $values[$key] = er_itf_get_array_value_utility( $array, $value ) ;
    }
    return $values;
}

function er_itf_title_format( $title ) {

    return $title = preg_replace('/\s+?(\S+)?$/', '', substr( $title, 0, 40 ) );
}

function er_itf_date_format( $datetime ) {
    
    return $datetime = date( 'Y-m-d H:i:s', strtotime( $datetime ) );
}

function er_itf_permalink_format( $permalink ) {

    return $permalink = 'https://twitter.com/evanrose/status/' . $permalink;
}

function er_itf_content_format( $content ) {

    $content = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\./-]*(\?\S+)?)?)?)@', '<a target="blank" title="$1" href="$1">$1</a>', $content);
    #Turn hashtags into links
    $content = preg_replace('/#([0-9a-zA-Z_-]+)/', "<a title='$1' href=\"http://twitter.com/search?q=%23$1\">#$1</a>", $content);
    #Turn @replies into links
    $content = preg_replace("/@([0-9a-zA-Z_-]+)/", "<a title='$1' href=\"http://twitter.com/$1\">@$1</a>", $content);

    return $content;       
}

function er_itf_format_item( $item ) {

    $item['meta_permalink'] = er_itf_permalink_format( $item['meta_permalink'] );
    $item['content'] = er_itf_content_format( $item['content'] );
    $item['datetime'] = er_itf_date_format( $item['datetime'] );
    $item['title'] = er_itf_title_format( $item['title'] );

    return $item;
}