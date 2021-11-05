<?php

require ( './.config');

$www = '/var/www/html';
$hostname = trim ( `hostname` );

initialize();

////////////////////////

function initialize() {

global $select_court, $select_time, $num_courts, $template_message, $template_from, $processed_clinics, $title;

$processed_clinics = [];

if ( $num_courts > 1 ) {
    $select_court = '<select name=court> <option value= selected>--- Select court ---';
    for ( $i=1; $i < $num_courts + 1 ; $i++ ) {
        $select_court .= "<option value=$i>Court $i";
                                              }
    $select_court .= '</select>';
			}
else $select_court = '<input type=hidden name=court value=1>';

$select_time = '<select name=seconds>
<option value=selected>--- Select recording time ---
<option value=10>10 seconds test
<option value=900>15 minutes
<option value=1800>30 minutes
<option value=2700>45 minutes
<option value=3600>60 minutes 
<option value=4500>75 minutes 
<option value=5400>90 minutes 
<option value=6300>105 minutes 
<option value=7200>120 minutes 
</select>';

if ( isset ( $template_message ) ) $template_message = urlencode ( $template_message );

$template_from = urlencode ( $title );

} // end function

////////////////////////

function ipad_form() {

global $cookie_password, $default_table, $select_court, $select_time;

echo "<p> $default_table <form action=ipad.php method=GET>
    <p><tr><td class=default_table colspan=2 align=center>
    $select_court </td></tr>

    <input type=hidden name=user_wants value=ipad>
    <tr><td class=default_table colspan=2 align=center>
    <input type=submit value='Display iPad' /> </td></tr></form> </table>";

} // end function

///////////////////////////

function process_options() {

if ( $_POST['user_wants'] === 'picture' ) take_picture() ;
if ( $_POST['user_wants'] === 'live_stream_start' ) live_stream_start() ;
if ( $_POST['user_wants'] === 'yt_live_stream_stop' ) yt_live_stream_stop() ;
if ( $_POST['user_wants'] === 'yt_live_stream_start' ) yt_live_stream_start() ;
if ( $_POST['user_wants'] === 'live_stream_stop' ) live_stream_stop() ;
if ( $_POST['user_wants'] === 'record' ) record_video() ;
if ( $_POST['user_wants'] === 'rally' ) send_rally() ;
if ( $_REQUEST['user_wants'] === 'ipad' ) show_ipad() ;
if ( $_POST['user_wants'] === 'start_record' ) start_record_video() ;
if ( $_POST['user_wants'] === 'stop_record' ) stop_record_video() ;
if ( $_POST['user_wants'] === 'video' ) trim_video() ;

} // end function

//////////////////////////////

function take_picture_form() {

global $cookie_password, $default_table, $select_court;

print "<hr><p> $default_table <tr>
 <td class=default_table colspan=2 align=center>
    <form action=index.php method=POST>
    <input type=hidden name=general_password value=$cookie_password>
    <input type=hidden name=user_wants value=picture>
    $select_court
    <input type=submit value='Take Picture' /> </form> </tr></td></table>";

} // end function

//////////////////////////////

function take_picture_form_user() {

global $cookie_password, $select_court;

print "<hr><p>
    <form action=user.php method=POST>
    $select_court
    <input type=hidden name=general_password value=$cookie_password>
    <input type=hidden name=user_wants value=picture>
    <input type=submit value='Take Picture' /> </form>";

} // end function

/////////////////////////

function take_picture() {

global $court, $www, $court_prefix;

get_court();

//$out = shell_exec ( "raspistill -v -o test.jpg");
$cmd = "ssh $court_prefix$court 'raspistill -w 640 -h 480 -q 50 -o $www/share/court$court.jpg'";

$out = shell_exec ( $cmd);

echo "<h3>$out</h3>";

//echo '<p> <img src=test.jpg width=600></p>';
echo "<p> <img src=share/court$court.jpg></p>";

} // end function

//////////////////////

function get_court() {

global $court, $num_courts;

$court = intval($_REQUEST['court']);

$match = 'No';

for ( $i=1; $i < $num_courts + 1 ; $i++ ) {
    if (  $court == $i ) $match = 'Yes';
                                          }
if ( $match === 'No' ) die_sub ( "No court selected");

} // end function

