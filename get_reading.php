#!/usr/bin/php
<?php
#
# Add the following to crontab:
# 0 */3 * * * cd /var/www/vhosts/tm3.org/standrew && ./get_reading.php
#

$today = date( 'Y/m/d' );

# Fetch, for example, https://oca.org/readings/daily/2016/02/14/2
$url = 'https://oca.org/readings/daily/' . $today . '/1';

# TODO: On Sunday there are three readings, and the epistle is at '/2'
# rather than '/1'.

$context = [
  'http' => [
    'method' => 'GET',
  ]
];
$context = stream_context_create($context);
$page = file_get_contents( $url, false, $context );

# Find the reading in the page, and strip off all the extra stuff
$page = preg_replace( '/^.*<article>.+?<h2>/s', '', $page );
$page = preg_replace( '/<\/article>.*/s', '', $page );

$page = preg_replace( '/<em>\(Epistle\)<\/em>/s', '', $page );

# Remove verse numbers
$page = preg_replace( '/<dt>.*?<\/dt>/s', '', $page );

# And all the extra HTML markup
$page = preg_replace( '/<.*?>/s', '', $page );
$page = preg_replace( '/&\w+?;/', '', $page );

$file_handle = fopen("today_reading.php", "w");
$file_contents = "<?php\nfunction reading() {\n\n\$reading = \"$page\";\nreturn \$reading;\n}\n?>\n\n";

fwrite($file_handle, $file_contents);
fclose($file_handle);
?>
