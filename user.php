<?php

//error_reporting(E_ALL);
//ini_set('display_errors', true);

//echo '<h1> Submit Job </h1>';

require_once "/var/www/cgi-bin/scripts/include.php";
require_once "./include.php";

echo "<title> $title </title> $image
        <p> $header_title $peoria_colors $common_css_style";

$out = `ps -ef | grep picam`;
$lines = preg_split ( '/\n/', $out );
$camera_error = "Camera in use, please try again later";

if ( count ( $lines ) > 3 ) die_sub ( $camera_error );

process_options();

upload_form();

////////////////////////

function upload_form() {

global $cookie_password, $default_table, $select_court, $select_time;

die_sub ( 'Feature disabled since cameras will now record whenever they detect someone is on the court.' );

recording_form( 'user' );

//take_picture_form();
take_picture_form_user();

} // end function

?>
