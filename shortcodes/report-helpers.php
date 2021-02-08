<?php
/* Mbo Report Helpers functions */
define ('MBO_NO_VALUE', 'שדה ריק');

function mbo_report_header($title, $cnt, $parents="משפחות"){
    date_default_timezone_set('Asia/Jerusalem');
    return '<h2 id="report-title" class="report-title" >'.$title.' '. 
            date("j/n/Y"). ',  שעה '.date("H:i").'<br />'.'סה"כ '. $parents . ": " . $cnt . "</h2>";
}
function mbo_permissions(){
    if( !current_user_can('editor') && !current_user_can('administrator') )
        return ("<h1 style='margin:50px auto'>Sorry, you are not allowed to view this page</h1>");
    return "ok";
}
function mbo_init_page_display(){
    date_default_timezone_set('UTC');

    $str = '<style>.entry-title{display:none}</style>';
    return $str;
}
// display the menu at the top of report
function mbo_report_menu($topics){
    $str = "<ol style='margin-right:30%'>" ;
    foreach ($topics as $k => $v)
        $str .= "<li><a class='report-menu-item' href='#".$v."'>".$k."</a></li>";
    $str .= "</ol>" ;
    $str .= "<div style='text-align:center;cursor:pointer'><button onClick='window.print()' >הדפסה או יצירת PDF ושמירה לדיסק</button></div>";
    return $str;
}
function gen_histogram($cptList, $allFieldGroups, $type, $field, $parentTable, $title, $xAxesTitle, $yAxesTitle){
    $histogram = mbo_optimized_histogram($cptList, $allFieldGroups, $type, $field, $parentTable);
    $str = draw_histogram($title, $histogram, $field, $xAxesTitle, $yAxesTitle);
    if ($histogram['avg'] > 0)
        $str .= mbo_avg_line($histogram);
    return $str;
}
function safeInc($v){ // safe incerement, initialize to 1 avoid undefined issues
    return isset($v) ? $v+1 : 1;
}
function sortbyQ($a, $b){
    $aparts = explode('/', $a);
    $bparts = explode('/', $b);
    if (count($aparts) != count($bparts))
        return count($aparts) < count($bparts);
    if ($aparts[1] != $bparts[1])
        return intval($aparts[1]) < intval($bparts[1]);
    return $aparts[0] < $bparts[0];
}
// Specific histogram for source finance of need solution array
function mbo_sumsubtable_src_histogram($postList, $allFields, $type, $keyField, $parentTable = null){ // $keyField not used
                $ks = array('needsolution_cost' => 'עלות כוללת', 
                'needsolution_yahav' => 'יהב',
                //'needsolution_off' =>  'לא מתוקצב',
                'needsolution_total' => 'מתוקצב',
                'needsolution_self' =>  'עצמי',
                'needsolution_revaha' => 'רווחה',
                'needsolution_youth' => 'צעירים',
                'needsolution_Zedaka' => 'קרן צדקה',
                'needsolution_government' => 'ממשלה',
                'needsolution_supplier_reduction' => 'הנחת ספק');
    //[needsolution_year_budget] => 2019
    // $histogram['needsolution_total'] = 20000;
    $histogram = array();
    foreach ($ks as $ksk => $ksv)
        $histogram[$ksv] = 0; // initialization

    $i = 0;
    foreach ( $postList as $cpt ){
        //$x = get_fields($cpt->ID);
        $x = $allFields[$cpt->ID];
        if (isset($parentTable)){
            $t = $x[$parentTable]; // the table
            foreach ($t as $ti){ // all lines add sources sums to histogram
                foreach ($ks as $ksk => $ksv)
                    $histogram[$ksv] += intval($ti[$ksk]); // field value in line of family table
            }
        } // else - ignored
        $i++;
    }
    // calc budget vs non budget
    /*$total_in_budget = 0;
    foreach ($ks as $ksk => $ksv){
        if ($ksk == 'needsolution_cost') continue;
        $total_in_budget += $histogram[$ksv];
    }
    $histogram['needsolution_off'] = $histogram['needsolution_cost'] - $total_in_budget;*/

    arsort($histogram, SORT_NUMERIC); 

    $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
    return $arg;
}
// Specific histogram of costs
function mbo_sumsubtable_histogram($postList, $allFields, $type, $keyField, $parentTable = null){ // $keyField not used
    $i = 0;
    $histogram = array();
    foreach ( $postList as $cpt ){
        //$x = get_fields($cpt->ID);
        $x = $allFields[$cpt->ID];
        if (isset($parentTable))
            $v= mbo_sumtable($x[$parentTable], $keyField); // Example: $x['intake_info']["family_referral_code"]
        else
            $v= 0; // Example: $x["family_referral_code"]
        //error_log("Got ". $v);
        //$rawsum[$i] = $v; // just keep the value
        $histogram[$v] = isset($histogram[$v]) ? $histogram[$v]+1 : 1;
        $i++;
    }
    arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

    $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
    return $arg;
}
// sum all values of $k in table $t
function mbo_sumtable($t, $k){
    $res = 0;
    foreach( $t as $ti)
        $res += intval($ti[$k]);
    return $res;
}
function mbo_date_histogram($postList, $allFields, $type, $keyField, $parentTable = null){ // $keyField not used
    $i = 0;
    $histogram = array();
    foreach ( $postList as $cpt ){
        //$x = get_fields($cpt->ID);
        $x = $allFields[$cpt->ID];
        if (isset($parentTable))
            $v= $x[$parentTable][$keyField]; // Example: $x['intake_info']["family_referral_code"]
        else
            $v= $x[$keyField]; // Example: $x["family_referral_code"]

        if (!isset($v) || empty($v)){
            $histogram[MBO_NO_VALUE] = isset($histogram[MBO_NO_VALUE]) ? $histogram[MBO_NO_VALUE] + 1 : 1;
        }else {
            $parts = explode('/', $v); // convert date to time
            // error_log(print_r($parts,true));
            if (count($parts) != 3)
                $histogram['לא תקין'] = isset($histogram['לא תקין']) ? $histogram['לא תקין'] + 1 : 1; // safeInc($histogram['לא תקין']);
            else {
                $qn = ceil($parts[1]/3); 
                if ($qn > 4) error_log("DATE report - program entry cpt ID=". $cpt->ID);
                if (intval($parts[2]) < 100 ) // force 4 digit year
                    $y = "20".$parts[2];
                else $y = $parts[2];
                $s = 'Q'.$qn.'/'.$y; // format Q1/2015
                $histogram[$s] = isset($histogram[$s]) ? $histogram[$s]+1 : 1;
            } 
        }
        $i++; 
    }
    //arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC
    uksort($histogram, 'sortbyQ');
    $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
    //error_log($type. " historgram, total=". $i);
    ////error_log(print_r( $histogram, true ));
    //return "Result generated"; // $json['body']['data'];
    return $arg;
}
function mbo_optimized_histogram($postList, $allFields, $type, $keyField, $parentTable = null){
    $i = 0;
    $val_arr = array();
    $histogram = array();
    foreach ( $postList as $cpt ){
        $x = $allFields[$cpt->ID];

        if (isset($parentTable)){
            if (isset($x[$parentTable][$keyField])) // Example: $x['intake_info']["family_referral_code"]
                $v= $x[$parentTable][$keyField] == "" ? MBO_NO_VALUE : $x[$parentTable][$keyField]; 
            else {
                error_log("Field not set: CPT id=".$cpt->ID. " parentable=". $parentTable . " keyField=". $keyField);
                $v = MBO_NO_VALUE;
            }
        }else
            $v= $x[$keyField]; // Example: $x["family_referral_code"]
            //if (!isset($x["family_referral_code"])) continue; // not counting usaved referrals
        // use status as index 
        if (is_array($v)){
            $v = "לא תקין";
            error_log("Family cptId=".$cpt->ID. "   Parenttable=". $parentTable. "  key=".$keyField);
        } // else error_log("mbo_optimized_histogram v=".$v) ;

        if (!isset($histogram[$v])) 
            $histogram[$v] = 1;
        else $histogram[$v]++;
        $val_arr[$i] = intval($v);
        $i++; 
    }
    arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

    asort($val_arr, SORT_NUMERIC); //, SORT_NUMERIC
    $average = number_format(array_sum($val_arr) / count($val_arr), 2);

    $midval = floor(count($val_arr)/2);
    if (count($val_arr) % 2) // odd
        $median = $val_arr[$midval];
    else $median = ($val_arr[$midval] + $val_arr[$midval+1]) / 2;

    $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram, 
            'avg' => $average, 'median' => $median, 'stddev' => number_format(mbo_stand_deviation($val_arr), 2), 'N' => count($val_arr));
    //$arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
    // error_log(print_r( $histogram, true ));
    return $arg;
}

