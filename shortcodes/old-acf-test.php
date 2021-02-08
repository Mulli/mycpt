<?php
/* acf tests */
include_once ABSPATH .'wp-content/plugins/mycpt/' . "field-groups/family-referral-group.php";

add_shortcode('acf-test', 'digma_acf_test');

function digma_acf_test(){
//get_fields([$post_id], [$format_value]);

	$fields = get_fields(139);
//var_dump($fields);
	if( $fields ){
	    $str = "<ul>";
		foreach( $fields as $name => $value ){
				$str .= "<li><b>". $name ."</b> ". $value ."</li>";
				if(is_array ($value)){
					
					$str .= "<pre>". var_export($value, true). "</pre>";
					/*foreach( $value as $n => $v ){
						$str .= "<li><b>". $n ."</b> ". $v ."</li>";
					}
					$str .= "</pre>";
					*/
				}

		}
	    $str .= "</ul>";
	    return $str;
	}
	return "no value";
}

function dump_post(){
		?>
	echo '<pre>';
		print_r( get_fields(139) );
	echo '</pre>';
<?php
}

add_shortcode('get-field-objects', 'digma_get_field_objects');
function digma_get_field_objects($atts ) {
	$a = shortcode_atts( array(
		'post_id' => 139,
	), $atts );
	/*
	*  get all custom fields and dump for testing
	*/
// $fields = get_field_objects($post_id, [$format_value], [$load_value]);

	$fields = get_field_objects($a['post_id']);
	var_dump( $fields ); 

	/*
	*  get all custom fields, loop through them and create a label => value markup
	*/

	$fields = get_field_objects($a['post_id']);

	if( !$fields ) return; // empty??

	foreach( $fields as $field_name => $field )	{
		echo '<div>';
			echo '<h3>' . $field['label'] . '</h3>';
			echo 'Key: <strong>'. $field['key'] . '</strong><br />';
			echo $field['value'];
		echo '</div>';
	}
	
}

add_shortcode('get-field-object', 'digma_get_field_object');
function digma_get_field_object(){
	/*
	*  Get a field object and display it with it's value
	*/

	$field_name = "text_field";
	$field = get_field_object($field_name);

	echo $field['label'] . ': ' . $field['value'];

	/*
	*  Get a field object and display it with it's value (using the field key and the value fron another post)
	*/

	$field_key = "field_5039a99716d1d";
	$post_id = 123;
	$field = get_field_object($field_key, $post_id);

	echo $field['label'] . ': ' . $field['value'];

	/*
	*  Get a field object and create a select form element
	*/

	$field_key = "field_5039a99716d1d";
	$field = get_field_object($field_key);

	if( $field )	{
		echo '<select name="' . $field['key'] . '">';
			foreach( $field['choices'] as $k => $v )			{
				echo '<option value="' . $k . '">' . $v . '</option>';
			}
		echo '</select>';
	}

}
/*
add_shortcode('acf-update', 'digma_acf_update');
function digma_acf_update(){
	// by field name for each type
	$post_id = 139;
	$name = "123456";
	$res = update_field('family_name', $name , $post_id);
	// add row to repeater
	// display result
	return "update fields ". $name . " returned ". $res;
	digma_acf_test();
}
*/
// Add new family, parent, kid, member
add_shortcode('acf-new-entity', 'digma_new_entity');
function digma_new_entity($atts ) {
	$a = shortcode_atts( array(
		'family_name' => 'family name',
		'entity_type' => 'digma_families',
	), $atts );

	// vars
	$my_post = array(
		'post_title'	=> $a['family_name'].date("Y-m-d-h:i:sa"),
		'post_type'		=> $a['entity_type'],
		'post_status'	=> 'publish',
		'comment-status'  => 'closed',
      	'ping-status'     => 'closed',
      	//'post_author'   => 1,
	  	//'post_category' => array( 8,39 )
	);

	// insert the post into the database
	$post_id = wp_insert_post( $my_post );


	// save a basic text value
	$field_key = "family_referral_initiator";
	$value = "some new string";
	update_field( $field_key, $value, $post_id );


	// save a checkbox or select value
	$field_key = "field_1234567";
	$value = array("red", "blue", "yellow");
	update_field( $field_key, $value, $post_id );


	// save a repeater field value
	$field_key = "field_12345678";
	$value = array(
		array(
			"sub_field_1"	=> "Foo",
			"sub_field_2"	=> "Bar"
		)
	);
	update_field( $field_key, $value, $post_id );


	// save a flexible content field value
	$field_key = "field_123456789";
	$value = array(
		array( "sub_field_1" => "Foo1", "sub_field_2" => "Bar1", "acf_fc_layout" => "layout_1_name" ),
		array( "sub_field_x" => "Foo2", "sub_field_y" => "Bar2", "acf_fc_layout" => "layout_2_name" )
	);
	update_field( $field_key, $value, $post_id );

	$fields = get_field_objects($post_id);
	// var_dump( $fields ); 
	$str = "<h1>New post ". $post_id . " created</h1>";
	$fff =  get_fields($post_id);
	var_dump($fff);
	if( $fff ){
		$str .= '<ul>';
			foreach( $fff as $name => $value ){
				$str .= '<li><b>'. $name . '</b> '. $value . '</li>';
			}
		$str .= '</ul>';
	}
	// $str .= '<pre>'. $fff ? 'true' : 'false' .'</pre>';
	return $str;
}


