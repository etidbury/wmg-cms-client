<?php


function printErrorPage(){
    #header('Content-Type: application/json');
    //set headers to NOT cache a page
    header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
    header("Pragma: no-cache"); //HTTP 1.0
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header("Location: /");

    header("HTTP/1.0 404 Not Found");

    echo '<html>
            <head>
                <meta http-equiv="Refresh" content="0;url=/?error=404" />
            </head><body></body>
          </html>';die();
    //die("404 Page Not Found");
}