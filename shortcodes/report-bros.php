<?php
define('MIN_SALARY', 5300); // get from national social site
if (!defined('MBO_DEBUG'))
    define('MBO_DEBUG', false); // get from national social site
// Report program
    add_shortcode("report-bros", 'mbo_report_bros'); // show all meta data for cpt
    function mbo_report_bros(){
        // if (($s = mbo_permissions()) != "ok") return $s;
        //error_log("hello");
        // get data from database
        $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= ". $type ." found";

        // init once all family fields for histograms
        $programCpt_fieldGroups = array();

        // index is main cpt relevant for all families upto 1st snapshot
        $ddata = array( /*'snapnum' => 0, 'single' => 0,
                        'f0' => array( 'family' => 0, 'mlink' => 0, 'plink' => 0),
                        'f1' => array( 'family' => 0, 'mlink' => 0, 'plink' => 0),
                        'f2' => array( 'family' => 0, 'mlink' => 0, 'plink' => 0)*/); 
        $post_list = array(array(array()), array(array()), array(array()), array(array())); // 0 - pre snapshot, 1, 2, 3 - snapshots
        //$woman0 = array(); $woman2 = array(); $man0 = array(); $man2 = array();
        foreach ( $programCpt as $cpt ){
            $snaptable = get_field('snapshots', $cpt->ID);

            if ($snaptable == false || count($snaptable) == 0){
                $ddata[$cpt->ID]['snapnum'] = 0; // 
                $x = get_field('family_members', $cpt->ID); // get family members
                $ddata[$cpt->ID]['single'] = count($x); // single parent of a couple
                $mlink = $plink = 0; 
                foreach ($x as $member){
                    if ($member['family_member_role'] == "mother"){
                        $mlink = get_fields($member['family_member_cptid']);
                    } else if ($member['family_member_role'] == "father"){
                        $plink = get_fields($member['family_member_cptid']);
                    }
                }
                $ddata[$cpt->ID]['f0'] = array('family' => get_fields($cpt->ID), 'single' => count($x),
                                        'mlink' => $mlink, 'plink' => $plink);
            } else { // more than 1 snapsot - put first as 'f0'
                $ddata[$cpt->ID]['snapnum'] = count($snaptable); // 
                $family = get_fields($snaptable[0]['link']); // family
                $mlink = empty($snaptable[0]['mlink']) ? 0 : get_fields($snaptable[0]['mlink']);
                $plink = empty($snaptable[0]['plink']) ? 0 : get_fields($snaptable[0]['plink']);
                $ddata[$cpt->ID]['f0'] = array('family' => $family, 'single' => $mlink == 0 || $plink == 0 ? 1 : 2,
                                    'mlink' => $mlink, 'plink' => $plink);
            }
            
        }
        error_log('total number programCpt='. count($programCpt));
        $intro_rep = mbo_intro_text(count($ddata));
        $pwork = parent_work($ddata);
        $psal = parent_min_salary($ddata);
        $in_poverty = '<h2>ב.	% משפחות מתחת לקו העוני</h2>'.'<p>בדו"ח הבא</p>';

        $first_part = '<div style="margin-right:130px">'. 
                    $intro_rep.
                    $pwork. 
                    $psal . 
                    $in_poverty .
                    employ_status($ddata, 'mlink', 'נשים').
                    employ_status($ddata, 'plink', 'גברים').
                    insocial_service($ddata).
                    education_status($ddata, 'mlink', 'נשים').
                    education_status($ddata, 'plink', 'גברים').
                    '</div>'.'<h2>סוף הדו"ח</h2>';
        return $first_part;
        //error_log('total number ddata='. count($ddata));
        //error_log(print_r($ddata, true));
        //parent_work($ddata);
        //return drawBrosCanvas('כותרת מתחלפת');
if(0){
        
        // initializate page display
        $str = mbo_init_page_display();
        
        // Display title
        $str .= mbo_report_header('דו"ח משפחות בוגרות ומסיימות בהסכמה לאחר שנה+<BR />נכון לתאריך:', count($programCpt_fieldGroups));

        //$str .= '<h2 id="report-title" class="report-title" >דו"ח משפחות בתכנית נכון לתאריך: '.date("j/n/Y").'<br />'.'סה"כ משפחות: ' . 
        //            count($programCpt) . "</h2>";
        // Display sub title 
        $str .= '<p class="report-title">(נכללות משפחות בוגרות ומסיימות בהסכמה לאחר שנה+)</p>';

        // Display  Menu
        $program_topics = array("סטטוס משפחות" => 'sec-program_status',
                                "תאריך כניסה לתכנית" => 'sec-program_date',
                                "מלווה יהב" => 'sec-intake_yahav_member', // yes. its 'intake_yahav_member'
                                "סטטוס זוגיות" => 'sec-mertial_status',
                                "התפלגות גילאי הנשים" => 'sec-family_referral_age_woman',
                                "התפלגות גילאי הגברים" => 'sec-family_referral_age_man',
                                "מספר הילדים במשפחה" => 'sec-kids_number', 
                                "לקוחות הרווחה" => 'sec-family_in_revaha', 
                                "השתתפות בתכניות אחרות" => 'sec-other_programs', 
                                "התפלגות השתתפות בתכניות אחרות" => 'sec-other_programs', 
                                "צורך ביעוץ משפטי" => 'sec-need_legal', 
                                "התפלגות סוג הדיור" => 'sec-family_house', 
                                "זקוקים לשיפור במצב המגורים" => 'sec-family_house', 
                                "התפלגות עלויות צרכים ומענים למשפחה" => 'sec-needsolution_cost', 
                                "התפלגות מקורות מימון צרכים ומענים למשפחה" => 'sec-needsolution_src', 
                            );

                            $str .= mbo_report_menu($program_topics);
        $programCpt = $post_list; // !!!!!!!! OVERRIDE THE WHOLE LIST WITH THEFILTEREDD ON !!!!!!!!!!!!!!!!!
        //??foreach ( $programCpt as $cpt )
            //??$programCpt_fieldGroups[$cpt->ID] = get_fields($cpt->ID);
        // Display list of all histogram graphs program_status
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "program_status", 'program_info', "סטטוס משפחות",
        "סטטוס בתכנית ולאחריה", "מספר משפחות");

        $program_date = mbo_date_histogram($programCpt, $programCpt_fieldGroups, "families", "program_date", 'program_info');
        $str .= draw_histogram("תאריך כניסה לתכנית", $program_date, "program_date", "תאריך כניסה לפי רבעונים", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "intake_yahav_member", 'program_info', "מלווה יהב",
        "שם מלווה התכנית", "מספר משפחות");
       
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "mertial_status", 'program_info', "סטטוס זוגיות",
        "סטטוס זוגיות", "מספר משפחות");

        // prepare all fields
        $allFields = array();
        foreach ($post_list as $p){
            $rfrl_id = $programCpt_fieldGroups[$p->ID]['program_info']['referral_code'];
            $allFields[$p->ID] = get_fields($rfrl_id);
        }
        $family_referral_age_woman = mbo_age_histogram($post_list, $allFields, "referrals", "family_referral_age_woman");
        $str .= draw_histogram("התפלגות גילאי הנשים", $family_referral_age_woman, "family_referral_age_woman",
                                "טווחי גיל בקבוצות של 5 שנים", "מספר הנשים"); 
        $str .= mbo_avg_line($family_referral_age_woman);

        $family_referral_age_man = mbo_age_histogram($post_list, $allFields, "referrals", "family_referral_age_man");
        $str .= draw_histogram("התפלגות גילאי הגברים", $family_referral_age_man, "family_referral_age_man",
                    "טווחי גיל בקבוצות של 5 שנים", "מספר הגברים");
        $str .= mbo_avg_line($family_referral_age_man);

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "kids_number", 'program_info', "מספר הילדים במשפחה",
        "מספר הילדים", "מספר משפחות");

        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_in_revaha", 'program_info', "לקוחות הרווחה",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "other_programs", 'program_info', "השתתפות בתכניות אחרות",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "other_programs_list", 'program_info', "התפלגות השתתפות בתכניות אחרות",
        "שמות תכניות אחרות", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "need_legal", 'family_legal', "צורך ביעוץ משפטי",
        "תשובות כן/לא", "מספר משפחות");
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_house", 'family_housing', "התפלגות סוג הדיור",
        "שמות סוגי הדיור", "מספר משפחות");
        
        $str .= gen_histogram($programCpt, $programCpt_fieldGroups, "families", "family_house_status", 'family_housing', "זקוקים לשיפור במצב המגורים",
        "תשובות כן/לא", "מספר משפחות");
        
        $program_ns_sum = mbo_sumsubtable_histogram($programCpt, $programCpt_fieldGroups, "families", 'needsolution_cost', "needsolution_array");
        $str .= draw_histogram("התפלגות עלויות צרכים ומענים למשפחה", $program_ns_sum, "needsolution_cost",
        "סכומי עלויות למשפחה", "מספר משפחות לפי גובה העלות");

        $program_ns_src = mbo_sumsubtable_src_histogram($programCpt, $programCpt_fieldGroups, "families", 
                                    null /* key filed ignored*/, "needsolution_array");
        $str .= draw_histogram("התפלגות מקורות מימון צרכים ומענים בכל השנים", $program_ns_src, "needsolution_src",
        "מקורות מימון", "גובה מימון מצטבר");

        $str .= "<div class='end-report'><h2>סוף הדו''ח</h2></div>";

        return $first_part . $str;
        //return displayBar($family_referral_status);
    }
}

