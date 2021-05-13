<?php
//
// Description
// -----------
// This script will deliver the website for clients,
// or the default page for main domain.
//
// All web requests for tenant websites are funnelled through this script.
//
$start_time = microtime(true);

//
// Load ciniki
//
global $ciniki_root;
$ciniki_root = dirname(__FILE__);
// Some systems don't follow symlinks like others
if( !file_exists($ciniki_root . '/ciniki-api.ini') ) {
    $ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
}

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
require_once($ciniki_root . '/ciniki-mods/core/private/dbHashQuery.php');
require_once($ciniki_root . '/ciniki-mods/core/private/loadMethod.php');
require_once($ciniki_root . '/ciniki-mods/core/private/checkModuleFlags.php');
require_once($ciniki_root . '/ciniki-mods/core/private/checkModuleActive.php');

//
// Initialize Database
//
require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbInit.php');
$rc = ciniki_core_dbInit($ciniki);
if( $rc['stat'] != 'ok' ) {
    print_error(NULL, 'There is currently a problem with our systems.  We are working to fix it as quickly as possible.  Please try again in a few minutes.');
    exit;
}

//
// Setup the defaults
//
$ciniki['request'] = array(
    'query_string' => '',
    'args' => array(), 
    'ssl' => 'no',
    );

// 
// Split the request URI into parts
//
$uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);              // Remove leading slash (/)
$u = preg_split('/\?/', $uri);                                          // Separate out arguments
$ciniki['request']['uri_split'] = preg_split('/\//', $u[0]);            // Split on slash (/) to get each piece
if( isset($u[1]) ) {
    $ciniki['request']['query_string'] = $u[1];
}
if( !is_array($ciniki['request']['uri_split']) ) {
    $ciniki['request']['uri_split'] = array($ciniki['request']['uri_split']);
}

//
// Parse the query_string args
//
if( isset($_GET) && is_array($_GET) ) {
    foreach($_GET as $arg_key => $arg_value) {
        if( is_array($arg_value) ) {
            error_log("Unsupported arguments: $arg_key");
        } else {
            $ciniki['request']['args'][$arg_key] = rawurldecode($arg_value);
        }
    }
}

//
// Check if SSL 
//
if( (isset($_SERVER['HTTP_CLUSTER_HTTPS']) && $_SERVER['HTTP_CLUSTER_HTTPS'] == 'on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' )
    ) {
    $ciniki['request']['ssl'] = 'yes';
}

//
// Process the request
//
ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processRequest');
ciniki_web_processRequest($ciniki);

exit;

//
// Supporting functions for the main page
//

function print_error($rc, $msg) {
print "<!DOCTYPE html>\n";
?>
<html>
<head><title>Error</title></head>
<body>
<div id="m_error">
    <div id="me_content">
        <div id="mc_content_wrap" class="medium">
            <p>Oops, we seem to have hit a snag.  <?php echo $msg; ?></p>
            <?php if($rc != NULL && $rc['stat'] != 'ok' ) { ?>
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
