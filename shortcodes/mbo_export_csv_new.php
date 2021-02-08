<?php
// Export Haya's report, ALL families
define ('MBO_UPLOAD_XL_REPORT', 'mbo-xl-report');
define ('MBO_MAX_TABLE_LINES', 20); // maximum lines in table e.g. kids
// define ('MBO_XL_DELIM', '^');already defined


add_shortcode("mbo-export-csv-new", 'mbo_export_csv_new'); // show all meta data for cpt
$monthly_balance = array();
$meta_map = ""; // holds details on table map to columns
$parent_meta = ""; // holds details on parents tables map to columns
$parent_meta_full ="";

function mbo_export_csv_new(){
    global $monthly_balance, $meta_map;

    // if (($s = mbo_permissions()) != "ok") return $s;
    // open file for results
    $fname = gen_xl_fname(); // 
    if (!($fh = mbo_report_file($fname))) // open handle to output csv file, if false - abort
        return '<h2>Fail to generate file for '.$nFileds.' fields to file - check dir & file name</h2>';
    // get data from database
    $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($programCpt) < 1)
        return "No post of type= ". $type ." found";
    foreach ($monthly_balance as $mb) $mb=0; // initialize

    // init once all family fields for histograms
    $families_to_scan = array();
    $cnt = 0; $missing =0; $writeln = 0;
    $slen = array(0,0,0,0);
    $hdr = true;
    // collect snapshots of families to scan
    foreach ( $programCpt as $cpt ){ 
        $snapshots[$cpt->ID] = get_field('snapshots', $cpt->ID);
        if (!$snapshots[$cpt->ID] || empty($snapshots[$cpt->ID])){
            //error_log("No snapshots for cptID=".$cpt->ID);
            $missing +=1;
            continue;
        }
        if ($hdr && count($snapshots[$cpt->ID])>2){ // only at loop start - write csv header
            $s = xport_to_csv($snapshots[$cpt->ID], $hdr, $cpt->ID);
            fwrite($fh, $s);
            $hdr = false;
        }
        if ($snapshots[$cpt->ID]){
            $cnt += 1;
            $slen[count($snapshots[$cpt->ID])] += 1;
            //error_log("snapshot table: " . print_r($snapshots[$cpt->ID], true));
        }
        else $missing +=1;
        if (count($snapshots[$cpt->ID])>2){ // we have 3 snapshots... final as well
            $families_to_scan[$cpt->ID] = $snapshots[$cpt->ID]; // generate list of families with ALL snapshots to scan
            $s = xport_to_csv($snapshots[$cpt->ID], $hdr, $cpt->ID);
            fwrite($fh, $s); // write_xl_line($fh, $lstr);
            // error_log("FWRITE writeln=". $writeln++);
        }
    }
    // go over families & generate per family the line in csv line
    foreach($families_to_scan as $k => $v){
        //error_log("KEY = ", $k );
        $s = xport_to_csv($v, $hdr, $k);
        fwrite($fh, $s); 
    }
    fclose($fh);
    error_log("familiestoscan=". print_r($families_to_scan, true));
    $str = '<div style="direction:ltr; text-align: left;margin-left:5em;">';
    //$str .= 'snapshots ='. $cnt . '  missing='. $missing. '<br />';
    $str .= '<h2>Some statistics</h2>';
    for ($i=1; $i < count($slen) ; $i++)
        $str .= $slen[$i] . " families with ". $i . " snapshots <br />";
    $str .= '<h3>Only families with 3 snapshots are considered. <b>Total ' . $slen[3] . '</b></h3>';
    $str .= '<p>Results in <a href="'.mbo_report_url($fname).'" target="_blank">'. $fname . '</a>';
    $str .= ' (click on link to download)<br />See import to Excel instructions at the bottom of this page.<br />';
    $str .= 'Note: All generated reports are stored on site for future use.</p>';
    $str .= $meta_map;
    $str .= '</div>';
    $str .= '<div style="direction:ltr;text-align:left;margin-left:80px">';
    $str .= '<p>Excel parameters to import the .csv file:<br />';
    $str .= '<ol>';
    $str .= '<li>Click the link above to download the file to your disk (You will need it soon...) </li>';
    $str .= '<li>Open new tab in excel (no need if its new file)</li>';
    $str .= '<li>Select Data >> Import from external files >> select **Text** file.</li>';
    $str .= '<li>Browse your files to find the downloaded csv file</li>';
    $str .= '<li>Click the import button</li>';
    $str .= '<li>The following parameters MUST be set:</li>';
    $str .= '<ol>';
    $str .= '<li>Seperated radio button</li>';
    $str .= '<li>Start import at line #1</li>';
    $str .= '<li>File source, select: 65001 Unicode (UTF-8)</li>';
    $str .= '<li>Check: my data contains headers for columns</li>';
    $str .= '<li>Seelct NEXT</li>';
    $str .= '<li>Unselect: TAB and select Other option. Enter: ^ (caret, SHIFT-6)</li>';
    $str .= '<li>Click: Finish/OK all the way</li>';
    $str .= '</ol></ol></p></div>';




    return $str;
    // start filter by family program status, snapshots etc...

    // go over all fg's - generate field titles, then data
    //return mbo_test_xl();
}
// tlist holds all data that is kept in snapshots
$tlist = array('program_info', 'family_housing', 'family_legal', 'debts_status', 'family_savings', 'family_products_services', 
        'rights_q', 'family_kids', 'family_income', 'family_expenses', 'family_debts', 'family_mortgage', 'family_rights', 'family_balance',
        'questionnaire_1year', 'questionnaire_2year', 'needsolution_array');
