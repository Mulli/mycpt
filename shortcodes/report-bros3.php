<?php

// Report bros3 section
add_shortcode("report-bros3", 'mbo_report_bros3'); // show all meta data for cpt
function mbo_report_bros3(){
    // if (($s = mbo_permissions()) != "ok") return $s;
    //error_log("hello");
    // get data from database
    $ddata = gen_ddata_array(); 
    if ($ddata < 0)
        return "No posts of found";

    $general_stats = create_general_stats($ddata);
    //error_log('total number programCpt='. count($ddata));
    $str0 = '<h2>סה"כ משפחות: ' . $general_stats['total-families'] ;
    $str0 .= " בתוכן, גברים: " . ($general_stats['single-man'] + $general_stats['couple-man']) ;
    $str0 .= '  נשים: ' . ($general_stats['single-woman'] + $general_stats['couple-woman']) . "</h2>";
    $str0 .= '<h2>'.'סה"כ משפחות חד הוריות: ' . $general_stats['total-single'] . 
                " בתוכן, גברים: ". $general_stats['single-man'] . " נשים: " . $general_stats['single-woman'] .  "</h2>";
    $str0 .= '<h2>'.'  סה"כ משפחות שני הורים: ' . $general_stats['total-couple'] . 
                " בתוכן, גברים: ". $general_stats['couple-man'] . " נשים: " . $general_stats['couple-woman'] .  "</h2>";

    $str0 .= "<hr />";

    $str1 = parent_unemp_to_work($ddata);

    $str2 = gen_course_stats($ddata);
    
    $str3 = text_salary_inc($ddata);

    $str4 = text_family_products($ddata, $general_stats['total-families']);

    $str5 = text_family_rights($ddata);

    $strLast = '<h3>סוף הדו"ח '. sDate() . " </h3>";
    return '<div style="margin-right:50px">'. $str0 . $str1 . $str2 . $str3 . $str4 . $str5 . $strLast . '</div>';
}
function text_family_rights($ddata){
    $res = calc_family_rights($ddata);
    // $res = array('total' => $cnt, 'start_lt' => $strat_lt, 'start_lt_per' => $start_lt_per, 'empty_start' => $empty_start,
    //'end_lt' => $end_lt, 'end_lt_per' => $end_lt_per, 'empty_end' => $empty_end );
    $s = "<h2>מיצוי זכויות - ". $res['total']." משפחות</h2>";
    $s .= "<h3> באיזו מידה משיגים זכויות < 4 " . " בהתחלה:". $res['start_lt'] . "  ". $res['start_lt_per'] . "% תשובות ריקות ".$res['empty_start']. "</h3>";
    $s .= "<h3> באיזו מידה משיגים זכויות < 4 " . " בסיום:". $res['end_lt'] . "  ". $res['end_lt_per'] . "% תשובות ריקות ". $res['empty_end']. "</h3>";
    $s .= "<h3> מעוניינים בסיוע " . " בהתחלה:". $res['start_lt_rs'] . "  ". $res['start_lt_rs_per'] . "% תשובות ריקות ".$res['empty_start_rs']. "</h3>";
    $s .= "<h3> מעוניינים בסיוע " . " בסיום:". $res['end_lt_rs'] . "  ". $res['end_lt_rs_per'] . "% תשובות ריקות ".$res['empty_end_rs']. "</h3>";

    return $s;
}
function calc_family_rights($ddata){
    $start_lt = 0;  $empty_start = 0;  $empty_end = 0;
    $end_lt = 0;    $cnt = 0;

    $empty_start_rs = 0; $empty_end_rs = 0;
    $start_lt_rs  = 0; $end_lt_rs = 0;

    foreach ($ddata as $dd){
        $p_start = $dd['f0']['family']['rights_q'];
        $p_end = $dd['f2']['family']['rights_q'];
        // rights extent
        if (empty($p_start['rights_extent']))
            $empty_start += 1;
        else $start_lt += intval($p_start['rights_extent']) < 4 ? 1 : 0;
        if (empty($p_end['rights_extent']))
            $empty_end += 1;
        else $end_lt += intval($p_end['rights_extent']) < 4 ? 1 : 0;
        $cnt += 1;
        // rights_assistance
        if (empty($p_start['rights_assistance']))
            $empty_start_rs += 1;
        else $start_lt_rs += $p_start['rights_assistance'] == 'כן' ? 1 : 0;
        if (empty($p_end['rights_assistance']))
            $empty_end_rs += 1;
        else $end_lt_rs += $p_end['rights_assistance'] == 'כן' ? 1 : 0;
    }
    $start_lt_per = round($start_lt/$cnt*100);
    $end_lt_per = round($end_lt/$cnt*100);
    
    $start_lt_rs_per = round($start_lt_rs/$cnt*100);
    $end_lt_rs_per = round($end_lt_rs/$cnt*100);
    $res = array('total' => $cnt, 'start_lt' => $start_lt, 'start_lt_per' => $start_lt_per, 'empty_start' => $empty_start,
                'end_lt' => $end_lt, 'end_lt_per' => $end_lt_per, 'empty_end' => $empty_end,
                'start_lt_rs' => $start_lt_rs, 'start_lt_rs_per' => $start_lt_rs_per, 'empty_start_rs' => $empty_start_rs,
                'end_lt_rs' => $end_lt_rs, 'end_lt_rs_per' => $end_lt_rs_per, 'empty_end_rs' => $empty_end_rs );
    if (MBO_DEBUG) error_log('calc_family_rights' . print_r($res, true));
    return $res;
}
function get_stats_line($table){
    $t = "";
    $dict = array('total' => 'N', 'avg' => 'ממוצע', 'variance' => 'שונות', 'std_dev' => 'סטיית תקן');
    foreach ($table as $k => $v)
        $t .= '<td>' . $dict[$k] . '</td><td>' . round($v, 2) . '</td>';
    $t = '<table><tbody><tr>'. $t . '</tr></tbody></table>';
    return $t;
}
function text_family_products($ddata, $total){
    $res = family_products($ddata);
    $c_total = $res['total_count'];
    $per = round($res['gotProduct'] / $total * 100);
    $ser = round($res['gotService'] / $total * 100);
    $us = round($res['under-start'] / $total * 100);
    $ue = round($res['under-end'] / $total * 100);
    $s = "<h2>". 'סה"כ שאלון מוצרים ושירותים ' . $c_total . '  מתוך ' . $total . ' משפחות'. "</h2>";
    $s .= "<h2> משפחות השלימו לפחות מוצר אחד: ".$res['gotProduct']. " ". $per . "%</h2>";
    $t = get_stats_line($res['stats']['prod-start']);
    $s .= "<h3> בתחילת התוכנית" .$t. "</h3>";
    $t = get_stats_line($res['stats']['prod-end']);
    $s .= "<h3> בסיום התוכנית" .$t. "</h3>";
    $s .= "<h2> משפחות ויתרו פחות שירותים: ".$res['gotService']. " ". $ser . "%</h2>";
    $s .= "<h2> משפחות ויתרו על 3 שירותים או פחות. בהתחלה: ".$res['under-start']. " ". $us . "%";
    $s .= "  בסיום: "  . $res['under-end']. " ". $ue . "%</h2>";

    $t = get_stats_line($res['stats']['service-start']);
    $s .= "<h3> בתחילת התוכנית" .$t. "</h3>";
    $t = get_stats_line($res['stats']['service-end']);
    $s .= "<h3> בסיום התוכנית" .$t. "</h3>";

    //'avg_start_cnt' => $avg_start_cnt, 'avg_start_below' => $avg_start_below,
    //        'avg_end_cnt' => $avg_end_cnt, 'avg_end_below' => $avg_end_below
    $s .= "<h2>נגישות לשרותים ואנשים ". $c_total . "  שאלונים</h2>";
    $s .= "<h3>קל מאוד = 1 ... קשה מאוד = 5</h3>";
    $avg1 = round($res['avg_start_below'] / $res['avg_start_cnt'] * 100);
    $s .= "<h2> בהתחלה <= 3.5: ". $res['avg_start_below'] . " מתוך  " . $res['avg_start_cnt'] . "  תשובות. " . $avg1 . "% ***</h2>";
    $avg2 = round($res['avg_end_below'] / $res['avg_end_cnt'] * 100);
    $s .= "<h2> בסיום <= 3.5: ". $res['avg_end_below'] . " מתוך  " . $res['avg_end_cnt'] . "  תשובות. " . $avg2 . "% ***</h2>";
//error_log("ssssssss", print_r($res['stats']['access-start'], true));
    $t = get_stats_line($res['stats']['access-start']);
    $x = "<h3> בתחילת התוכנית" .$t. "</h3>";
    $t = get_stats_line($res['stats']['access-end']);
    $x .= "<h3> בסיום התוכנית" .$t. "</h3>";
    //error_log("XXXXXXXX=". $x);
    return $s . $x;
}