function insocial_service($ddata){
    $hist = array();
    $total = 0;
    //calc for women, than for men
    foreach ($ddata as $dd){
        $d = $dd['f0']['family']['program_info'];
        $hist[$d['family_in_revaha']] = isset($hist[$d['family_in_revaha']]) ? $hist[$d['family_in_revaha']]+1 : 1 ;
    }
    $t = 'התפלגות משפחות המטופלות ברווחה בתחילת התוכנית';
    $str1 = mbo_html_table($t, $hist, count($ddata));
    $comments = '<ul><li>אין מספיק נתונים להתאמה על רקע עוני</li></ul>';
    return $str1 . $comments;
}

function education_status($ddata, $item, $gender){
    $hist_status = array(); // histogram education status
    $hist_years = array();
    $total = 0;
    //calc for women, than for men
    foreach ($ddata as $dd){
        $d = $dd['f0'];
        //$item = 'mlink';
        if ($d[$item]){
            $total += 1;
            $p = $d[$item]['parent_education'];
            $hist_status[$p['academic_education']] = isset($hist_status[$p['academic_education']]) ? 
                                                                    $hist_status[$p['academic_education']]+1 : 1;
            $hist_years[$p['year_of_study']] = isset($hist_years[$p['year_of_study']]) ? 
                                                                    $hist_years[$p['year_of_study']]+1 : 1;
        }
    }
    $t = 'התפלגות השכלה  '. $gender . ' בתחילת התוכנית';
    $str1 = mbo_html_table($t, $hist_status, $total);
    $t = 'התפלגות שנות לימוד '. $gender . ' בתחילת התוכנית ';
    $str2 = mbo_html_table($t, $hist_years, $total);
    return $str1 . $str2;
}
function employ_status($ddata, $item, $gender){
    $hist_status = array(); $hist_res = array();
    $hist_res['missing'] = $hist_res['below'] = $hist_res['above'] = 0;
    $total = 0;
    //calc for women, than for men
    foreach ($ddata as $dd){
        $d = $dd['f0'];
        //$item = 'mlink';
        if ($d[$item]){
            $total += 1;
            $p = $d[$item]['parent_employment'];
            if (!isset($p)) error_log("daddy is the problem!");
            if (!isset($hist_status[$p['employ_status']])){
                $hist_status[$p['employ_status']] = 0;
                // error_log('RESET status for '. $p['employ_status']);
            } 
            $hist_status[$p['employ_status']] += 1;
            
            if (empty($p['employ_bruto']) && empty($p['employ_neto']))
                $hist_res['missing'] += 1;
            else if (intval($p['employ_bruto']) > MIN_SALARY || intval($p['employ_neto']) > MIN_SALARY)
                $hist_res['above'] += 1;
            else $hist_res['below'] += 1;
        }
    }
    $t = 'התפלגות סטטוס תעסוקה  '. $gender . ' בתחילת התוכנית';
    $str1 = mbo_html_table($t, $hist_status, $total);
    $t = 'התפלגות '. $gender . ' ביחס לשכר מינימום ('.MIN_SALARY.' ש"ח) בתחילת התוכנית ';
    $str2 = mbo_html_table($t, $hist_res, $total);
    return $str1 . $str2;
}
function mbo_plus1($item){
    return (isset($item) ? $item + 1 : 1);
}
// min sal  MIN_SALARY
function parent_min_salary($ddata){
    $hist_status = array();
    $hist_res['missing'] = $hist_res['above'] = $hist_res['below'] = 0;
    $total = 0;

    foreach ($ddata as $dd){
        $d = $dd['f0'];
        $item = 'mlink';
        if ($d[$item]){
            $total += 1;
            $p = $d[$item]['parent_employment'];
            if (!isset($hist_status[$p['employ_status']])){
                $hist_status[$p['employ_status']] = 1;
            } else $hist_status[$p['employ_status']] += 1;
            if (empty($p['employ_bruto']) && empty($p['employ_neto']))
                $hist_res['missing'] +=1 ;
            else if (intval($p['employ_bruto']) > MIN_SALARY || intval($p['employ_neto']) > MIN_SALARY)
                $hist_res['above'] += 1;
            else $hist_res['below'] += 1;
        }
        $item = 'plink';
        if ($d[$item]){
            $total += 1;
            $p = $d[$item]['parent_employment'];
            if (!isset($hist_status[$p['employ_status']])){
                $hist_status[$p['employ_status']] = 1;
            } else $hist_status[$p['employ_status']] += 1;
            if (empty($p['employ_bruto']) && empty($p['employ_neto']))
                $hist_res['missing'] +=1 ;
            else if (intval($p['employ_bruto']) >= MIN_SALARY || intval($p['employ_neto']) >= MIN_SALARY)
                $hist_res['above'] += 1;
            else $hist_res['below'] += 1;
        }
    }
    $str1 = mbo_html_table('התפלגות סטטוס תעסוקה בתחילת התוכנית', $hist_status, $total);
    
    $str2 = mbo_html_table('התפלגות המשתכרים שכר מינימום חודשי ('.MIN_SALARY.'ש"ח) בתחילת התוכנית', $hist_res, $total);
    $l = '<a href="https://www.btl.gov.il/Mediniyut/GeneralData/Pages/%D7%A9%D7%9B%D7%A8%20%D7%9E%D7%99%D7%A0%D7%99%D7%9E%D7%95%D7%9D.aspx"
    target="_blank">נתוני בטל"א</a>';
    $str2 .= '<li>שכר מינימום חודשי: '.MIN_SALARY.' ש"ח נכון 1.1.2019 לפי '. $l . '</li>';
    //error_log('hist_status = '. print_r($hist_status, true));
    //error_log('hist_res = '. print_r($hist_res, true));
    return $str1. $str2;
}
function mbo_html_table($title, $table, $total){
    $str = '<h2>'.$title.'</h2>';
    $str .= '<table><tbody>';
    foreach ($table as $k => $v){
        $kv =  empty($k) ? 'שדה ריק' : $k ;
        $str .= '<tr><td>'. $kv .'</td><td>'.$v.'</td><td>'.mbo_p($v, $total) .'</td></tr>';
    }
    $str .= '<tr><td>סה"כ</td><td>'.$total.'</td><td>'.mbo_p($total, $total) .'</td></tr>';
    $str .= '<tbody></table>';
    return $str;
}
//        if ($d['plink'])
//employ_cap_percent
// employ_bruto
// employ_neto