////////////////////////////

function recording_form( $action ) {

global $cookie_password, $default_table, $select_court, $select_time;

echo "<p> $default_table <form action=$action.php method=POST>
    <p><tr>
    <td class=default_table colspan=2 align=center>
    $select_time </td></tr>
    <p><tr><td class=default_table colspan=2 align=center>
    $select_court </td></tr>
    <input type=hidden name=general_password value=$cookie_password>

    <p> <tr><td class=default_table>Email: </td>
        <td class=default_table><input type=text name=email> </td></tr>
    <input type=hidden name=user_wants value=record>
    <tr><td class=default_table colspan=2 align=center>
    <input type=submit value='Record video' /> </td></tr></form> </table>";

} // end function

//////////////////////////

function record_video() {

global $www, $court, $court_prefix, $template_message, $template_from, $seconds, $email;

$seconds = intval( $_POST['seconds'] );
$email = $_POST['email'];
$email = preg_quote ( $email , '@');

if ( $seconds < 1 ) return;

get_court();

record_video_commands_ssh();

} //end function

//////////////////////////////////

function record_video_commands_ssh() {

global $www, $court, $court_prefix, $template_message, $template_from, $seconds, $email;

echo "Recording for $seconds seconds on $court_prefix $court";
$cmd = "ssh $court_prefix$court 'cp $www/run_picam_template.sh  $www/run_picam.sh'";
$out = shell_exec ( $cmd );

shell_exec ( "ssh $court_prefix$court '/usr/bin/perl -i -wpe 's/seconds/$seconds/g' $www/run_picam.sh'");

$cmd = "ssh $court_prefix$court ". '"/usr/bin/perl -i -wpe '. "'s/template_email/$email/'".  " $www/run_picam.sh" .'"';
shell_exec ( $cmd );

shell_exec ( "ssh $court_prefix$court '/usr/bin/perl -i -wpe 's/template_subject/Admin\+recording/' $www/run_picam.sh'");
shell_exec ( "ssh $court_prefix$court '/usr/bin/perl -i -wpe 's/template_name/Admin/' $www/run_picam.sh'");
shell_exec ( "ssh $court_prefix$court '/usr/bin/perl -i -wpe 's/template_message/$template_message/' $www/run_picam.sh'");
shell_exec ( "ssh $court_prefix$court '/usr/bin/perl -i -wpe 's/template_from/$template_from/' $www/run_picam.sh'");

`ssh $court_prefix$court 'cd $www;./run_picam.sh >> logs/run_admin_picam_output.txt 2>>logs/run_admin_picam_error.txt &'`;

} // end function

///////////////////////////////////////

function prepare_run_picam_template() {

global $email, $name, $subject, $seconds;

shell_exec ( "cp run_picam_template.sh run_picam.sh" );
shell_exec ( "/usr/bin/perl -i -wpe 's/seconds/$seconds/g' run_picam.sh");
//shell_exec ( "/usr/bin/perl -i -wpe 's/template_message/$email/' run_picam.sh");
shell_exec ( "/usr/bin/perl -i -wpe 's/template_email/$email/g' run_picam.sh");
shell_exec ( "/usr/bin/perl -i -wpe 's/template_name/$name/g' run_picam.sh");
shell_exec ( "/usr/bin/perl -i -wpe 's/template_subject/$subject/g' run_picam.sh");

} // end function

////////////////////////////////////

function check_conditions ( $obj ) {

global $hostname, $debug;

//if ( $debug ) return 0;

$court_name =  $obj->{'courtName'};
$court_name = fix_court_name ( $court_name ) ;

$hostname = trim ( `hostname` );

if ( $obj->{'type'} === 'clinic' ) return ( check_clinic_courts ( $obj ) );

if ( $court_name !== $hostname ) return 1;

return 0;

} // end function

//////////////////////////////////////////

function fix_court_name ( $court_name ) {

$court_name = preg_replace ( '/\s/', '' , $court_name);
$court_name = strtolower ( $court_name );

return $court_name;

} // end function

////////////////////////////////////////

