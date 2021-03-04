<?php
define ('MBO_UPLOAD_XL_DELET', 'mbo-xl-report'); // directory in uload dir for xl files
  
add_shortcode("delet2xl", 'mbo_delet2xl'); // show all meta data for cpt
$mbo_acflist = array();

function mbo_delet2xl($atts, $content = null ) {
	global $mbo_acflist;

	if( !current_user_can('administrator'))
		return "<h3>" . "אין לך הרשאות מתאימות" . "</h3>";

    $a = shortcode_atts( array(
        'acf-json-group' => 'group_5fdbb577d6bd3',		// acf FG definition in mycpt/acf-json
        'cpt'  =>  'digma_WorkplansDefs',				// cpt
		'cache' => 'true' 								// use transient - db cache
    ), $atts );

	$debug = 0; // shutdown debug printing

    // 1. generate map from json
	$fg_filename = WP_PLUGIN_DIR . '/mycpt/acf-json/' . $a['acf-json-group'] . '.json';
	if (file_exists($fg_filename)){
		if ($debug) error_log('mbo_delet2xl json file name'.$fg_filename);
	} else return "<h3>" . "קובץ הגדרה לא נמצא:" . $fg_filename . "</h3>";

	$str = file_get_contents( $fg_filename);
	if (!$str)
		return "<h3>" . "תקלת מערכת. לא ניתן לקרוא את הקובץ:" . $fg_filename . "</h3>";

	//	Now decode the JSON using json_decode():
	$json = json_decode($str, true); // decode the 
	if ($json == null || !$json)
		return "<h3>" . "תקלת מערכת. לא ניתן לפענח פורמט json:" . $fg_filename . "</h3>";

    // 2. utilities: generate mapping from acf to tabular data
	// $acfmap = array();
	$t0 = microtime(true);
	$acfmap = [];
	if ( false === ( $value = get_transient( 'mbo_acflist' ) ) ) {
		$acfmap = genAcfMap($json['fields'][0], $acfmap); // $mbo_acflist is set as global variable, thus side effect!
		$t = time();
		set_transient( 'mbo_acflist', array($t, $mbo_acflist), 365 * 24 * 3600 ); // a whole year...
	} else {
		$mt = intval($value[0]);
		if ($mt > filemtime($fg_filename))
			$mbo_acflist = $value[1];
		else{
			$acfmap = genAcfMap($json['fields'][0], $acfmap); // $mbo_acflist is set as global variable, thus side effect!
			$t = time();
			set_transient( 'mbo_acflist', array($t, $mbo_acflist), 365 * 24 * 3600 ); // a whole year...
		}
	}
	$t1 = microtime(true);
	error_log('Microtime mbo_acflist= '. ($t1 - $t0));

	//if ($debug) error_log('acfmap:'. print_r($mbo_acflist, true)); // GOOD for debug
	$map2xl = array(); // Heaader names of excel file
	$name2field = array(); // mapping names to field type
	$out = "";
	$i = 0;
	$xl = 0;
	foreach ($mbo_acflist as $item){
		if ($item['type'] == "message" || $item['type'] == "tab" || $item['name'] == "" || $item['name'] == []) continue;
		$repeater_data = $item['type'] != "repeater" ?  "" : $item['min'] . ":" . $item['max'] ;

		$line = "<td>" . $i++ . "</td>" . "<td>" . $xl .':' . itoAA($xl). "</td>" 
				. "<td>". $item['label'] . "</td>"
				. "<td style='direction:ltr'>" . $item['name'] . "</td>" 
				. "<td style='direction:ltr'>" . $item['type'] . "</td>"
				. "<td>" . $repeater_data . "</td>";
		$out .= "<tr>". $line . "</tr>";

		$v = esc_xl_comma($item['label']);
		if (isset($name2field[$item['name']])) // DEBUG
			error_log('REWRITE in name2field. current='. print_r($name2field[$item['name']], true) . 'newvalue='. 
				print_r(array('type'=> $item['type'], 'col' => $xl, 'col_id' => $item['name'], 'label' => $v), true));

		$name2field[$item['name']] = array('type'=> $item['type'], 'col' => $xl, 'col_id' => $item['name'], 'label' => $v);
		$map2xl[$xl] = $v; //$item['label']; // $item['name']; // $v
		if ($debug) error_log('map2xl col='. $xl . ' label = '. $v . '  name='. $item['name']);
		$xl += 1;

		if ($item['type'] == "repeater"){ // handle ONLY SIMPLE children no groups/repeater grandchildren
			$repeatermap2xl = array();
			$res = do_repeater($item, ($i-1), $xl);
			$out .= $res[0];
			for ($k = 0; $k < count($res[1]); $k++){
				$v = $res[1][$k]; // name id
				// take until first underscore & add name (id) of field
				//$name2col_xl[$item['name'] . '_' . $v] = $xl; // in repeater sub fields must have parent name
				$name2field[$item['name'] . '_' . $v] = array('type'=> $item['type'], 'col' => $xl, 'col_id' => $item['name'] . '_' . $v, 'label' => $res[2][$k]);
				$map2xl[$xl] = $res[2][$k]; // $item['label']; // $v;
				if ($debug) error_log('map2xl col='. $xl . ' label = '. $res[2][$k] . '  name='. $item['name'] . '_' . $v);
				$xl += 1;
			}
		}
	}
	$t2 = microtime(true);
	error_log('Microtime map2xl = '. ($t2 - $t1));
	// 3. map all post types (plans) to long comma seperated list (csv)
	// and write it to file in WordPress upload directory 
    
	$xl_col_header = $map2xl;

	//if (count($xl_col_header) != count($name2field))
	//	error_log('ERROR SIZE OF map2xl = '. count($xl_col_header) . ' name2field='. count($name2field));

	$xl_lines = get_data_from_cpt($a['cpt'], $name2field);
	// target file name
	$xl_filename = get_delet_xl_filename();

	$t3 = microtime(true);
	error_log('Microtime get_data_from_cpt = '. ($t3 - $t2));
	//test_fputcsv($xl_filename, $xl_col_header,$xl_lines);
	//test2_fputcsv($xl_filename, $xl_col_header,$xl_lines);

	$newmap = implode (",", $xl_col_header);
	$csvfile = $newmap . "\n";

	for($i=0; $i < count($xl_lines) ; $i++){
		$l = implode (",", $xl_lines[$i]); // all inner items wrapped in "" with single " replaced by ''
		$csvfile .= $l . "\n";
	}
	file_put_contents ( $xl_filename, "\xEF\xBB\xBF" . $csvfile . "\n");
	$t4 = microtime(true);
	error_log('Microtime implode & file_put_contents = '. ($t4 - $t3));
	//if (count($xl_col_header) != count($name2index))
	$testsr = count($xl_col_header) != count($name2field) ? "<p>אופס.. תקלה!!! אי תאימות עמודות xl_col_header=" . count($xl_col_header) . "   name2index= ". count($name2field) . "</p>"
				: '<p>סה"כ עמודות אקסל: ' . count($xl_col_header) . "</p>";

	return $testsr . "<table><tbody>" . xl_get_header() . $out . "</tbody></table>";// . "<p>" . print_r($name2field, true) . "</p>";
}