// % working parents total & single vs. couples
function parent_work($ddata){
    // how many families
    // how many single or couples families
    $total_families = count($ddata);
    $total_single = 0;
    $total_couple = 0;
    $res1 = array(); // results for single -0 & couples = 1
    $res2 = array(); // results for single -0 & couples = 1
    $res1['empty'] = $res1['unemp'] = $res1['emp']=0;
    $res2['empty'] = $res2['unemp'] = $res2['emp']=0;

    foreach ($ddata as $dd){
        $d = $dd['f0'];
        if ($d['single'] == 1){
            $total_single += 1;
            $em = $d['mlink'] ? $d['mlink']['parent_employment']['employ_status'] : $d['plink']['parent_employment']['employ_status'];
            if (mbo_if_unemployed($em)){
                $res1['unemp'] += 1;
            } else if (empty($em)){ // missing info
                $res1['empty'] += 1;
                if (MBO_DEBUG){
                  if ($d['mlink'])
                    error_log("EMPTY SINGLE Woman employment look for unemployed = ". print_r($d['mlink']['parent_notemployed'],true));
                  else
                    error_log("EMPTY SINGLE Man employment look for unemployed = ". print_r($d['plink']['parent_notemployed'],true));
                }
            } else $res1['emp'] += 1;
        } else {
            $total_couple += 1;
            $em = $d['mlink']['parent_employment']['employ_status'];
            $ep = $d['plink']['parent_employment']['employ_status'];
            if (mbo_if_unemployed($em) || mbo_if_unemployed($ep))
                $res2['unemp'] += 1;
            else if (empty($em) && empty($ep)){ // missing info
                $res2['empty'] += 1;
                if (MBO_DEBUG){
                     error_log("EMPTY Woman employment look for unemployed = ". print_r($d['mlink']['parent_notemployed'],true));
                     error_log("EMPTY Man employment look for unemployed = ". print_r($d['plink']['parent_notemployed'],true));
                }
            } else $res2['emp'] += 1;
        }
    }
    //error_log('total single = '. $total_single . 'hist' . print_r($res1, true));
    //error_log('total couples = '. $total_couple . 'hist' . print_r($res2, true));
    $str = '<h2>בכניסה לתכנית: % המשפחות בהן שני בני הזוג עובדים + חד הוריות עובדות</h2>';
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
    $str .= '</ul>';
    return $str;
}
// predicate 
function mbo_if_unemployed($item){
    return ( strpos($item, 'מובטל') !== false || strpos($item, 'לא עובד') !== false || strpos($item, 'עקר/ת בית') !== false);
}
// string: number format with %
function mbo_p($x, $y){
    return number_format($x/$y*100, 1).'%';
}

