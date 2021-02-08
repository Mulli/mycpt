<?php

// [mbo-search entity=<referrals|intakes|family|parents>" field_group="<field group>" field_name=<value| empty> value=<xyz|empty>]

//  https://digma.me/mbo-search/?entity=<referrals|intakes|families|parents>&field_group=<field group>&field_name=<field group>&value=<xyz|empty>
//  https://digma.me/mbo-search/?entity=parents&field_group=parent_info&field_name=parent_social_section&field_value=some-value&new_value=new-value

add_shortcode('mbo-search','mbo_search');
function mbo_search($atts ) {
	$a = shortcode_atts( array(
		'cpt_type' => 'family', // referrals|intakes|family|parents
		'field_group' => '',
		'field_name' => '',
		'field_value' => ''
	), $atts );
	$cpt_type ="";
	$field_group = "";
	$field_name = "";
	$field_value = "";
    $replace_value = false;
	// get input files name
	if (isset($_GET['entity']))
        $cpt_type = get_cpt_type($_GET['entity']);
	if (isset($_GET['field_group']))
		$field_group = $_GET['field_group'];
	if (isset($_GET['field_name']))
        $field_name = $_GET['field_name'];
    if (isset($_GET['field_value']))
		$field_value = $_GET['field_value'];
    if (isset($_GET['new_value'])){
        $new_value = $_GET['new_value'];
        $replace_value = true;
    }

    if (!$cpt_type || !$field_group || !$field_name)
        return "Missing parameters:<br />usage: https://digma.me/mbo-search/?entity=<referrals|intakes|families|parents>&field_group=<field group>&field_name=<field group>&value=<xyz|empty><br />";
    // TODO some header
    // get data from database
    $programCpt = get_posts( array('post_type' => $cpt_type, 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($programCpt) < 1)
        return "No post of type= ". $cpt_type ." found";
    // get once all tables for search
    // and verify other parameters
    $tableList = array();
    $cnt = 0;
    $str = "";
    foreach ( $programCpt as $cpt ){
        $tableList[$cpt->ID] = get_field($field_group, $cpt->ID);
        if (!$tableList[$cpt->ID])
            return "Empty field group " . $field_group . " in post id=". $cpt->ID;
        // search
        error_log(print_r($tableList[$cpt->ID], true));
        if (isset($tableList[$cpt->ID][$field_name])
            && $tableList[$cpt->ID][$field_name] == $field_value){
            $str .= "cpt id=". $cpt->ID . "   " . $cpt->post_title;
            $cnt++;
            if ($replace_value){
                $tableList[$cpt->ID][$field_name] = $new_value;
                update_field($field_group, $tableList[$cpt->ID], $cpt->ID );
                $str .= " REPLACED from" . $field_value .  "to >>" . $new_value . "<<";
            }
            $str .= "<br />";
        }
    }

    // return result
    $hdr = "<h1>תוצאות חיפוש</h1>";
    $hdr .= "<h3>נמצאו " . $cnt . " תוצאות בחיפוש של:<b> ". ($field_value ? $field_value : " שדה ריק ") ."</b></h3>";
    return $hdr . $str;
}

function get_cpt_type($entity){
    $mbo_type = array('referrals', 'intakes','families','parents');
    if (!in_array($entity, $mbo_type))
        return "";
    return "digma_".$entity;
}
