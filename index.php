<?php

error_reporting(E_ALL);
//ini_set('display_errors', true);

require_once "/var/www/cgi-bin/scripts/include.php";
require_once "./include.php";

echo "<title> $title </title> $image
        <p> $header_title $peoria_colors $common_css_style";

cookie_login_page();

process_options();

echo " <p> <h2>Admin Control Panel </h2>";

for ( $i=1; $i < $num_courts + 1 ; $i++ ) 
    if ( file_exists ( "share/.video_lock_$court_prefix$i" ) ) { echo "<h3>Court $i camera in use</h3>"; }

echo "<P><a href=todays_videos.php?user_wants=videos>Today's videos</a>";

upload_form();

show_courts();

show_AI();

////////////////////////

function show_courts() {

global $num_courts, $court_prefix;

echo "<p>";
for ( $i=1; $i < $num_courts + 1 ; $i++ ) 
//    echo "<p> <h3> Court $i</h3><p> <img src=share/$court_prefix$i.jpg>";
    echo "<img src=share/$court_prefix$i.jpg>";

} // end function

////////////////////////

function upload_form() {

global $cookie_password, $select_court, $select_time, $yt_livestream_url;

recording_form( 'index');

echo "<hr>  
    <a href=live_stream.php target=new>Watch live stream</a>
<p>    <form action=index.php method=POST>
    <p> $select_court
    <input type=hidden name=general_password value=$cookie_password>
    <input type=hidden name=user_wants value=live_stream_start>
    <input type=submit value='Start live streaming ( no recording )' /> </form>

<p>  

    <form action=index.php method=POST>
    <p> $select_court
    <input type=hidden name=general_password value=$cookie_password>
    <input type=hidden name=user_wants value=live_stream_stop>
    <input type=submit value='Stop live streaming' /> </form>

<hr><p>  

    <a href=$yt_livestream_url target=new>Watch YouTube live stream</a>
    <form action=index.php method=POST>
    <p> $select_court
    <input type=hidden name=general_password value=$cookie_password>
    <input type=hidden name=user_wants value=yt_live_stream_start>
    <input type=submit value='Start YouTube live streaming' /> </form>
<p>  
    <form action=index.php method=POST>
    <p> $select_court
    <input type=hidden name=user_wants value=yt_live_stream_stop>
    <input type=hidden name=general_password value=$cookie_password>
    <input type=submit value='Stop YouTube live streaming' /> </form>";

echo '<hr>';
ipad_form();

take_picture_form();

echo '<hr>';

} // end function

///////////////////////////////

function live_stream_start() {

global $court, $court_prefix;

echo "<h3>Starting live streaming</h3>";

get_court();

`ssh $court_prefix$court 'pkill -9 picam; pkill ffmpeg;rm /var/www/html/share/hls/*'`;

//`/var/www/html/make_dirs.sh`;
//`/home/squash/picam/picam --alsadev hw:1,0 --maxfps 30 --minfps 10 -v 1000000 -w 1024 -h 768 -o /run/shm/hls > logs/live_stream_output.txt 2>logs/live_stream_error.txt &`;
`ssh $court_prefix$court '/home/squash/picam/picam --alsadev hw:1,0 --maxfps 30 --minfps 10 -v 1000000 -w 1024 -h 768 -o /var/www/html/share/hls > /var/www/html/logs/live_stream_output.txt 2>/var/www/html/logs/live_stream_error.txt &'`;

} // end function

///////////////////////////////

function live_stream_stop() {

global $court_prefix;

echo "<h3>Stopping live streaming</h3>";

global $court;

get_court();

`ssh $court_prefix$court 'pkill -9 picam; pkill ffmpeg'`;

} // end function

///////////////////////////////

function yt_live_stream_start() {

global $yt_livestream, $court_prefix;

echo "<h3>Starting YouTube live streaming</h3>";

global $court;

get_court();

`ssh $court_prefix$court 'pkill -9 picam; pkill ffmpeg'`;

//`ssh $court_prefix$court 'ffmpeg -i tcp://127.0.0.1:8181?listen -c:v copy -c:a aac -ar 44100 -ab 40000 -f flv rtmp://a.rtmp.youtube.com/live2/a3d4-tvt0-gssb-4ktd > /var/www/html/logs/yt_ffmpeg_live_stream_output.txt 2>/var/www/html/logs/yt_ffmpeg_live_stream_error.txt &'`;
`ssh $court_prefix$court 'ffmpeg -i tcp://127.0.0.1:8181?listen -c:v copy -c:a aac -ar 44100 -ab 40000 -f flv $yt_livestream > /var/www/html/logs/yt_ffmpeg_live_stream_output.txt 2>/var/www/html/logs/yt_ffmpeg_live_stream_error.txt &'`;

sleep(3);

//`ssh $court_prefix$court '/home/squash/picam/picam --alsadev hw:1,0 --tcpout tcp://127.0.0.1:8181 > /var/www/html/logs/yt_live_stream_output.txt 2>/var/www/html/logs/yt_live_stream_error.txt &'`;
//`ssh $court_prefix$court '/home/squash/picam/picam --alsadev hw:1,0 --vfr --maxfps 30 --minfps 10 --shutter 20000 -v 1000000 --mode 2 -w 1640 -h 1232 --tcpout tcp://127.0.0.1:8181 > /var/www/html/logs/yt_live_stream_output.txt 2>/var/www/html/logs/yt_live_stream_error.txt &'`;
`ssh $court_prefix$court '/home/squash/picam/picam --alsadev hw:1,0 --maxfps 30 --minfps 10 -v 1000000 -w 1024 -h 768 --tcpout tcp://127.0.0.1:8181 > /var/www/html/logs/yt_live_stream_output.txt 2>/var/www/html/logs/yt_live_stream_error.txt &'`;

} // end function

///////////////////////////////

function yt_live_stream_stop() {

global $court_prefix;

echo "<h3>Stopping YouTube live streaming</h3>";

global $court;

get_court();

`ssh $court_prefix$court 'pkill -9 picam; pkill ffmpeg'`;

} // end function

?>
