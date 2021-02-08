<?php

//  https://digma.me/parentqsc/?family=2019&parent=1984&gender=m
//       start  end
// questions:
add_shortcode('parentqsc','digma_parentq');
function digma_parentq($atts ) {
	$a = shortcode_atts( array(
		'family_cptID' => '',
		'parent_cptID' => '',
		'gender' => '',
		'entity_type' => 'digma_parents',
		'num_cols' => 'unused-now-TBD',
		'print_csv' => 0
	), $atts );
	// get input files name
	if (!isset($_GET['family']) || !isset($_GET['parent'])|| !isset($_GET['gender']))
		return "file name missing,<br />usage: parentq?family=<id>&parent=<id>&gender=<m/w><br />";
	
	$family = $_GET['family'];
	$parent = $_GET['parent'];
	$gender = $_GET['gender'];

// locate parent cpt in $parent
	$form_data = array();
        get_input_form($family, $parent, $gender, $form_data);
}
function get_input_form($family, $parent, $gender, $form_data){
	$args = array(
	    'post_type'  => 'elementor_lead',
	    'posts_per_page' => -1,
	);
	$postslist = get_posts( $args );
	
	$str = "";
	
	foreach ( $postslist as $p ) {
	   $str .= '<p>' . $p->post_title . '</p>';
	   // $key_value = get_post_meta( $p->ID, 'meta_input', true );
	   $ld = get_post_meta($p->ID,'lead_data',true);
	   $ld_json = json_decode($ld,true);
	   foreach ($ld_json as $k => $v){
	   	$str .= $v['title'] . '=' . $v['value'] . "<br />";
	   }
//	   error_log(print_r( $ld_json , true));
	// map form fields to field-group subfields
	// Move to mycpt plugin & attach to NEW import button
	}
	return $str;
}

?>