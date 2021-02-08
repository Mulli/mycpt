<?php
// Report referrals
    add_shortcode("report-intakes", 'mbo_report_intakes'); // show all meta data for cpt
    function mbo_report_intakes(){
        // if (($s = mbo_permissions()) != "ok") return $s;
        
        // get data from database
        $intakesCpt = get_posts( array('post_type' => 'digma_intakes', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($intakesCpt) < 1)
            return "No post of type= ". $type ." found";

        // initializate page display
        $str = mbo_init_page_display();
        
        // init once all family fields for histograms
        $intakeCpt_fieldGroups = array();
        foreach ( $intakesCpt as $cpt )
            $intakeCpt_fieldGroups[$cpt->ID] = get_fields($cpt->ID);

        // Display title  & Menu
        // Display title
        $str .= mbo_report_header('דו"ח אינטייק<br />נכון לתאריך:', count($intakesCpt));
        //$str .= '<h2 id="report-title" class="report-title" >דו"ח אינטייק נכון לתאריך: '.date("j/n/Y").'<br />'.'סה"כ משפחות: ' . count($intakesCpt) . "</h2>";
        $intake_topics = array("סטטוס אינטייק" => 'sec-intake_status', 
                        "מלווה תוכנית" => 'sec-intake_yahav_member', 
                        "החלטת מנהלת" => 'sec-intake_mgr_decision',
                        "החלטת משפחה" => 'sec-intake_family_decision',
                        "תאריך מעבר לאינטייק" => 'sec-intake_date',
                        "תאריך ביצוע אינטייק" => 'sec-intake_date' 
                        );

        $str .= mbo_report_menu($intake_topics);

        // Display list of all histogram graphs intake_status
        $str .= gen_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_status", 'intake_info', "סטטוס אינטייק",
                            "סטטוס באינטייק", "מספר משפחות");
        $str .= gen_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_yahav_member", 'intake_info', "מלווה תכנית",
                            "שם מלווה התכנית", "מספר משפחות");
        $str .= gen_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_mgr_decision", 'intake_info', "החלטת מנהלת",
                            "תמצית החלטת מנהלת", "מספר משפחות");
        $str .= gen_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_family_decision", 'intake_info', "החלטת משפחה",
                            "תמצית החלטת משפחה", "מספר משפחות");
        
        $intake_date = mbo_date_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_date", 'intake_info');
        $str .= draw_histogram("תאריך מעבר לאינטייק", $intake_date, "intake_date",
                            "תאריך מעבר לפי רבעונים", "מספר משפחות");

        $intake_qdate = mbo_date_histogram($intakesCpt, $intakeCpt_fieldGroups, "intakes", "intake_qdate", 'intake_info');
        $str .= draw_histogram("תאריך ביצוע אינטייק", $intake_qdate, "intake_qdate",
                            "תאריך ביצוע אינטייק לפי רבעונים", "מספר משפחות");
       
        $str .= "<div class='end-report'><h2>סוף הדו''ח</h2></div>";

        return $str;
        //return displayBar($family_referral_status);
    }
 
?>