function check_clinic_courts ( $obj ) {

global $bearer, $debug, $hostname, $processed_clinics;

$id = $obj->{'reservationId'};
if ( in_array ( $id, $processed_clinics ) ) return 1;

$processed_clinics[] = $id;

$cmd = "curl 'https://api.ussquash.com/resources/res/reservations/infos/$id' -H 'Accept: application/json' -H 'Authorization: Bearer $bearer'";

echo "\nProcessing clinic $id with command:\n$cmd\n\n";

$json = shell_exec ( $cmd );
$obj = json_decode( $json );

$num_courts =  count ( $obj[0]->{'courtNames'} );
$num_players =  count ( $obj[0]->{'Players'} );

//echo "$num_courts , $num_players\n";

for ( $i=0; $i< $num_courts ; $i++ ) {
    $court_name = fix_court_name ( $obj[0]->{'courtNames'}[$i] );
    if ( $debug ) echo "$court_name === $hostname\n";
    if ( $court_name === $hostname ) return 0;
                                     }
//for ( $i=0; $i< $num_players ; $i++ ) echo $obj[0]->{'Players'}[$i]->{'player'}->{'email'} . "\n";

return 1;

} // end function

////////////////////////////////////

function check_start_time ( $obj ) {

global $seconds;

$start_time = $obj->{'startTime'};

$date1 = new DateTime('now');
$date2 = new DateTime( $start_time );

$interval = date_diff($date1, $date2);
$minutes = $interval->format('%h') * 60 + $interval->format('%i');

$invert = $interval->invert;  # 1 if start_time is in the past

if ( $invert == 1 ) $seconds = 2580; else $seconds = 2700;
//if ( $invert == 1 ) $seconds = 3180; else $seconds = 3300;

if ( $invert == 1 ) $minutes = $minutes * -1;

// echo "Seconds is $seconds, start_time is $start_time";

return $minutes;

} // end function

////////////////////////////////////

function check_membership ( $obj ) {

global $num_players, $bearer;

if ( $num_players < 1 ) { echo "Number of players is $num_players\n"; return 0; }

for ( $i=0; $i< $num_players ; $i++ )  {
    $id =  $obj[0]->{'Players'}[$i]->{'player'}->{'id'} ;
    $cmd = "curl 'https://api.ussquash.com/resources/res/clubs/12034/members?ssmId=$id' -H 'Accept: application/json' -H 'Authorization: Bearer $bearer'";
    echo "\nProcessing player $id membership with command:\n$cmd\n\n";
    $json = shell_exec ( $cmd );
    $player_obj = json_decode( $json );
    echo "Membership is " . $player_obj[0]->{'memberType'} . "\n";
} // end for loop

} // end funtion

//////////////////////

function show_AI () {

global $default_table, $num_courts;

$out = file_get_contents ( "share/AI/AI.txt"  );
$out = preg_split ( '/\n/', $out );

$minutes = file_get_contents ( "share/AI/minutes.txt"  );
$minutes = preg_split ( '/\n/', $minutes );

echo "<hr><p>$default_table<tr class=default_table>";

$index = 0;
for ( $i=0; $i < $num_courts  ; $i++ ) {
   
   $index += 1;
   $status = 'occupied';

   $line = preg_split ( '/\s/', $out[$i] );
   if ( $line[0] === '0' ) $status = 'empty';

   echo "<td class=default_table>Court $index $status - " .  intval ( $line[1]* 100) . "% confidence </td>
   <td class=default_table>$line[2] darkness</td>
   <td class=default_table>$minutes[$i] minutes</td>
   </tr>";
}

echo '</table>';

} // end function

//////////////////////

function read_AI () {

global $default_table, $num_courts, $court_status, $darkness;

$out = file_get_contents ( "share/AI/AI.txt"  );
$out = preg_split ( '/\n/', $out );

for ( $i=0; $i < $num_courts ; $i++ ) {

   $status = 'occupied';

   $line = preg_split ( '/\s/', $out[$i] );
   if ( $line[0] === '0' ) $status = 'empty';

   $court_status{$i+1} = $status;
   $darkness{$i+1} = $line[2];
}

} // end function

?>
