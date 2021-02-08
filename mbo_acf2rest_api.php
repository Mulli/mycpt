<?php
// TODO - replace acf to rest plugin with this filter
// OR use mycpt *complete* interface - no need to expose via regular wordpress api

// add this to functions.php
//register acf fields to Wordpress API
//https://support.advancedcustomfields.com/forums/topic/json-rest-api-and-acf/

function acf_to_rest_api($response, $post, $request) {
    if (!function_exists('get_fields')) 
        return $response;

    if (isset($post)) {
        $acf = get_fields($post->id);
        $response->data['acf'] = $acf; // $acf is false if get_fields() fails
    }
    return $response;
}
// add_filter('rest_prepare_post', 'acf_to_rest_api', 10, 3); // for posts
// add_filter('rest_prepare_cpt', 'acf_to_rest_api', 10, 3); // for custom post types