add_shortcode('add-referral', 'digma_add_referral');
function digma_add_referral($atts ) {
//	global $family_referral_map;
	$a = shortcode_atts( array(
		'family_name' => 'family name',
		'entity_type' => 'digma_referrals',
	), $atts );
	// ABSPATH .'/wp-content/plugins/ehu-events
	// vars
	$my_post = array(
		'post_title'	=> $a['family_name'].date("Y-m-d-h:i:sa"),
		'post_type'		=> $a['entity_type'],
		'post_status'	=> 'publish',
		'comment-status'  => 'closed',
      	'ping-status'     => 'closed',
      	//'post_author'   => 1,
	  	//'post_category' => array( 8,39 )
	);

$post_id = wp_insert_post( $my_post );
	// save a basic text value
	//			'key' => 'field_5b49a684fe226',

	$field_key = $family_referral_map['fields'][0]['name']; // "_field_5b49a684fe226"; // referral date
	$value = "1.2.2018";
	update_field( $field_key, $value, $post_id );


	// save a checkbox or select value
	$field_key = "family_referral_family_name";
	$value = "ישראלי";
	update_field( $field_key, $value, $post_id );

/*
error_log("Meta values of post:");
ob_start();
var_dump(get_post_meta($post_id));
error_log(ob_get_clean());
*/
	// insert the post into the database
	
	return "Created new referral for ". $a['family_name'] . " post_id=". $post_id ."  " .$family_referral_map['fields'][0]['name']."<br />";
}

// get line from excel and update entity with new field group data
// $line - excel line of data
// $keymap - mapping specific excel to field keys in field group

function add_new_fg($line, $row, $entity, $field_group){
	global  $family_referral_map;

	if ($row > 5) return;
		return $str = "Post title= " . $family_referral_map['fields'][0]['name'];
//	var_dump($line);
// new entity??
	$entity_post = array(
		'post_title'	=> $line[1], // $a['family_name'].date("Y-m-d-h:i:sa"),
		'post_type'		=> $entity,
		'post_status'	=> 'publish',
		'comment-status'  => 'closed',
      	'ping-status'     => 'closed',
      	'menu_order'	=> $row
      	//'post_author'   => 1,
	  	//'post_category' => array( 8,39 )
	);

	// insert the post into the database
	$post_id = wp_insert_post( $entity_post );



	// update fields within field group
	for ($i=0 ; $i < count($line) ; $i++){
		if (empty($line[$i])) continue; //empty field are allowed
		$field_key = $family_referral_map['fields'][$i]['name']; // "family_referral_initiator";
		$value = $line[$i]; //"some new string";
		//$post_id = 123456; //entity-id that owns the field group
		update_field( $field_key, $value, $post_id );
	}
	
}
	

/*
// read csv file from MS excel save as csv UTF8 with ';' as delimeter
// remove all \" double quotes - somehow it breaks the parsing to too many lines
// OPTION - get also number of columns to overcome this issue
// [get-csv file_name="<under uploads/yahav/..."  entity="<family|parents|kids...>" entity_type="<field group>" ]
add_shortcode('get-csv','digma_get_csv');
function digma_get_csv($atts ) {
	$a = shortcode_atts( array(
		'file_name' => '',
		'entity_type' => 'digma_families',
		'num_cols' => 'unused-now-TBD',
		'print_csv' => 0
	), $atts );

	if (empty($a['file_name']))
		return "file name missing";
	$str = "";
	$row = 1;
	$upload_dir   = wp_upload_dir();
	$fname = $upload_dir['basedir'] . $a['file_name'];
	if (($handle = fopen( $fname, "r")) !== FALSE) {
		$str ="<h1>Reading file " . $fname . "(". $a['entity_type'] . ") </h1>";
	//    while (($data = fgetcsv($handle, 5000, ";")) !== FALSE) {
		while (($line = fgets( $handle, 5000 )) !== FALSE){
			$data = explode( ';', $line );
			//print_r($line);
	
			if ($a['print_csv']){
		        $num = count($data);
		        $str .= "<p> $num fields in line $row: <br /></p>\n";
		        $row++;
		        for ($c=0; $c < $num; $c++){
		        	 if (empty($data[$c])) continue;
		             $str .= "(" . $c . "):  " . $data[$c] . "<br />\n";
		        }
		    }else $row++;

	        $str .= add_new_fg($data, $row, $a['entity_type'], "");
	    }
    } else 
    	return "<p>Fail to open " . $fname . "<br /></p>\n";
    fclose($handle);
    return $str;
}
*/