function family_products($ddata){
    $qlist100 = array('q101', 'q102', 'q103', 'q104', 'q105', 'q106', 'q107', 'q108', 'q109');
    $qlist200 = array('q201', 'q202', 'q203', 'q204', 'q205', 'q206', 'q207', 'q208', 'q209',
                        'q210', 'q211', 'q212', 'q213','q214', 'q215');
    $list30 = array('q31', 'q32', 'q33');
    $qlist400 = array('q401', 'q402', 'q403', 'q404', 'q405', 'q406', 'q407', 'q408', 'q409',
                        'q410', 'q411', 'q412', 'q413');
    $total_count = 0;
    $gotProduct = 0;
    $gotService = 0;
    $funder_start = 0;
    $funder_end = 0;
    $avg_start_cnt = 0;
    $avg_start_below = 0;
    $avg_end_cnt = 0;
    $avg_end_below = 0;

    $prod_start_arr = array();
    $prod_end_arr = array();
    $service_start_arr = array();
    $service_end_arr = array();
    $access_start_arr = array();
    $access_end_arr = array();

    foreach ($ddata as $dd){
        $p_start = $dd['f0']['family']['family_products_services'];
        if (!isset($dd['f2']['family']['family_products_services_end'])){
            //error_log("family_products_services_end UNDEFINED=". print_r($dd['f2']['family'], true));
            continue;
        }

        $p_end = $dd['f2']['family']['family_products_services_end'];

        if (emptyquestionnaire($p_start) || emptyquestionnaire($p_end))
            continue;

        $total_count += 1;
        // products
        $cnt_start = questionnaire_count($p_start, $qlist200 );
        $cnt_end = questionnaire_count($p_end, $qlist200 );
        array_push($prod_start_arr, $cnt_start);
        array_push($prod_end_arr, $cnt_end);

        if ($cnt_end < $cnt_start) // family got at least 1 product
            $gotProduct += 1;
        // services
        $service_start = questionnaire_count($p_start, $qlist100 );
        $service_end = questionnaire_count($p_end, $qlist100 );
        array_push($service_start_arr, $service_start);
        array_push($service_end_arr, $service_end);

        if ($service_end < $service_start) // family got at least 1 product
            $gotService += 1;
        // Count above 3.5
        $funder_start += $service_start < 4 ? 1 : 0;
        $funder_end += $service_end < 4 ? 1 : 0;
        // access
        $avg_start = questionnaire_access_avg($p_start, $qlist400 ); // array($cnt, $avg);
        $avg_end = questionnaire_access_avg($p_end, $qlist400 ); // array($cnt, $avg);
        if ($avg_start[1] < 0 || $avg_end[1] < 0) // cannot compare empty
            continue;

        if ($avg_start[1] > 0){
            $avg_start_cnt +=1;
            $avg_start_below += $avg_start[1] <= 3.5 ? 1 : 0;
            array_push($access_start_arr, $avg_start[1]);
        }
        if ($avg_end[1] > 0){
            $avg_end_cnt +=1;
            $avg_end_below += $avg_end[1] <= 3.5 ? 1 : 0;
            array_push($access_end_arr, $avg_end[1]);
        }
    }
    $a = array( 'prod-start' => get_arr_stats($prod_start_arr),
                'prod-end' => get_arr_stats($prod_end_arr),
                'service-start' => get_arr_stats($service_start_arr),
                'service-end' => get_arr_stats($service_end_arr),
                'access-start' => get_arr_stats($access_start_arr),
                'access-end' => get_arr_stats($access_end_arr));

    $res = array('gotProduct' => $gotProduct, 'gotService' => $gotService,
                'under-start' =>  $funder_start, 'under-end' =>  $funder_end,
                'avg_start_cnt' => $avg_start_cnt, 'avg_start_below' => $avg_start_below,
                'avg_end_cnt' => $avg_end_cnt, 'avg_end_below' => $avg_end_below,
                'total_count' => $total_count, 'stats' => $a
            );
    
    if (MBO_DEBUG)  error_log ("family_products RES=" . print_r($res, true) );
    
    
    return ($res);
}
function get_arr_stats($arr){
    $cnt = count($arr);
    if ($cnt < 2) return false;
    $avg = round(array_sum($arr)/$cnt, 2);
    $std_dev = mbo_std_deviation($arr, $avg);
    $var = round(mbo_variance($arr), 2);
    return (array('total'=> $cnt, 'avg' => $avg, 'std_dev' => $std_dev, 'variance' => $var));
}
function mbo_variance($arr) { 
    $n = count($arr); 
    $variance = 0.0; 
    $average = array_sum($arr)/$n; 
    foreach($arr as $i) { // sum of squares of differences between all numbers and means. 
        $variance += pow(($i - $average), 2); 
    } 
    return (float)sqrt($variance/$n); 
} 
function mbo_std_deviation($arr, $avg){
    $ans=0;
    // error_log('stddev'. print_r($arr, true));
    foreach($arr as $i){
        $d = $i-$avg;
        $ans += $d*$d; // poer of 2
    }
    $arr_size=count($arr);
    return sqrt($ans/$arr_size);
}
function emptyquestionnaire($q){
    $empty = 0;
    foreach ($q as $k => $v)
        $empty += ($v == "" || $v == 'נא לבחור') ? 0 : 1;
    return $empty == 0;
}
function questionnaire_access_avg($table, $flist ){
    $cnt = 0;
    $total = 0;
    $dict = array('נא לבחור' => 0, '' => 0, 'קל מאוד' => 1, 'קל' => 2, 'די קל' => 3, 'קשה' => 4, 'קשה מאוד' => 5);
    foreach ($flist as $f){
        //error_log ("questionnaire_access_avg f=" . $f . "  cnt= ". $cnt . "  total= ". $total);
        if ($dict[$table[$f]] > 0){
            $cnt += 1;
            $total += $dict[$table[$f]];
        }
    }
    $avg = $cnt > 0 ? $total/$cnt : -1;
    //error_log ("questionnaire_access_avg avg=" . $avg . "  cnt= ". $cnt );
    return array($cnt, $avg);
}