// rlist holds all data not in snapshots taken from main source
$rlist = array('questionnaire_1year', 'questionnaire_2year', 'needsolution_array');

/*, 'family_meeting_summary',  'family_members' );*/
// Return a line (family) of csv file
function xport_to_csv($stable, $hdr, $cptId){
    global $tlist, $rlist, $monthly_balance, $meta_map;

    $clen = array(); // test columns
    //// ORDER IS VIATL! for balance calculation, must be last to accumulate values
    $family_balance_hdr = 'total_monthly^work_income^rights_income^capital_income^expenses_monthly^diur^communication^shotef^';
    $family_balance_hdr .= 'debts_sum^debts_monthly^mortgage_sum^mortgage_monthly^rights_monthly^right_one_time^rights_other^'; 
    $lstr = "";
    $meta = "";
    $prev_ttmp = 0;
    $parent_tables_data = false; // flag to display parent tables metadata once

    for ($i=0; $i < 3 ; $i++){
        if ($i == 1 ) continue;
        init_monthly_balance($family_balance_hdr);
        $family_tables = get_fields($stable[$i]['link']); // start
        //error_log("xport_to_csv - family_tables=".print_r($family_tables,true));
        foreach ($tlist as $tname){ // go over list of table-form
            if (!isset($family_tables[$tname])){
                error_log("MISSING COLUMNS: Missing table=". $tname . "  in snapshot phase=". $i . "  cptId=". $stable[$i]['link']);
                $info = false;
            } else 
                $info = $family_tables[$tname];
            
            switch ($tname){
                case 'family_income':   if ($hdr) $lstr .= "income monthly^ work income^rights income^capital income^";
                                        else $lstr .= !$info ? "0^0^0^0^" : do_table_income($info);
                                        break;
                case 'family_rights':   if ($hdr) $lstr .= "rights_monthly^right_one_time^rights_other^";
                                        else $lstr .= !$info ? "0^0^0^" : do_table_rights($info);
                                        break;
                case 'family_expenses': if ($hdr) $lstr .= "expenses monthly^diur^communication^shotef^other^";
                                        else $lstr .= !$info ? "0^0^0^0^0^" : do_table_expenses($info);
                                        break;
                case 'family_debts':    if ($hdr) $lstr .= "total debts^monthly return^";
                                        else $lstr .= !$info ? "0^0^" : do_table_debts($info);
                                        break;
                case 'family_mortgage': if ($hdr) $lstr .= "total mortgage^monthly mortgage return^";
                                        else $lstr .= !$info ? "0^0^" : do_table_mortgage($info);
                                        break;
                case 'family_balance':  if ($hdr) $lstr .= $family_balance_hdr;
                                        else $lstr .= do_balance($family_balance_hdr); // calculated data
                                        break;
                case 'needsolution_array':
                                        if ($i != 2) // only once towards the end
                                            break;
                                        //error_log("doing NS");
                                        if ($hdr) {
                                            $lstr .= serialize_table_hdr($info);
                                            //error_log("NS phase=".$i." hdr cols=".substr_count(serialize_table_hdr($info), MBO_XL_DELIM));
                                        }
                                        else {
                                            $lstr .= serialize_table($info); // only once and at the end
                                            //error_log("NS phase=".$i." data cols=".substr_count(serialize_table($info), MBO_XL_DELIM));
                                        }
                                        // Add need solution (NS) summary calculation
                                        if ($hdr) {
                                            $lstr .= "ns_total_cost^ns_yahav^ns_self^ns_revaha^ns_youth^ns_zedaka^ns_gov^ns_supp^";
                                            //error_log("NS phase=".$i." hdr cols=".substr_count(serialize_table_hdr($info), MBO_XL_DELIM));
                                        } else $lstr .= do_table_ns_summary($info);
                                        //break;
                case 'parents':         
                                        // MUST FOLLOW LAST family table currently 'needsolution'
                                        // All parents data & headers is handled in thefunction below. Same concepts & utilities as fro families
                                        $lstr .= prents_tables($stable, $hdr, substr_count($lstr, MBO_XL_DELIM)); // $n substr_count($lstr, MBO_XL_DELIM)); // do both phases
                                        $parent_tables_data = true;
                                        break;
                case 'family_kids':     if ($hdr)
                                            $lstr .= serialize_table_hdr($info);
                                        else
                                            $lstr .= serialize_table($info);
                                        break;
                //case 'outof_snapshot':  $lstr .= outof_snapshot_data($rlist, $hdr, $cptId);break;
                default: 
                                        if ($info == false || empty($info)){ // empty table
                                            error_log(sprintf("EMPTY/FALSE Table %s phase=%d cptId=%d\n", $tname, $i, $stable[$i]['link']));
                                        }
                                        if (in_array($tname, array('questionnaire_1year', 'questionnaire_2year')) && $i != 2){
                                            break;
                                        } else {
                                            $cindex = 0;
                                            foreach ($info as $k => $v){ // write the values
                                                $cindex +=1;
                                                if ($hdr){
                                                    $lstr .= $k . MBO_XL_DELIM;
                                                    //error_log("HDR=". $lstr);
                                                } 
                                                else {
                                                    $lstr .= $v . MBO_XL_DELIM; // str_replace(array("\n", "\r", "\t"), ' ', trim($v))
                                                }
                                            }
                                            if ($hdr)
                                                $clen[$tname.'h-'.$i] =  $cindex;
                                            else
                                                $clen[$tname.'-'.$i] =  $cindex;
                                        }
            }
            //$ttmp = explode('^', $lstr);
            //$n = count($ttmp);
            
            $n = substr_count($lstr, MBO_XL_DELIM);
            $meta .= sprintf('<tr><td>%d</td><td>%s</td><td>%4d</td><td>%6d</td><td>%s</td></tr>', 
                    $i, $tname, ($n - $prev_ttmp), $n, GetExcelColumnName($prev_ttmp));
            $prev_ttmp = $n;
            if ($parent_tables_data){
                $meta .= get_parent_full_meta_data();
                $parent_tables_data = false;
            }
        }
        //error_log("BEFORE: lstr-len=". substr_count($lstr, MBO_XL_DELIM). " phase=".$i);
        //$lstr = pad_col_x1000();
        if ($i < 2){ // do not add padding at the end
            $kk = substr_count($lstr, MBO_XL_DELIM);
            $pad_limit = 300;
            $m  = $kk % $pad_limit; // pad to multiplication of 1000
            for ($k=0; $k < $pad_limit - $m; $k++ )
                $lstr .= MBO_XL_DELIM;
            $n = substr_count($lstr, MBO_XL_DELIM);
            $meta .= sprintf('<tr><td>%d</td><td>%s</td><td>%4d</td><td>%6d</td><td>%s</td></tr>', 
                    $i, "padding***", ($n - $prev_ttmp), $prev_ttmp, GetExcelColumnName($prev_ttmp));
            $prev_ttmp = $n;
        }
        // error_log ("CLEN =". print_r($clen, true));
        //error_log("AFTER: lstr-len=". substr_count($lstr, MBO_XL_DELIM). " phase=".$i);
        // first parent tables
        // second parent tables
        /////$lstr = str_pad($lstr, ($i+1)*1000, MBO_XL_DELIM); // fill missing cells
    }
    

    $meta_map = "<h2>Table of contents</h2><ol>";
    $meta_map .= "<li>Section 1: Family data</li>";
    $meta_map .= "<li>Section 2: Woman's followed by Man's data, Initial Phase (0)</li><li>Section 3: Woman's followed by Man's data, Final Phase (2)</li></ol>";
    $meta_map .= '<table width="500"><tbody>';
    $meta_map .= '<tr><td><b>phase</b></td><td><b>table name</b></td><td><b># of cols</b></td><td><b>end col</b></td><td><b>xl start col</b></td></tr>';
    $meta_map .= $meta . '</tbody></table>';
    // error_log("lstr-len=". substr_count($lstr, MBO_XL_DELIM));
    return $lstr."\n";
}
// $rlist = array('questionnaire_1year', 'questionnaire_2year', 'needsolution_array');