function gen_mbo_acflist($json, $usecache){
	global $mbo_acflist;
	$acfmap = "";
	if (!$usecache){
		$acfmap = genAcfMap($json['fields'][0], $acfmap); // $mbo_acflist is set as global variable, thus side effect!
		$t = time();
		set_transient( 'mbo_acflist', array($t, $mbo_acflist), 365 * 24 * 3600 ); // a whole year...
		error_log(' Got time ='.$t);
		if (!set_transient( 'mbo_acflist_create_time', $t, 365 * 24 * 3600 ))
			error_log(' set_transient mbo_acflist_create_time FAIL ='.$t);
		} else { // use cache if exists
		if ( false === ( $value = get_transient( 'mbo_acflist' ) ) ) {
			$acfmap = genAcfMap($json['fields'][0], $acfmap); // $mbo_acflist is set as global variable, thus side effect!
			set_transient( 'mbo_acflist', $mbo_acflist, 365 * 24 * 3600 ); // a whole year...
			$t = time();
			error_log(' Got time ='.$t);

			if (!set_transient( 'mbo_acflist_create_time', $t, 365 * 24 * 3600 ))
				error_log(' set_transient mbo_acflist_create_time FAIL ='.$t);
			 // a whole year...
		} else $mbo_acflist = $value; // side effect!!
	}
	return true;
}

// read all posts - each to a single line
function get_data_from_cpt($cpt, $name2field){
	// get files data
	$filesdata = get_files_data($cpt);
	// if ($debug) error_log('p data='. print_r($filesdata, true));
	$res_array = locate_fields($filesdata, $name2field); // foreach field in data locate column
	return $res_array;
}