function questionnaire_count($table, $flist ){
    $cnt = 0;
    foreach ($flist as $f){
        if ($table[$f] == 'כן')
            $cnt += 1;
    }
    return $cnt;
}
function text_salary_inc($ddata){
    $hist = salary_inc($ddata);
    $s = "<h2>שדרוג תעסוקתי</h2>";
    foreach ($hist as $k => $v)
        $s .= "<h3>הגדלת שכר ". $k . " : ". $v ."</h3>";
    return $s;
}
function salary_inc($ddata){
    $hist = array();
    foreach ($ddata as $dd){
        $f_intable = $dd['f2']['family']['family_income'];
        //$gender = $iswoman ? 'האשה' : 'הגבר';
        foreach ($f_intable as $line){ // scan income table at phase 2
            if ($line['income_type'] == 'משכורת'){ // compare
                $f0 = lookupSalaryF0($dd['f0']['family']['family_income'], $line['income_goesto'] );
                if (intval($line['income_sum']) > intval($f0)){
                    if (isset($hist[$line['income_goesto']])) $hist[$line['income_goesto']] += 1;
                    else $hist[$line['income_goesto']] = 1; // initialize
                }
            }   
        }
    }
    return $hist;
}

// lookup salary at phase 0
function lookupSalaryF0($table, $gender){
    foreach ($table as $t){
        if ($t['income_type'] == 'משכורת' && $t['income_goesto'] == $gender ) // compare
            return $t['income_sum'];
    }
    return 0;
}
function gen_course_stats($ddata){
    $cres = course_stats($ddata);
    $hist = $cres[0];
    $str2 = "";
    foreach ($hist as $k => $v){
        $str2 .= '<tr><td>'. $k . '</td><td>' . $v . '</td></tr>';
    }
    $i = 1;
    foreach ($cres[1] as $v){
        $str2 .= '<tr><td>'. ' אחר #' . $i++ . '</td><td>' . $v . '</td></tr>';
    }
    $str2 = "<h2>קורסים והכשרות</h2>" . "<table><tbody" . $str2 . "</tbody></table>";
    return $str2;
}
function course_stats($ddata){
    $hist = array();
    $other = array();
    $i = 0;
    foreach ($ddata as $dd){
        if (MBO_DEBUG) error_log("FAMILY =" . print_r($dd['f2']['family'], true));
        $d= $dd['f2']['family'];
        // needsolution_array
        foreach ($d['needsolution_array'] as $needitem){
            if ($needitem['needsolution_need'] == 'קורס/הכשרה'){
                if (isset($hist[$needitem['needsolution_solution']]))
                    $hist[$needitem['needsolution_solution']] += 1;
                else $hist[$needitem['needsolution_solution']] = 1;
                if ($needitem['needsolution_solution'] == 'אחר'){
                    $n = "**חסרה הגדרה משפחה: ".$d['program_info']['intake_family_name'] . " " . 
                          $d['program_info']['intake_family_woman_name'] . " " . $d['program_info']['intake_family_man_name'] ;
                    $other[$i++] = $needitem['needsolution_comment'] != "" ? $needitem['needsolution_comment'] : $n;
                }        
            }
        }
    }
    return array($hist, $other);
}
function create_general_stats($ddata){
    // total man, women
    // total single, double parent
    $total_single = 0;
    $total_couple = 0;
    // total man, woman IN single double families
    $total_1man = 0;
    $total_1woman = 0;
    $total_2man = 0;
    $total_2woman = 0;
    foreach ($ddata as $dd){
        if (!isset($dd['f0'])){
            if (MBO_DEBUG) error_log('ddata f0 undefined!!! program info'. print_r($dd, true));
            continue;
        } 
        $d = $dd['f0'];
        if ($d['single'] == 1){
            $total_single += 1;
            $iswoman = $d['mlink'] != 0;
            if ($iswoman) $total_1woman +=1;
            else $total_1man +=1;
        } else {
            $total_couple += 1;
            $total_2man +=1;
            $total_2woman +=1;
        }
    }
    $res = array('total-single'=> $total_single, 'total-couple' => $total_couple,
                'single-woman' => $total_1woman,'single-man' => $total_1man,
                'couple-woman' => $total_2woman,'couple-man' => $total_2man,
                'total-families' => $total_single + $total_couple
            );
    return ($res);
}
function mbo_intro_bros3(){
    $str = '<h2>הגדרות ומינוחים</h2>';
    $str .= '<ul>';
    $str .= '<li>בכניסה לתכנית: נתונים מתוך תמונת מצב 0 ואם אין עדיין, מתבצע חיפוש במצב נתונים נוכחי</li>';
    $str .= '<li>שדה ריק: ערך חסר. שדה שצריך להיות בו ערך אבל עדיין לא הוזן</li>';
    $str .= '<li>חד הוריות: כאשר יש הורה יחיד רק הוא נלקח בחשבון והשני אינו קיים, לא נכלל בחישובים ולא גורם לשדה ריק </li>';
    $str .= '</ul>';
    return $str;
}