function outof_snapshot_data($rlist, $hdr, $cptId){
    // $info, $tname,
    $lstr = "";

    foreach ($rlist as $tname){
        $info = get_field($tname, $cptId);
    
        if ($info == false || empty($info)){ // empty table
            error_log(sprintf("outof_snapshot_data: Table %s no phase cptId=%d\n", $tname, $cptId));
        }
        if ($tname == "needsolution_array"){
            if ($hdr)
                $lstr .= serialize_table_hdr($info);
            else
                $lstr .= serialize_table($info);
            continue;
        }
        foreach ($info as $k => $v){ // write the values
            if ($hdr){
                $lstr .= $k . MBO_XL_DELIM;
                //error_log("HDR=". $lstr);
            } 
            else {
                if (is_array($v)){
                    error_log("outof array". print_r($v, true));
                    $lstr .= "IM ARRAY see debug.log" . MBO_XL_DELIM;
                } else 
                    $lstr .= $v . MBO_XL_DELIM; // str_replace(array("\n", "\r", "\t"), ' ', trim($v))
            }
        }
    }
    return $lstr;
}
function serialize_table_hdr3($t){
    $lstr = "";
    for ($i = 0; $i < 3; $i++){
        foreach ($t[0] as $k => $v){
            $lstr .= $k . MBO_XL_DELIM;
        }
    }
    return $lstr;
}
// draw table column header MBO_MAX_TABLE_LINES times, ignore 'id'
function serialize_table_hdr($t){
    $lstr = "";
    for ($i = 0; $i < MBO_MAX_TABLE_LINES; $i++){
        foreach ($t[0] as $k => $v){
            $lstr .= $k . MBO_XL_DELIM;
        }
    }
    return $lstr;
}
function serialize_table($t){
    $lstr = "";
    foreach ($t as $line){
        foreach ($line as $k => $v){
            if ($k != 'needsolution_comment')
                $lstr .= $v . MBO_XL_DELIM;
            else $lstr .= (empty($v) ? " " : preg_replace('/[\x00-\x1F\x7F]/', ' ', $v)) . MBO_XL_DELIM;
            //error_log("serialize_table needsolution_comment=". preg_replace('/[\x00-\x1F\x7F]/', ' ', $v)); // just replace w/blank 0-31 and 127
            //else error_log("serialize_table needsolution_comment=".addcslashes($v, "\0..\31!@\@\177..\377"));
        }
    }
    for ($i = count($t); $i < MBO_MAX_TABLE_LINES; $i++)
        for ($j = 0; $j < count($t[0]); $j++) // start at 1, to ignore id
            $lstr .= '0' . MBO_XL_DELIM;
    return $lstr;
}
/*
function write_xl_line($fh, $line){
    if (!($fh = mbo_report_file())) // if false return
        return 'Fail to generate file for xport to csv - check dir & file name';
    fwrite($fh, $line);
    fclose($fh);
}*/
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
// Generates xl file name based on creation time
function gen_xl_fname(){
    date_default_timezone_set('Asia/Jerusalem');

    $today = getdate();
    $fname = sprintf("XlReport%4d-%02d-%02d-%02d%02d%02d.csv", $today['year'], $today['mon'], $today['mday'], 
                                $today['hours'], $today['minutes'], $today['seconds']);
    return $fname;
}
// return url for genertaed xl file
function mbo_report_url($fname){
    $upload = wp_upload_dir();
    return $upload['baseurl']."/".MBO_UPLOAD_XL_REPORT."/".$fname;
}
// return handle to file
function mbo_report_file($fname){
    $updir = mbo_xl_uploadir(MBO_UPLOAD_XL_REPORT);
//error_log("mbo_report_file=". $fname);
    $handle = fopen($updir."/".$fname, "w");
    return $handle;
}

