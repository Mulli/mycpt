<?php
// Report program
    add_shortcode("acf-cleanup", 'mbo_acf_cleanup'); // show all meta data for cpt
    function mbo_acf_cleanup(){
        if (!isset($_GET['op']))
		    return "file name missing,<br />usage: acf-cleanup?op=[meeting|psnapshot]<br />";
	
        $op = $_GET['op'];
        
        if ($op == 'meeting')
            return cleanup_meeting_summary_in_f_snapshot();
        if ($op == 'psnapshot')
            return fix_cat_parent_snapshot();
        if ($op == 'test')
            return test_parent_snapshot();
        return 'Unexpected op request = '. $op;
    }
    function test_parent_snapshot(){
        $programCpt = get_posts( array('post_type' => 'digma_parents', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= psnapshot found";
        $str = 'Got ' . count($programCpt) . ' posts';
        foreach ($programCpt as $cpt){
            $c = get_the_category($cpt->ID);
            $s ="";
            foreach ($c as $ci)
                $s .= $ci->name . ", ";
            $str .= sprintf('<li>PID = %d  title=%s categories=[%s]</li>', $cpt->ID, $cpt->post_title, $s);
        }
        return $str;
    }
    // remove general (1) and set psnapshot
    function fix_cat_parent_snapshot(){
        $str = "";
        $links = array('mlink', 'plink');
        $cat_ids = array(intval(29));

        $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= digma_families found";
        foreach ( $programCpt as $cpt ){
            $snaptable = get_field('snapshots', $cpt->ID);
            if ($snaptable == false || count($snaptable) == 0) 
                continue;
            //error_log(print_r($snaptable, true));
            
            for ($i=0; $i < count($snaptable); $i++){
                foreach ($links as $alink){
                    $pl = $snaptable[$i][$alink];
                    if (!isset($pl) || empty($pl)) continue;
                    $ca = get_the_category( $pl );
                    // error_log(print_r($ca, true));
                    $term_taxonomy_ids = wp_set_object_terms( $pl, $cat_ids, 'category' );

                    if ( is_wp_error( $term_taxonomy_ids ) )
                        $str .= '<li> FAIL Phase = '.$i.'  PID = '.$pl. ' CAT = '. $ca[0]->slug . '  total=' . count( $ca ). '</li>';
                    else {// Success! The post's categories were set.
                        $ca = get_the_category( $pl );
                        $str .= '<li> Phase = '.$i.'  PID = '.$pl. ' CAT = '. $ca[0]->slug . '  total=' . count( $ca ). '</li>';
                    }
                }   
            }
        }
        return '<ol>'. $str . '</ol>';
    }

    function cleanup_meeting_summary_in_f_snapshot(){
        $programCpt = get_posts( array('post_type' => 'digma_families', 'posts_per_page' => -1, 'status' => 'publish') );
        if (count($programCpt) < 1)
            return "No post of type= digma_families found";

        foreach ( $programCpt as $cpt ){
            $snaptable = get_field('snapshots', $cpt->ID);
            if ($snaptable == false || count($snaptable) == 0) 
                continue;
            //error_log(print_r($snaptable, true));
            
            for ($i=0; $i < 3; $i++){
                if (!isset($snaptable[$i]['link'])) continue;
                $d = get_field('family_meeting_summary', $snaptable[$i]['link']);
                if ($d){
                    $b = delete_field('family_meeting_summary', $snaptable[$i]['link']);
                    if ($b) error_log('YES Deleted from snap='. $i . ' in '. $cpt->ID);
                    else {
                        error_log('NOT Deleted from snap='. $i . ' in '. $cpt->ID);
                        error_log(print_r($d, true));
                    }
                } else error_log('FAIL to get snap='. $i . ' in '. $cpt->ID);
            }
        }
        return "Complete deletion";
    } 

?>
