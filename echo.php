<?php

$data = file_get_contents("php://input");
$query = json_decode( $data );
# error_log( print_r( $query, 1 ) );

$help = "Ask me who todays saint is, or whether today is a fast day";

if ( $query ) {
    $action = $query->request->intent->name;

    if ( $action == "GetSaint" ) {
        $response = getsaint();
    }
    elseif ( $action == "GetFast" ) {
        $response = getfast();
    }
    elseif ( $action == "GetReading" ) {
        $response = getreading();
    } elseif ( $action = 'GetHelp' ) {
        $response = $help;
    } else {
        $response = $help;
    }

    sendresponse( $response);
} else {
    sendresponse( $help );
}

/*
Get today's saint from oca.org
*/
function getsaint() {
    # What's today
    $today = date( 'Y/m/d' );

    # Fetch, for example,  https://oca.org/saints/lives/2016/02/14
    $url = 'https://oca.org/saints/lives/' . $today;

    $context = [
      'http' => [
        'method' => 'GET',
      ]
    ];
    $context = stream_context_create($context);
    $page = file_get_contents( $url, false, $context );

    # The saint of today is in the second <h2> - the first is just
    # today's date. So ...
    $pattern = '/^.*?<h2 ?.*?>(.+?)<\/h2>/si';

    # Throw the first one away ...
    $page = preg_replace( $pattern, '', $page );

    # and then capture the second one.
    preg_match( $pattern, $page, $matches );

    return "Today's saint or feast is: " . $matches[1];
}

function getfast() {
    return "Sorry, that function isn't implemented yet. Check back in a day or two.";
}

/*
  Get today's reading from oca.org

  Note that the page layout will change some day and break this.
*/
function getreading() {

    # The reading is fetched periodically, and stashed in
    # today_reading.php
    include 'today_reading.php';
    $reading = reading();
    return $reading;

}

/*

Format and return the response back to Alexa

*/
function sendresponse( $response ) {

    $response = array (
       "version" => '1.0',
        'response' => array (
            'outputSpeech' => array (
                'type' => 'PlainText',
                'text' => $response
            ),

             'card' => array (
                   'type' => 'Simple',
                   'title' => 'StAndrew',
                   'content' => $response
             ),

            'shouldEndSession' => 'true'
        ),
    );

    echo json_encode($response);
}

?>