// return the following values: total monthly, work income, rights income, capital income,
function do_table_income($t){
    global $monthly_balance;

    $work = array('משכורת', 'הכנסה מעסק');
    $capital = array('הכנסה מהון');
    $freq = array('חודשי' => 1, 'דו חודשי' => 2 ,'רבעוני' => 4,'שנתי'=> 12);
    $total_monthly = 0;
    $work_income = 0;
    $rights_income = 0;
    $capital_income = 0;

    if ($t == false || empty($t) || (count($t) == 1 && empty($t[0]['income_sum']))) // empty table
        return "0^0^0^0^";
    
    foreach ($t as $line){
        // assuming income is monthly
        if (!isset($line['income_sum']) || empty($line['income_sum'])) continue;

        if (!isset($freq[$line['income_frequency']])) continue; // ignore unknown/missing frequecy values
        $m_in = (int) (intval($line['income_sum']) / intval($freq[$line['income_frequency']])); // normalize frequence to monthly income
        $total_monthly += $m_in;
        if (in_array($line['income_type'], $work))
            $work_income += $m_in;
        else if (in_array($line['income_type'], $capital))
            $capital_income += $m_in;
        else // rights or other
            $rights_income += $m_in;
    }
    $monthly_balance['total_monthly'] = $total_monthly; 
    $monthly_balance['work_income'] = $work_income;
    $monthly_balance['rights_income'] = $rights_income;
    $monthly_balance['capital_income'] = $capital_income;
    set_monthly_balance('total_monthly', $total_monthly);
    set_monthly_balance('work_income', $work_income);
    set_monthly_balance('rights_income', $rights_income);
    set_monthly_balance('capital_income', $capital_income);
    return sprintf("%d^%d^%d^%d^", $total_monthly, $work_income, $rights_income, $capital_income);
}
// rights_monthly, right_one_time, rights_other
function do_table_rights($t){
    global $monthly_balance;

    $rmoney = array('תשלום', 'מענק', 'החזר');
    $recurr = array('הנחה', 'הקלת מס');
    $freq = array('חודשי' => 1, 'דו חודשי' => 2 ,'רבעוני' => 4,'שנתי'=> 12);
    $total_monthly = 0; $right_one_time = 0; $rights_other = 0;
    
    if ($t == false || empty($t) || (count($t) == 1 && empty($t[0]['rights_sum']))) // empty table
        return "0^0^0^";
    
    foreach ($t as $line){
        // error_log("rights table=". print_r($line, true));
        if (!isset($line['rights_sum']) || empty($line['rights_sum'])) continue;

        if (in_array($line['rights_pay_type'], $rmoney) && isset($freq[$line['rights_frequency']]))
            $total_monthly += intval($line['rights_sum']);
        else if (in_array($line['rights_pay_type'], $recurr) /*&& isset($freq[$line['rights_frequency']]) DONT KNOW HOWTO CALCULATE!! */)
            $right_one_time += intval($line['rights_sum']);
        else $rights_other += intval($line['rights_sum']);
    }
    $monthly_balance['rights_monthly'] = $total_monthly; 
    $monthly_balance['right_one_time'] = $right_one_time;
    $monthly_balance['rights_other'] = $rights_other;
    set_monthly_balance('rights_monthly', $total_monthly);
    set_monthly_balance('right_one_time', $right_one_time);
    set_monthly_balance('rights_other', $rights_other);
    $s = sprintf("%d^%d^%d^", $total_monthly, $right_one_time, $rights_other);
    return $s;
}
// "expenses monthly, diur, communication, shotef,"
function do_table_expenses($t){
    global $monthly_balance;

    $comm = array('ניידים','אינטרנט','כבלים');
    $total_monthly = 0;
    $diur = 0;
    $communication = 0;
    $shotef = 0; 
    $other = 0;
    if ($t == false || empty($t) || (count($t) == 1 && empty($t[0]['expense_sum']))) // empty table
        return "0^0^0^0^0^";

    foreach ($t as $line){
        if (!isset($line['expense_sum']) || empty($line['expense_sum'])) continue;
        //error_log("expn=". print_r($line, true));
        $total_monthly += intval($line['expense_sum']) ;
        if ($line['expense_category'] == 'דיור')
            $diur += intval($line['expense_sum']);
        else if (in_array($line['expense_category'], $comm))
            $communication += intval($line['expense_sum']);
        else if ($line['expense_category'] == 'שוטפות')
            $shotef += intval($line['expense_sum']);
        else $other += intval($line['expense_sum']);
        // else - leave out
    }
    $monthly_balance['expenses_monthly'] = $total_monthly;
    $monthly_balance['diur'] = $diur;
    $monthly_balance['communication'] = $communication;
    $monthly_balance['shotef'] = $shotef;
    set_monthly_balance('expenses_monthly', $total_monthly);
    set_monthly_balance('diur', $diur);
    set_monthly_balance('communication', $communication);
    set_monthly_balance('shotef', $shotef);
    $s = sprintf("%d^%d^%d^%d^%d^", $total_monthly, $diur, $communication, $shotef, $other);
    return $s;
}