// check if unemployed changes to employed
// look at paremt status & family income table
function mbo_unemp2work($em, $iswoman, $dd) {
    //error_log("mbo_unemp2work dd f2=". print_r($dd['f2'], true));

    $f_intable = $dd['f2']['family']['family_income'];
    $em2 = $iswoman ? $dd['f2']['mlink']['parent_employment']['employ_status'] 
                                : $dd['f2']['plink']['parent_employment']['employ_status'] ;
    if (mbo_if_unemployed($em2)){
        if (MBO_DEBUG){
            $who = $iswoman ? 'אשה' : 'גבר';
            error_log("unemployed em2=". $who . '  status ' . print_r($em2, true));
            error_log("INCOME=".  print_r($f_intable, true));
        }
        if (lookup_salary($f_intable, $iswoman)) // maybe parent not updated
            return true;
        return false;
    }
    return true; // still unemployed

        // check income table in case parent status is not updated
        //check_income_table($dd['f2']);
        //error_log('check_income_table'. print_r($dd['f2']['family']['family_income'], true));
        //error_log('check_income_table'. print_r($dd['f2']['family']['program_info'], true));
}
function lookup_salary($f_intable, $iswoman){
    $gender = $iswoman ? 'האשה' : 'הגבר';
    foreach ($f_intable as $line){
        if ($line['income_type'] == 'משכורת' && $line['income_goesto'] == $gender)
            return true;
    }
    return false;
}
// % working parents total & single vs. couples
function parent_unemp_to_work($ddata){
    // how many families
    // how many single or couples families
    $total_families = count($ddata);
    $total_single = 0;
    $total_couple = 0;
    $res1 = array(); // results for single -0 & couples = 1
    $res2 = array(); // results for single -0 & couples = 1
    $res1['empty'] = $res1['unemp'] = $res1['emp']=0;
    $res2['empty'] = $res2['unemp'] = $res2['emp']=0;
    //$unemp_to_work = array(); // start working
    $single_u2w = array(0,0); // woman, man, 
    $couple_u2w = array(0,0); // woman, man, 

    foreach ($ddata as $dd){
        if (!isset($dd['f0'])){
            if (MBO_DEBUG) error_log('ddata f0 undefined!!! program info'. print_r($dd, true));
            continue;
        } 
        $d = $dd['f0'];
        if ($d['single'] == 1){
            $total_single += 1;
            $iswoman = $d['mlink'] != 0;
            $em = $iswoman ? $d['mlink']['parent_employment']['employ_status'] : $d['plink']['parent_employment']['employ_status'];
            if (mbo_if_unemployed($em) && mbo_unemp2work($em, $iswoman, $dd)){
                if ($iswoman)
                    $single_u2w[0] += 1;
                else $single_u2w[1] += 1;
            }
        } else {
            $total_couple += 1;
            $em = $d['mlink']['parent_employment']['employ_status'];
            $ep = $d['plink']['parent_employment']['employ_status'];
            
            // was unemp now gets monthly salary
            if (mbo_if_unemployed($em) && mbo_unemp2work($em, true, $dd))
                $couple_u2w[0] += 1;
            if (mbo_if_unemployed($ep) && mbo_unemp2work($ep, false, $dd))
                $couple_u2w[1] += 1;
 
        }
    }
    if (MBO_DEBUG){
        error_log('single u2w'. print_r($single_u2w, true));
        error_log('couples u2w'. print_r($couple_u2w, true));
    }
    //error_log('total single = '. $total_single . 'hist' . print_r($res1, true));
    //error_log('total couples = '. $total_couple . 'hist' . print_r($res2, true));
   /* $str = '<h2>בכניסה לתכנית: % המשפחות בהן שני בני הזוג עובדים + חד הוריות עובדות</h2>';
    $str .= '<table><tbody>';
    $str .= '<tr><td>סטטוס</td><td>סה"כ</td><td>לא מובטל</td><td>מובטל</td><td>שדה ריק</td></tr>';
    $str .= '<tr><td>חד הוריות</td><td>'.$total_single.'</td><td>'.$res1['emp'].'</td><td>'.$res1['unemp'].'</td><td>'.$res1['empty'].'</td></tr>';
    $str .= '<tr><td></td><td>'.'100%'.'</td><td>'.mbo_p($res1['emp'],$total_single).'</td><td>'.mbo_p($res1['unemp'],$total_single).'</td><td>'.mbo_p($res1['empty'],$total_single).'</td></tr>';
    $str .= '<tr><td>שני בני זוג</td><td>'.$total_couple.'</td><td>'.$res2['emp'].'</td><td>'.$res2['unemp'].'</td><td>'.$res2['empty'].'</td></tr>';
    $str .= '<tr><td></td><td>'.'100%'.'</td><td>'.mbo_p($res2['emp'],$total_couple).'</td><td>'.mbo_p($res2['unemp'],$total_couple).'</td><td>'.mbo_p($res2['empty'],$total_couple).'</td></tr>';
    $str .= '</tbody></table>';
    $str .= '<ul>';
    $str .= '<li>מובטל, אם חד-הורי/ת מובטל/ת או לפחות אחד מזוג ההורים מובטל</li>';
    $str .= '<li>מובטל, אם בעיסוק מופיעות המילים: "מובטל" או "לא עובד" או "עקר/ת בית"</li>';
    $str .= '<li>בזוגות: שדה ריק = אין מידע, אם לשני בני הזוג שדות ריקים</li>';
    $str .= '</ul>';*/
    $str = '<h2> חד הוריות: מאבטלה -> לעבודה, נשים=' . $single_u2w[0] . '   גברים ='. $single_u2w[1] . "</h2>";
    $str .= '<h2> זוג הורים: מאבטלה -> לעבודה, נשים=' . $couple_u2w[0] . '   גברים ='. $couple_u2w[1] . "</h2>";
    return $str;
}
function check_f2($em, $woman){
    // error_log('check_f2 woman= '.  $woman . print_r($em, true));
    return 0;
}
function sDate(){
    date_default_timezone_set('Asia/Jerusalem');

    $today = getdate();
    $fname = sprintf("%4d-%02d-%02d-%02d%02d%02d", $today['year'], $today['mon'], $today['mday'], 
                                $today['hours'], $today['minutes'], $today['seconds']);
    return $fname;
}

?>