// read csv file from MS excel save as csv UTF8 with ';' as delimeter
// remove all \" double quotes - somehow it breaks the parsing to too many lines
// OPTION - get also number of columns to overcome this issue
// [get-csv file_name="<under uploads/yahav/..."  entity="<family|parents|kids...>" entity_type="<field group>" ]
add_shortcode('get-referrals','digma_get_referrals');
function digma_get_referrals($atts ) {
	global $family_referral_map;

	$a = shortcode_atts( array(
		'file_name' => '',
		'entity_type' => 'digma_families',
		'num_cols' => 'unused-now-TBD',
		'skip_first' => 1, 
		'print_csv' => 0
	), $atts );

	if (empty($a['file_name']))
		return "file name missing";

	$entity_post = array(
		'post_title'	=> $family_referral_map['fields'][0]['name'], // $a['family_name'].date("Y-m-d-h:i:sa"),
		'post_type'		=> $a['entity_type'],
		'post_status'	=> 'publish',
		'comment-status'  => 'closed',
      	'ping-status'     => 'closed',
      	//'post_author'   => 1,
	  	//'post_category' => array( 8,39 )
	);
	$line = array();

	$str = "";
	// Read the csv file /////////////////////////
	$row = 0;
	$upload_dir   = wp_upload_dir();
	$fname = $upload_dir['basedir'] . $a['file_name'];
	if (($handle = fopen( $fname, "r")) !== FALSE) {
	//    while (($data = fgetcsv($handle, 5000, ";")) !== FALSE) {
		while (($line[$row] = fgets( $handle, 2000 )) !== FALSE){
			$row++;
		}
	} else 
    	return "<p>Fail to open " . $fname . "<br /></p>\n";
    fclose($handle);

	$str .="<h1>Got ". $row ." lines from file " . $fname . "(". $a['entity_type'] . ") </h1>";

	// load each line to post - may need pre/post processing
	
    for ($l = $a['skip_first']? 1 : 0; $l < $row ; $l++){
echo ".";
		$data = explode( ';', $line[$l] );
		//print_r($line);
		if (count($data) < 5){
			$str .= "REMOVED LINE - too short line#=" . $l . " fields # = " . count($data) . " Probably wrong data<br/>";
			continue;
		}

		if ($a['print_csv']){
	        $num = count($data);
	        $str .= "<p> $num fields in line $row: <br /></p>\n";
	        $row++;
	        for ($c=0; $c < $num; $c++){
	        	 if (empty($data[$c])) continue;
	             $str .= "(" . $c . "):  " . $data[$c] . "<br />\n";
	        }
	    }else $row++;

	    $entity_post['post_title'] = $data[1] . " " . $data[2] . " " . $data[3];
	    $entity_post['menu_order'] = $row;

        // $str .= add_new_fg($data, $row, $a['entity_type'], "");
        $post_id = wp_insert_post( $entity_post );

        $expected_fields = count($family_referral_map['fields']);

		// update fields within field group
		for ($i=0 ; $i < count($data) ; $i++){
			if (empty($line[$i])) continue; //empty field are allowed
			if ($i >= $expected_fields) 
				continue;
			$field_key = $family_referral_map['fields'][$i]['name']; // "nice name" inside post;
			$value = $data[$i]; //"some new string";
			
			update_field( $field_key, $value, $post_id );
		}
		$str .= "#=<b>" . $row . "</b> משפחת=<b>" . $data[1] . "</b> נוצרה. פוסט #" . $post_id . "<br />";
		if ($i > $expected_fields)
			$str .= "TOO MANY FIELDS DETECTED on line=". $row . " Expected=". $expected_fields . " Got=" . count($data) . "<br />";
    }

    return $str;
}

add_shortcode('dpost', 'digma_delete_post');

function digma_delete_post($atts ) {
	$a = shortcode_atts( array(
		'entity_type' => 'digma_referrals' // digma_referrals'
	), $atts );

	if (empty($a['entity_type'])) return;

	$args = array('post_type' => $a['entity_type'], 'posts_per_page' => -1, 'post_status' => 'publish');

	$myposts = get_posts( $args );

    $i=0;
    $str="";
	foreach ( $myposts as $post ){
		if ($post->ID > 2892){
			$i++;
			wp_delete_post( $post->ID); // , $force_delete );
		} else $str .= "post " . $post->ID . " NOT DELETED<br />";
	}
	$str .=  $i . " posts deleted<br />";
	return $str;
}
?>