// 
function do_table_debts($t){
    global $monthly_balance;
    $total = 0;
    $total_return_monthly = 0;
     
    if ($t == false || empty($t) || (count($t) == 1 && empty($t[0]['debts_sum']))) // empty table
        return "0^0^";

    foreach ($t as $line){
        //error_log("expn=". print_r($line, true));
        $total += intval($line['family_debt_orig_sum']) ;
        $total_return_monthly += intval($line['family_debt_monthlyreturn']) ;
    }
    $monthly_balance['debts_sum'] = $total;
    $monthly_balance['debts_monthly'] = $total_return_monthly;
    set_monthly_balance('debts_sum', $total);
    set_monthly_balance('debts_monthly', $total_return_monthly);
    return sprintf("%d^%d^", $total, $total_return_monthly);
}

// "total mortgage, monthly mortgage return,"
function do_table_mortgage($t){
    global $monthly_balance;

    $total_monthly = 0;
    $total = 0;
    
    if ($t == false || empty($t) || (count($t) == 1 && empty($t[0]['mortgage_sum']))) // empty table
        return "0^0^";

    foreach ($t as $line){
        // error_log("expn=". print_r($line, true));
        $total += intval($line['mortgage_sum']);
        $total_monthly += intval($line['mortgage_monthly']) ;
    }
    $monthly_balance['mortgage_sum'] = $total;
    $monthly_balance['mortgage_monthly'] = $total_monthly;
    set_monthly_balance('mortgage_sum', $total);
    set_monthly_balance('mortgage_monthly', $total_monthly);
    $s = sprintf("%d^%d^", $total, $total_monthly);
    return $s;
}
//  $lstr .= "ns_total_cost^ns_yahav^ns_self^ns_revaha^ns_youth^ns_zedaka^ns_gov^ns_supp^"
function  do_table_ns_summary($t){
    $cl = array('needsolution_cost'=> 0, 'needsolution_yahav'=> 0, 'needsolution_self'=> 0, 'needsolution_revaha'=> 0, 'needsolution_youth'=> 0, 
                'needsolution_Zedaka'=> 0, 'needsolution_government'=> 0, 'needsolution_supplier_reduction'=> 0);
    $lstr = "";
    //error_log("calca ns table = ". print_r($t, true));
    foreach ($t as $line){
        foreach ($cl as $k => $v){
            //error_log("calca ns line[".$k."] = ". $line[$k]);
            if ( isset($line[$k]) && !empty($line[$k]))
                $cl[$k] +=  intval($line[$k]);
        }
    }
    //error_log("calca RESULT  ns table = ". print_r($cl, true));

    foreach ($cl as $k => $v)
        $lstr .= $cl[$k] . MBO_XL_DELIM; 

    return $lstr;
}
function init_monthly_balance($hdr_list){
    global $monthly_balance;
    $l = trim($hdr_list, MBO_XL_DELIM);

    $str_array = explode(MBO_XL_DELIM, $l);
    foreach ($str_array as $k){
        $monthly_balance[trim($k)] = 0; // 
    }
    return true;
}

