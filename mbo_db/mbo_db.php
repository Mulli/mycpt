<?php
// All MBO db tables api
//  mbo_create_db($tablename) - create a table + definition dynamic definition TB
    function mbo_create_db($tablename) {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $tablename;

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            total_referrals mediumint(9) NOT NULL,
            total_intakes mediumint(9) NOT NULL,
            total_families mediumint(9) NOT NULL,
            total_families_inprogram mediumint(9) NOT NULL,
            total_families_bogrot mediumint(9) NOT NULL,
            total_families_completeOK mediumint(9) NOT NULL,

            UNIQUE KEY id (id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( "mbo_".$table_name."_version", "0.1" );
    }
?>