// foreach field in data locate column
function locate_fields($filesdata, $name2field){
	$debug = 0;
	$i = 0;
	$res_array = array();
	foreach($filesdata as $f){
		$outline = map_cpt2xl($f, $name2field);
		$res_array[$i]= $outline;
		if ($debug) error_log('locate columns INPUT'. print_r($f, true));
		if ($debug) error_log('locate columns OUTPUT'. print_r($outline, true));
		//if ($i > 2) break;
		$i += 1;
	}
	return $res_array;
}
// map cpt values to excel sheel columns
// note that cpt holds some values not all columns are filled
function map_cpt2xl($f, $name2field){
	$outline = array();
	
	//error_log('name2field SIZE='. count($name2field));
	for ($j = 0; $j < count($name2field); $j++){
		$outline[$j] = "";
	}

	foreach ($f as $fi){
		if (is_array($fi)){
			foreach ($fi as $k => $v){

				if ($k == "" | $k == []) continue;
				if (!isset($name2field[$k]))
					error_log('======= column not found ======== name2field k='. $k);
				else {
				 	// DEBUG - error_log('map_cpt2xl k='. print_r($k, true) . '   v=' . print_r($v, true) . ' name2field='. print_r($name2field[$k], true));
					$ftype = $name2field[$k]['type'];

					//if (($k == "wp_solution_area_other_goal" || $k == "wp_solution_area_other_table")) // ($name2field[$k]['col'] == 62 || $name2field[$k]['col'] == 63) &&
					//	error_log("hi");

					if ($ftype == "number" || $ftype == "text" || $ftype == "textarea"|| $ftype == "select" || $ftype == "radio" || $ftype == "date_picker")
						$outline[$name2field[$k]['col']] =  esc_xl_comma($v); //esc_xl_comma('"'.$v.'"') ;

					else if ($ftype == "checkbox" ) { // handle multiple values (serialized??)
						if (isset($v) && is_array($v)){
							if (empty($v)) $outline[$name2field[$k]['col']] = "";
							else $outline[$name2field[$k]['col']] = esc_xl_comma(implode(' | ', $v)) ; // 'checkbox';
						}else $outline[$name2field[$k]['col']] = "";
						//if (($name2field[$k]['col'] == 66 || $name2field[$k]['col'] == 198) && !empty($v))
						//	error_log('TEST CHECKBOX map_cpt2xl k='. print_r($k, true) . '   v=' . print_r($v, true) . ' name2field='. print_r($name2field[$k], true));
					}
					else if ($ftype == "repeater" ) { // get array and put in correct columns
						$jcount = 1;
						if (isset($v) && is_array($v)){
							for ($i = 0; $i < count($v); $i++){
								if (isset($v[$i]) && is_array($v[$i])){
									$vi = $v[$i];
									foreach ($vi as $kk => $vv)
										$outline[($name2field[$k]['col']+$jcount++)] = esc_xl_comma($vv); // esc_xl_comma('"'. $vv . '"');
								}
							}
						} else if (isset($v) && !is_array($v) && !empty($v))
							error_log('========= repeater not handled' . $ftype . '  NOT AN ARRAY! MIGHT BE EMPTY?? see description above!! :-) ');
						else $outline[($name2field[$k]['col']+$jcount++)] = ""; // empty array - ok
 					} 
					else if ($ftype == "group" /*|| $ftype == "tab"*/ )
						$outline[$name2field[$k]['col']] = ""; // $name2field[$k]['col'] . 'label:'.$name2field[$k]['label'];
					else error_log('========= type not handled' . $ftype . '  see description above!! :-) ');
				}
			}
		}
	}
	//if ($debug) error_log('outline SIZE='. count($outline));
	
	return $outline;
}
function get_files_data($post_type){
	$progs = get_posts( array('post_type' => $post_type, 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($progs) < 1)
        return "No post of type= ". $post_type . " found";
	$arr = array();
	foreach ($progs as $prog){
		$arr[$prog->ID] = get_field('workplans_plan_3', $prog->ID);
	}
	return $arr;
}
function do_repeater($item, $index, $xl){
	$out = "";
	$i = 0; 
	$repeatermap2xllable = array();
	$repeatermap2xlname = array();
	for ($j = 0; $j < $item['max'] ; $j++){
		$subf = $item['sub_fields'];
		foreach ($subf as $sf){
			$line = "<td>" . $index . "(". $j . ")" . "</td>" . "<td>" . ($xl+$i).':' . itoAA($xl+$i) . "</td>"
			. "<td>" . $j. '_' . $sf['label'] . "</td>"
			. "<td style='direction:ltr'>" . $j. '_' . $sf['name'] . "</td>" 
			. "<td style='direction:ltr'> " . $sf['type'] . "</td>"
			. "<td>" . "#". $j . "</td>";
			$out .= "<tr>". $line . "</tr>";
			$repeatermap2xllable[$i] = $j . '_' . $sf['label'];
			$repeatermap2xlname[$i] = $j . '_' . $sf['name'];
			$i += 1;
		}
	}
	return [$out, $repeatermap2xlname, $repeatermap2xllable];
}
// csv escape comma
function esc_xl_comma($text){
	if (!isset($text) || $text == "") return "";
	$ntext = str_replace('"', "''", $text);
	//return '"' . str_replace(",", ';', $text) . '"';
	return '"' . $ntext . '"';
}
// excel column presentation
function itoAA($n){
	for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
		$r = chr($n%26 + 0x41) . $r;
	return $r;
}

function xl_get_header(){
	$line = "<td>" . '#' . "</td>" . "<td>" . 'מיפוי' . "</td>" 
				. "<td>". 'שם השדה' . "</td>"
				. "<td style='direction:ltr'>" . 'מזהה בתוכנה' . "</td>" 
				. "<td style='direction:ltr'>" . 'סוג' . "</td>"
				. "<td>" . 'הערות' . "</td>";
	return "<tr style='font-weight:bold'>". $line . "</tr>";
}

function genAcfMap($start, $acfMap){
	global $mbo_acflist;
	if ($start['type'] == "tab" || $start['type'] == "message"){ // ignore
		$mbo_acflist[$start['key']] = $start;
		return $acfMap;
	}
	// handle inner node in tree
	if ($start['type'] == "group" /*|| $start['type'] == "repeater"*/){ // treat repeater as regular field
		$mbo_acflist[$start['key']] = $start;
		if (isset($start['sub_fields'])){
			for ($i=0; $i < count($start['sub_fields']); $i++)               
				genAcfMap($start['sub_fields'][$i], $acfMap);
		} else return $acfMap;
	}
	// handle simple leaf in tree
	if ($start['name'] == ""){
		error_log('Empty name = ' . $start);
		return $acfMap;
	}

	$acfMap[$start['key']] = $start;
	$acfMap[$start['name']] = $start;
	if ($start['type'] != 'group') // collect before - not after
		$mbo_acflist[$start['key']] = $start;
	//error_log('RECURS=<pre style="direction:ltr">' . print_r($acfMap, true) . '</pre>');
	return $acfMap;
}


function get_delet_xl_filename(){
	$upload_dir = delet_xl_uploadir(MBO_UPLOAD_XL_DELET);
    return $upload_dir . "/" . gen_delet_xl_fname();
}

// Generates xl file name based on creation time
function gen_delet_xl_fname(){
    date_default_timezone_set('Asia/Jerusalem');

    $today = getdate();
    $fname = sprintf("DeletXlReport%4d-%02d-%02d-%02d%02d%02d.xls", $today['year'], $today['mon'], $today['mday'], 
                                $today['hours'], $today['minutes'], $today['seconds']);
    return $fname;
}
// verify directory exists
function delet_xl_uploadir($dir) {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/'. $dir;
    if (! is_dir($upload_dir)) {
        mkdir( $upload_dir, 0755 );
    }
    return $upload_dir;
}

function test2_fputcsv($xl_filename, $xl_col_header,$xl_lines){
	$csv = implode (",", $xl_col_header) . "\n"; // xl header
	for($i=0; $i < count($xl_lines) ; $i++)
		$csv .= implode (",", $xl_lines[$i]). "\n"; // all inner items wrapped in "" with single " replaced by ''
	
	$encoded_csv = mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
	$nfile = 'new-'.$xl_filename;
	file_put_contents ( 'hello.csv', chr(255) . chr(254) . $encoded_csv);
	// file_put_contents ( $xl_filename.'.xlsx', chr(255) . chr(254) . $encoded_csv);
	//file_put_contents ( $xl_filename.'.csv', "\xEF\xBB\xBF" . $newmap . "\n");
	
	$testsr = '<p>סה"כ עמודות אקסל: ' . count($xl_col_header) . "</p>";
	return $testsr;
}

function test_fputcsv($xl_filename, $xl_col_header,$xl_lines){
	$sep  = "\t";
    $eol  = "\n";

    $csv  =  count($xl_col_header) ? '"'. implode($sep, $xl_col_header).'"'.$eol : '';
    foreach($xl_lines as $line) {
      $csv .= '"'. implode('"'.$sep.'"', $line).'"'.$eol;
    }
	$encoded_csv = mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
	$encoded_with_BOM = chr(255) . chr(254) . $encoded_csv;

	file_put_contents ( $xl_filename.'.csv', $encoded_with_BOM); //, FILE_APPEND);

	return "";
}
?>