function set_monthly_balance($fld, $value){
    global $monthly_balance;

    return $monthly_balance[$fld] = $value;
}

function do_balance($hdr_list){
    global $monthly_balance;

    $l = trim($hdr_list, MBO_XL_DELIM);

    $str_array = explode(MBO_XL_DELIM, $l);
    // error_log("do_balance monthly size=".count($str_array)." show me=".print_r($str_array, true).print_r($monthly_balance, true));
    $lstr = "";
    foreach ($str_array as $k){
        //if (!isset($monthly_balance[trim($k)]) || empty($monthly_balance[trim($k)])) continue;
        $lstr .= $monthly_balance[trim($k)] . MBO_XL_DELIM; // instead of $v
    }
    return $lstr;
}
/* $stable array(start, middle, end) of cpt id
       ( [id] => 0
        [name] => 0
        [date] => 31/3/2019 08:56:00
        [link] => 8934  // family
        [plink] => 8936 // woman
        [mlink] => 8935 // man
        )
*/
function GetExcelColumnName($columnNumber) {
    $columnName = '';
    if ($columnNumber < 26)
        return chr(ord('A') + $columnNumber);
    $columnNumber += 1;
    while ($columnNumber > 0) {
        $modulo = ($columnNumber-1) % 26 ;
        $columnName = chr(ord('A') + $modulo) . $columnName; // 'A' is 65
        $columnNumber = (int)(($columnNumber - $modulo) / 26);
    }
    return $columnName;
}
/*
function GetExcelColumnName($columnNumber) {
    $columnName = '';
    //if ($columnNumber < 26)
    //    return chr(ord('A') + $columnNumber);
    while ($columnNumber > 0) {
        $modulo = ($columnNumber - 1) % 26;
        $columnName = chr(ord('A') + $modulo) . $columnName; // 'A' is 65
        $columnNumber = (int)(($columnNumber - $modulo) / 26);
    }
    return $columnName;
}*/

