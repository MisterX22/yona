<?php
/**
*   This file serves as a way to serve images to browsers.
*   It replies to GET requests with following query parameters:
*   @param id The images id in database
*   @param conflist The name of the conference containing the image
*
*   @author PH
*/
include('includes/controller.php');
$controller = new Controller();

# viewing the uploaded image
if ( isset($_GET['id']) && isset($_GET['conflist']) ) {
    $id = $controller->escape($_GET['id']);
    $conflist = $controller->escape($_GET['conflist']);
    // query for the image binary data in the db
    $img = $controller->get_image($conflist, $id);

    if ( $img ) {
        // let the client browser know what type of data you're sending
        header('Content-type: '.$img['mime']);

        // dump the binary data to the browser
        echo $img['data'];
        exit;
    } else {
        header('HTTP/1.1 404 Not Found');
        exit;
    }
}
else
{
    header('HTTP/1.1 404 Not Found');
    exit;
}
?>