function drawBrosCanvas($title){
    $str = '<div class="mbo-draw"><canvas id="'.'drawBrosCanvas'.'" width="400" height="400"></canvas></div>';
    $jsscript = "
    <script>        
    jQuery(function() {            
        var densityCanvas = document.getElementById('drawBrosCanvas');

        Chart.defaults.global.defaultFontFamily = 'Assistant';
        Chart.defaults.global.defaultFontSize = 14;
        Chart.defaults.global.defaultFontStyle = 'bold';
        Chart.defaults.global.legend.position = 'bottom';


        var densityData = {
        label: 'בכניסה לתכנית',
        data: [5427, 5243, 5514, 3933, 1326, 687, 1271, 1638],
        backgroundColor: '#1dc7ea',
        borderWidth: 0,
        yAxisID: 'y-axis-snapshot0'
        };

        var gravityData = {
        label: 'בסיום התכנית',
        data: [3.7, 8.9, 9.8, 3.7, 23.1, 9.0, 8.7, 11.0],
        backgroundColor: '#ffa534',
        borderWidth: 0,
        yAxisID: 'y-axis-snapshot2'
        };

        var g3Data = {
        label: 'עוד סדרה',
        data: [60, 29, 8, 60, 23, 9, 47, 50],
        backgroundColor: 'red',
        borderWidth: 0,
        yAxisID: 'y-axis-snapshot1'
        };

        var planetData = {
        labels: ['משפטי', 'דיור', 'אבטלה', 'אבטלה-ג', 'אבטלה-נ', 'שכר מעבודה', 'שכר-גברים', 'שכר נשים'],
        datasets: [densityData, gravityData, g3Data]
        };

        var chartOptions = {
        scales: {
            xAxes: [{
            barPercentage: 1,
            categoryPercentage: 0.6
            }],
            yAxes: [{
            id: 'y-axis-snapshot0'
            }, {
            id: 'y-axis-snapshot2'
            }, {
            id: 'y-axis-snapshot1'
            }]
        },
        title: {
                display: true,
                text: '$title',
                fontSize: 32,
                fontFamily: 'Assistant',
                fontStyle: '600',
                fontColor: '#222',
                position: 'top',
                padding: 40
        },
        animation: {
                duration: 1,
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = dataset.data[index]; 
                            var data2 = dataset.data[index]+'%';                                                
                            ctx.fillText(data, bar._model.x, bar._model.y - 5);
                            ctx.fillText(data2, bar._model.x, bar._model.y + 25);
                        });
                    });
                }
            }
        };

        var barChart = new Chart(densityCanvas, {
        type: 'bar',
        data: planetData,
        options: chartOptions
        });
        
    });
    </script>";
    return $str. $jsscript;
}

function mbo_intro_text($family_number){
    $str = '<h2> מתייחס ל'.$family_number.' משפחות אחרי אינטייק עד תמונת מצב ראשונה  </h2>';
    $str .= '<p> כל המשפחות שיש להן תמונת מצב התחלה וגם המשפחות שטרם הגיעו לשלב זה והן אחרי אינטייק  </p>';
    $str .= '<h2>הגדרות ומינוחים</h2>';
    $str .= '<ul>';
    $str .= '<li>בכניסה לתכנית: נתונים מתוך תמונת מצב 0 ואם אין עדיין, מתבצע חיפוש במצב נתונים נוכחי</li>';
    $str .= '<li>שדה ריק: ערך חסר. שדה שצריך להיות בו ערך אבל עדיין לא הוזן</li>';
    $str .= '<li>חד הוריות: כאשר יש הורה יחיד רק הוא נלקח בחשבון והשני אינו קיים, לא נכלל בחישובים ולא גורם לשדה ריק </li>';
    $str .= '</ul>';
    return $str;
}
?>