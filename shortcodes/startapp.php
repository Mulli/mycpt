<?php
/* shortcodes/startapp.php - iframe with latest index.php 
 * force browser to use new index.php if exists 
 */
//  [yahav-start]
//  [digmatec app="digmatec"]

//add_shortcode('yahav-start','yahav_start');
//function yahav_start($atts ) {
add_shortcode('digmatec','digmatec_start');
function digmatec_start($atts){
	$a = shortcode_atts( array(
		'app' => 'digmatec',
    ), $atts ); // not used
    $ver = filemtime( ABSPATH . $a['app'] . '/index.html');
    $url = site_url(); // get_site_url(); url=" . $url . "&ver="
//error_log('digmatec_start file='. ABSPATH . $a['app'] . '/index.html');

    $str = "<style>
        header,h1{ display:none;}p{margin: 0 auto;}
        body {margin:0; }</style>
        <iframe style='width: 100%; height: 100vh;border-width:0' src='/". $a['app'] . "/index.html?". $ver . "'></iframe>";

    return $str;
}