// get woman & man - whoever exist - if not padd table. fixed positions!
// follow HDR - request, LINE/DATA request
// support phase snapshot data
// Called
function prents_tables($stable, $hdr, $colNum){
    $phdr = array('parent_info', 'parent_education', 'parent_hstatus', 'parent_employment', 'parent_notemployed' ); // forms
    $ptbl = array('health_issues', 'parent_diploma', 'parent_employ_boz', 'parent_employ_history'); // tables (must have MAX sizes too: will be 3)
    // only once - not in snapshot: 'questionnaire_pre', 'questionnaire_post'
    $lstr = "";
    $meta ="";
    error_log("prents_tables colNUm=".$colNum);
    if ($hdr){ // generate line of column headers
        $p_link = get_parent_snapshot_link($stable[0], 'mlink'); // need either man/woman fro headers
        if (!$p_link) // try papa
            $p_link = get_parent_snapshot_link($stable[0], 'plink'); // need either man/woman fro headers
        $p_tables = get_fields($p_link);
        // error_log("parents headers forms & woman phse 1 & 2=". print_r($p_tables, true));
        $lstr = get_parent_repeatable_hdr($p_tables, $phdr, $ptbl);
        $lstr .= $lstr; // X 2
        error_log("prents_tables colNUm=".$colNum); 
        set_parent_data_length(substr_count($lstr, MBO_XL_DELIM) + $colNum) ;  
        error_log("prents_tables  set_parent_data_length colNUm=".(substr_count($lstr, MBO_XL_DELIM)+ $colNum)); 

        return $lstr . $lstr; // X 4 - all required headers for parents woman followed by man for both phases
    }
    // else generate line of data
    for ($i = 0; $i < 3; $i+=2){ // get both phases for woman & man later
        $p_link = get_parent_snapshot_link($stable[$i], 'mlink');
        error_log("parents woman phase=".$i." link=".$p_link);
        if (!$p_link){ // no parent man or woman so pad it; maybe skip columns??
            for ($k=0; $k < get_parent_data_length(); $k++)
                $lstr .= ' ' . MBO_XL_DELIM;
        } else {
            $p_tables = get_fields($p_link); // parent link per phase
            $lstr .= get_parent_data($p_tables, $phdr, $ptbl, $i, $colNum+substr_count($lstr, MBO_XL_DELIM));
        }
        // add woman map
        $meta .= "<tr><td colspan='5'><h4>Woman's Data, Phase: ". $i . "</h4></td></tr>". get_parent_meta_data();

        $p_link = get_parent_snapshot_link($stable[$i], 'plink');
        error_log("parents man phase=".$i." link=". $p_link);

        if (!$p_link){ // no parent man or woman so pad it; maybe skip columns??
            for ($k=0; $k < get_parent_data_length(); $k++)
                $lstr .= ' ' . MBO_XL_DELIM;
        } else {
            $p_tables = get_fields($p_link); // parent link per phase
            $lstr .= get_parent_data($p_tables, $phdr, $ptbl, $i, $colNum+substr_count($lstr, MBO_XL_DELIM));
        }
        // add man map
        $meta .= "<tr><td colspan='5'><h4>Man's Data, Phase: ". $i . "</h4></td></tr>". get_parent_meta_data();
    }
    set_parent_full_meta_data($meta);
    return $lstr;
}
$parent_data_length = 0;
function set_parent_data_length($n){
    global $parent_data_length;
    $parent_data_length = $n;
    error_log("set_parent_data_length =". $parent_data_length);
    return $parent_data_length;
} 
function get_parent_data_length(){
    global $parent_data_length;
    return $parent_data_length;
} 
// handle forms & tables 
function get_parent_data($p_tables, $phdr, $ptbl, $i, $colNum){
    $s ="";
    $meta = "";
    
    foreach ($phdr as $formname){ // form type headers
        foreach ($p_tables[$formname] as $k => $v)
            if (is_array($v)){ //for debug purposes
                error_log("get_parent_data UNEXPECTED ARRAY in header". print_r($v, true));
            }
            else $s .= $v . MBO_XL_DELIM;
            $meta .= col_index($s, $i, $formname, $colNum);
    }
    foreach ($ptbl as $tabname) {// table type heades - repeated 3 times
        if (!isset($p_tables[$tabname])){
            error_log("get_parent_data line 635 table=".$tabname. "   undefined."); //  All tables=".print_r($p_tables, true));
            if ($tabname == "parent_diploma") // 6 empty fields times 3
                $s .= " ^ ^ ^ ^ ^ ^" . " ^ ^ ^ ^ ^ ^" . " ^ ^ ^ ^ ^ ^";
            if ($tabname == "health_issues") // 4 empty fields times 3
                $s .= " ^ ^ ^ ^" . " ^ ^ ^ ^" . " ^ ^ ^ ^";
        } else $s .= serialize_table3($p_tables[$tabname]);
        $meta .= col_index($s, $i, $tabname, $colNum);
    }
    set_parent_meta_data($meta); // for printing col locations
    return $s;
}
//set_parent_full_meta_data
function set_parent_full_meta_data($meta){
    global $parent_meta_full;
    return $parent_meta_full = $meta;
}
function get_parent_full_meta_data(){
    global $parent_meta_full;
    return $parent_meta_full;
}
function set_parent_meta_data($meta){
    global $parent_meta;
    return $parent_meta = $meta;
}
function get_parent_meta_data(){
    global $parent_meta;
    return $parent_meta;
}
function col_index($str, $i, $tname, $colNum){
    static $prev_ttmp = 0, $init = true;
    if ($init){
        $prev_ttmp = $colNum;
        $init = false;
    }
    $n = substr_count($str, MBO_XL_DELIM) + $colNum;
    error_log("col_index prev=".$prev_ttmp. "Phase =". $i . "  n=".$n. "  colNum=".$colNum);
    $meta = sprintf('<tr><td>%d</td><td>%s</td><td>%4d</td><td>%6d</td><td>%s</td></tr>', 
            $i, $tname, ($n - $prev_ttmp), $n, GetExcelColumnName($prev_ttmp));
    $prev_ttmp = $n;
    return $meta;
}
function serialize_table3($t){
    $lstr = "";
    foreach ($t as $line){
        foreach ($line as $k => $v){
            if ($k != 'needsolution_comment') // never holds - use as placeholder for similar cases
                $lstr .= $v . MBO_XL_DELIM;
            else $lstr .= (empty($v) ? " " : preg_replace('/[\x00-\x1F\x7F]/', ' ', $v)) . MBO_XL_DELIM;
        }
    }
    for ($i = count($t); $i < 3; $i++)
        for ($j = 0; $j < count($t[0]); $j++) // start at 1, to ignore id
            $lstr .= '0' . MBO_XL_DELIM;
    return $lstr;
}
function get_parent_repeatable_hdr($p_tables, $phdr, $ptbl){
    $s = "";
    foreach ($phdr as $formname){ // form type headers
        foreach ($p_tables[$formname] as $k => $v)
            if (is_array($k)){
                error_log("get_parent_repeatable_hdr UNEXPECTED ARRAY in header". print_r($v, true));
            }
            else $s .= $k . MBO_XL_DELIM;
    }
    foreach ($ptbl as $tabname) // table type heades - repeated 3 times
        $s .= serialize_table_hdr3($p_tables[$tabname]);
    return $s;
}
// $t === $stable[$i]
function get_parent_snapshot_link($t, $link){
    if (!isset($t) || !isset($t[$link]))
        return false;
    return $t[$link];
}