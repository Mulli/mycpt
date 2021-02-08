<?php

// mycpy/Mbo shortcodes
// [show-meta type="digma_referrals" pid="4237"]
add_shortcode("show-meta", 'mbo_show_meta'); // show all meta data for cpt

function mbo_show_meta($atts ) {
	$a = shortcode_atts( array(
		'type' => 'digma_referrals',
		'pid'  => 4237 // known existing referral
	), $atts );
	
	if ( isset( $a['type'] ) && isset( $a['pid'] ) ) {
		$meta_type = $a['type'];
		$pid       = $a['pid'];
	} else {
		return "usage: [show-meta type=\"digma_referrals\" pid=\"4237\"]";
	}
	//$meta_values = get_metadata($meta_type, $pid); // defaults all , $meta_key, $single)
	$meta_values = get_post_meta( $pid );
	// array expected...
	if ( ! empty( $meta_values ) ) {
		if ( count( $meta_values ) > 0 ) {
			print_r( $meta_values );
		} else return "show meta values count = 0"; // $meta_values;
	}

	return "Got empty result";
}