function drawCanvas($type, $title, $labels, $data, $canvasId, $xAxesTitle = "", $yAxesTitle = ""){
    //error_log(json_encode($labels));
    //$mdata = array( $data, $data );
    $jLabels = json_encode($labels);
    if ($yAxesTitle == "") $yAxesTitle = "ערכי ציר Y";
    if ($xAxesTitle == "") $xAxesTitle = "ערכי ציר X";

    $jData = json_encode($data); //$data = [12, 19, 3, 5, 2, 3];
    $str = '<div class="mbo-draw"><canvas id="'.$canvasId.'" width="400" height="400"></canvas></div>';
    $jsscript = "
    <script>        
    jQuery(function() {            
     
        var ctx = document.getElementById('$canvasId');
        var myChart = new Chart(ctx, {
            type: '$type',
            data: {
                labels: $jLabels.map(x => Number.isInteger(x)? x : mboc_formatLabel(x, 10)),
                datasets: [{
                    label: '',
                    data: $jData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.98)',
                        'rgba(54, 162, 235, 0.98)',
                        'rgba(255, 206, 86, 0.98)',
                        '#469990',
                        'rgba(153, 102, 255, 0.98)',
                        'rgba(255, 159, 64, 0.98)',
                        '#9A6324',
                        '#808000',
                        '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b',
                        '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                        '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        '#469990',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        '#9A6324',
                        '#808000',
                        '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b', 
                        '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                        '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                    ],
                    borderWidth: 2,
                    datalabels:{
                        align:'end',
                        anchor: 'end',
                        offset: 2,
                        textAlign: 'center',
                        color: 'black',
                        fontSize: 18
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                /*showInlineValues : true,
                centeredInllineValues : true,*/
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '$yAxesTitle',
                            fontColor: '#444',
                            fontWeight: '600',
                            fontFamily: 'Assistant',
                            fontSize: 14
                        }
                    }],
                    xAxes: [{
                        maxBarThickness: 60,
                        scaleLabel: {
                            display: true,
                            labelString: '$xAxesTitle',
                            fontColor: '#444',
                            fontWeight: '600',
                            fontFamily: 'Assistant',
                            fontSize: 14
                        }
                    }]
                },
                
                title: {
                    display: true,
                    text: '$title',
                    fontSize: 18,
                    fontFamily: 'Assistant',
                    fontStyle: '600',
                    fontColor: '#222',
                    position: 'top',
                    padding: 40
                }
            }
        })
    });
    </script>";
    return $str. $jsscript;
}

