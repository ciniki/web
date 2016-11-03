<?php
//
// Description
// -----------
// This script will deliver the website for clients,
// or the default page for main domain.
//
// All web requests for business websites are funnelled through this script.
//


//
// Load ciniki
//
global $ciniki_root;
$ciniki_root = dirname(__FILE__);
// Some systems don't follow symlinks like others
if( !file_exists($ciniki_root . '/ciniki-api.ini') ) {
    $ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
}
$themes_root = "/ciniki-mods/web/themes";
$themes_root_url = "/ciniki-web-themes";

//
// Initialize Ciniki
//
$ciniki = array();
require_once($ciniki_root . '/ciniki-mods/core/private/loadCinikiConfig.php');
if( ciniki_core_loadCinikiConfig($ciniki, $ciniki_root) == false ) {
    print_error(NULL, 'There is currently a configuration problem, please try again later.');
    exit;
}

// standard functions
require_once($ciniki_root . '/ciniki-mods/core/private/dbQuote.php');
require_once($ciniki_root . '/ciniki-mods/core/private/loadMethod.php');

//
// Initialize Database
//
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInit');
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
$rc = ciniki_core_dbInit($ciniki);
if( $rc['stat'] != 'ok' ) {
    return $rc;
}



$uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);
if( $uri == '' ) {
    print_error(NULL, "Not Found");
}
$strsql = "SELECT id, business_id, furl FROM ciniki_web_shorturls "
    . "WHERE surl = '" . ciniki_core_dbQuote($ciniki, $uri) . "' "
    . "";
$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'url');
if( $rc['stat'] != 'ok' ) {
    print_error(NULL, 'Not found');
}
if( isset($rc['url']) ) {
    Header('HTTP/1.1 301 Moved Permanently');
    Header('Location: ' . $rc['url']['furl']);
    exit;
}
print_error(NULL, '');

//
// Done
//
exit;

//
// Supporting functions for the main page
//

function print_error($rc, $msg) {
print "<!DOCTYPE html>\n";
?>
<html>
<head>
<title>Ciniki ShortURL</title>
</head>
<body>
<div id="m_error">
    <div id="me_content">
        <div id="mc_content_wrap" class="medium">
            <h1>Ciniki - ShortURL</h1>
            <p>This is a service for <a href='http://ciniki.com/'>Ciniki</a> clients to provide short URL's for Twitter and other services.</p>
            <?php if($rc != NULL && $rc['stat'] != 'ok' ) { ?>
            <p>Oops, we seem to have hit a snag.  <?php echo $msg; ?></p>
            <table class="list header border" cellspacing='0' cellpadding='0'>
                <thead>
                    <tr><th>Code</th><th>Message</th></tr>
                </thead>
                <tbody>
                    <?php
                    print "<tr><td>" . $rc['err']['code'] . "</td><td>" . $rc['err']['msg'] . "</td></tr>\n";
                    ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
<?php
}

?>
