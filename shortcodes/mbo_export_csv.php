<?php
// Export Haya's report, ALL families
define ('MBO_UPLOAD_XL_REPORT', 'mbo-xl-report');
// define ('MBO_XL_DELIM', '^');already defined


add_shortcode("mbo_export_csv", 'mbo_export_csv'); // show all meta data for cpt

function mbo_export_csv(){
    // if (($s = mbo_permissions()) != "ok") return $s;
    
    // get data from database
    $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($programCpt) < 1)
        return "No post of type= ". $type ." found";

    // init once all family fields for histograms
    $programCpt_fieldGroups = array();
    $cnt = 0; $missing =0;
    $slen = array(0,0,0,0);
    foreach ( $programCpt as $cpt ){ // collect snapshots
        //$programCpt_fieldGroups[$cpt->ID] = get_fields($cpt->ID);
        $snapshots[$cpt->ID] = get_field('snapshots', $cpt->ID);
        if ($snapshots[$cpt->ID]){
            $cnt += 1;
            $slen[count($snapshots[$cpt->ID])] += 1;
            //error_log("snapshot table: " . print_r($snapshots[$cpt->ID], true));
        }
        else $missing +=1;
    }
    $str = '<div style="direction:ltr; text-align: center;">';
    $str .= 'snapshots ='. $cnt . '  missing='. $missing. '<br />';
    for ($i=1; $i < count($slen) ; $i++)
        $str .= $slen[$i] . " families with ". $i . " snapshots <br />";
    $str .= '</div>';

    return $str;
    // start filter by family program status, snapshots etc...

    // go over all fg's - generate field titles, then data
    //return mbo_test_xl();
}
function mbo_test_xl(){
    $nFields = 12000;
    if (!($fh = mbo_report_file())) // if false return
        return 'Fail to generate file for '.$nFileds.' fields to file - check dir & file name';

    $s = xl_gen_fields($nFields);
    fwrite($fh, $s);
    fclose($fh);
    return 'Wrote '.$nFields.' fields to file';
}
function xl_gen_fields($n){
    $s = "";
    for ($i=0; $i<$n; $i++)
        $s.= 'field-'. $i . MBO_XL_DELIM;
    return $s;
}
function mbo_xl_uploadir($dir) {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/'. $dir;
    if (! is_dir($upload_dir)) {
        mkdir( $upload_dir, 0755 );
    }
    return $upload_dir;
}
function mbo_report_file(){
    date_default_timezone_set('Asia/Jerusalem');
    $updir = mbo_xl_uploadir(MBO_UPLOAD_XL_REPORT);

    $today = getdate();
    $fname = sprintf("XlReport%4d-%02d-%02d-%02d%02d%02d.csv", $today['year'], $today['mon'], $today['mday'], 
                                $today['hours'], $today['minutes'], $today['seconds']);
//error_log("mbo_report_file=". $fname);
    $handle = fopen($updir."/".$fname, "w");
    return $handle;
}
    