function mbo_1or2_parent_histogram($postList, $allFields, $type, $keyField){ // $keyField not used
    $i = 0;
    $histogram = array('זוג הורים' => 0, 'חד הורית' => 0, 'לא מוגדר' => 0);
    foreach ( $postList as $cpt ){
        //$x = get_fields($cpt->ID);
        $x = $allFields[$cpt->ID];
        if (!isset($x["family_referral_woman_firstname"]) && !isset($x["family_referral_man_firstname"]))
            $histogram['לא מוגדר'] += 1; 
        else if (isset($x["family_referral_woman_firstname"]) && strlen($x["family_referral_woman_firstname"])>1
                && isset($x["family_referral_man_firstname"]) && strlen($x["family_referral_man_firstname"])>1)
            $histogram['זוג הורים'] += 1;
        else $histogram['חד הורית'] += 1;
        
        $i++; 
    }
    arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

    $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
    //error_log(print_r( $histogram, true ));
    return $arg;
}

function drawSroiCanvas($type, $title, $labels, $data, $canvasId, $xAxesTitle = "", $yAxesTitle = ""){
       
    $jLabels = json_encode($labels);
    if ($yAxesTitle == "") $yAxesTitle = "ערכי ציר Y";
    if ($xAxesTitle == "") $xAxesTitle = "ערכי ציר X";

    $jData = json_encode($data); //$data = [12, 19, 3, 5, 2, 3];
    $str = '<div class="mbo-draw"><canvas id=Sroi-"'.$canvasId.'" width="400" height="400"></canvas></div>';
    $jsscript = "
    <script>        
    jQuery(function() {            
     
        var ctx = document.getElementById('$canvasId');
        var myChart = new Chart(ctx, {
            type: '$type',
            data: {
                labels: $jLabels.map(x => Number.isInteger(x)? x : mboc_formatLabel(x, 10)),
                datasets: [{
                    label: '',
                    data: $jData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.98)',
                        'rgba(54, 162, 235, 0.98)',
                        'rgba(255, 206, 86, 0.98)',
                        '#469990',
                        'rgba(153, 102, 255, 0.98)',
                        'rgba(255, 159, 64, 0.98)',
                        '#9A6324',
                        '#808000',
                        '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b',
                        '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                        '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        '#469990',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        '#9A6324',
                        '#808000',
                        '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b', 
                        '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                        '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                    ],
                    borderWidth: 2,
                    datalabels:{
                        align:'end',
                        anchor: 'end',
                        offset: 2,
                        textAlign: 'center',
                        color: 'black',
                        fontSize: 18
                    },
                    {
                        label: '',
                        data: $jData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.98)',
                            'rgba(54, 162, 235, 0.98)',
                            'rgba(255, 206, 86, 0.98)',
                            '#469990',
                            'rgba(153, 102, 255, 0.98)',
                            'rgba(255, 159, 64, 0.98)',
                            '#9A6324',
                            '#808000',
                            '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b',
                            '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                            '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            '#469990',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            '#9A6324',
                            '#808000',
                            '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#e6194b', 
                            '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', 
                            '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
                        ],
                        borderWidth: 2,
                        datalabels:{
                            align:'end',
                            anchor: 'end',
                            offset: 2,
                            textAlign: 'center',
                            color: 'black',
                            fontSize: 18
                        }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                /*showInlineValues : true,
                centeredInllineValues : true,* /
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '$yAxesTitle',
                            fontColor: '#444',
                            fontWeight: '600',
                            fontFamily: 'Assistant',
                            fontSize: 14
                        }
                    }],
                    xAxes: [{
                        maxBarThickness: 60,
                        scaleLabel: {
                            display: true,
                            labelString: '$xAxesTitle',
                            fontColor: '#444',
                            fontWeight: '600',
                            fontFamily: 'Assistant',
                            fontSize: 14
                        }
                    }]
                },
                
                title: {
                    display: true,
                    text: '$title',
                    fontSize: 18,
                    fontFamily: 'Assistant',
                    fontStyle: '600',
                    fontColor: '#222',
                    position: 'top',
                    padding: 40
                }
            }
        })
    });
    </script>";
    return $str. $jsscript;
    
}
    // mbo_new_histogram, 
    // $postList - cpt post list of type
    //  $type - referrals, intakes, etc...
    // $keyFiled - to calc histogram
    /* NOT IN USE
    function mbo_new_histogram($postList, $type, $keyField){
        $i = 0;
        $histogram = array();
        foreach ( $postList as $cpt ){
            $x = get_fields($cpt->ID);
            //if (!isset($x["family_referral_code"])) continue; // not counting usaved referrals
            $v= $x[$keyField]; // the status as index
            if (!isset($histogram[$v])) 
                $histogram[$v] = 1;
            else $histogram[$v]++;
            $i++; 
        }
        arsort($histogram, SORT_NUMERIC); //, SORT_NUMERIC

        $arg = array ('type' => $type, 'total' => $i, 'table' => $histogram	);
        //error_log($type. " historgram, total=". $i);
        //error_log(print_r( $histogram, true ));
        //return "Result generated"; // $json['body']['data'];
        return $arg;
    }*/
/* wp-charts plugin (uses chart js) - turned off by Mulli
function displayBar($arg){ //wp_charts_shortcode
        // return do_shortcode('[wp_charts title="סטטוס הפניות" type="bar" margin="5px 20px"  
        return do_shortcode('[wp_charts title="סטטוס הפניות" type="bar" margin="5px 20px" width="20%" 
                fillopacity="0.98" 
                data="97,20,16,11,6,5,4,3" 
                color="#1dc7ea,#ffa534,#fe0000,#f08080,#ff03ff,#027e02"
                labels="x1,x2,x3,x4x5,x6,x7,x8"]');
               //labels="עברה לאינטייק,סיום אין התאמה,סיום,בירור עם משפחה,בירור עם גורם מפנה, בתקלה פני"]');
               //datasets="97,next 20,next 16,next 11,next 6,next 5,next 4,next 3" 

    }
*/
// bros helprs
function mbo_delta_date1year($date1, $date2){
    if (empty($date1) || empty($date2))
        return false;

    error_log('mbo_delta_date1year date1='. $date1 . '  date2='. $date2);
    //$d1 = mbo_strtotime($date1);
    //$d2 = mbo_strtotime($date2);
    $d = str_replace('/', '-', $date1); $d= str_replace('.', '-', $date1);
    $d1 = strtotime($d);
    $d = str_replace('/', '-', $date2); $d= str_replace('.', '-', $date2);
    $d2 = strtotime($d);
    
    if ($d1 <= 0 || $d2 <= 0)
        return false;
    $delta_year = strtotime('1-1-2019') - strtotime('1-1-2018')  ;
    $res = ($d1 + $delta_year) < $d2 ? 'true' : 'false';
    // error_log('mbo_delta_date1year d1='. $d1 . '  d2='. $d2 . '  delta_year='. $delta_year . ' return ' . $res);
    return $d1 + $delta_year < $d2;
}
// return date in array that can be compared
// NOT IN USE
function mbo_strtotime($v){
    if (!isset($v) || empty($v))
        return 0 ; // no value
 
    $parts = explode('/', $v); // convert date to time
    // error_log(print_r($parts,true));
    if (count($parts) != 3)
        return 0;
   
    if (intval($parts[2]) < 100 ) // force 4 digit year 20 18 not just 18
        $parts[2] = "20".$parts[2];
    $s = $parts[0] . '-' . $parts[1] . '-' . $parts[2]; 
    return strtotime($s); // dd/mm/yyyy in a single number
}

// Collect families with 3 snapshots (begin & end)
// with status בוגרת  or מסיימת לפני הזמן בהסכמה with at lease 1 year in program
function gen_ddata_array(){
    $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
    if (count($programCpt) < 1)
        return -1; // return "No post of type= ". $type ." found";

    // init once all family fields for histograms
    $programCpt_fieldGroups = array();
    // index is main cpt relevant for all families upto 1st snapshot
    $ddata = array(); 
    
    foreach ( $programCpt as $cpt ){
        $snaptable = get_field('snapshots', $cpt->ID);
        if ($snaptable == false || count($snaptable) < 3)
            continue;
        // @TODO - take only בוגרות ומסיימות בהסכמה
        // more than 1 snapsot - put first as 'f0'
        $family = get_fields($snaptable[0]['link']); // family
        if (!isset($family['program_info']) || !isset($family['program_info']['program_status'])){
            error_log('Cant find snapshot of cpt id= '. $cpt->ID);
            continue;
        }
        // allow on families that complete the program as expected
        if ($family['program_info']['program_status'] != 'בוגרת'){
            if ($family['program_info']['program_status'] != 'מסיימת לפני הזמן בהסכמה')
                continue;
            if (!mbo_delta_date1year($family['program_info']['program_date'],
                                        $family['program_info']['program_end_date']))
                continue; // less than 1 year
            error_log("CHECK DATE OVER 1 YEAR= ". print_r($family['program_info'], true));
        }

        $ddata[$cpt->ID]['snapnum'] = count($snaptable); // only now allowin table

        $mlink = empty($snaptable[0]['mlink']) ? 0 : get_fields($snaptable[0]['mlink']);
        $plink = empty($snaptable[0]['plink']) ? 0 : get_fields($snaptable[0]['plink']);
        $ddata[$cpt->ID]['f0'] = array('family' => $family, 'single' => $mlink == 0 || $plink == 0 ? 1 : 2,
                            'mlink' => $mlink, 'plink' => $plink);
        $family = get_fields($snaptable[2]['link']); // family
        $mlink = empty($snaptable[2]['mlink']) ? 0 : get_fields($snaptable[2]['mlink']);
        $plink = empty($snaptable[2]['plink']) ? 0 : get_fields($snaptable[2]['plink']);
        $ddata[$cpt->ID]['f2'] = array('family' => $family ,'single' => $mlink == 0 || $plink == 0 ? 1 : 2,
                            'mlink' => $mlink, 'plink' => $plink);
    }

    error_log("COUNT DDATA = ". count($ddata));
    return $ddata;
}

?>