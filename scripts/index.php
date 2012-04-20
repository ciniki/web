<?php
//
// This script will deliver the website for clients,
// or the default page for main domain.
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
$themes_root = "/ciniki-api/web/themes";
$themes_root_url = "/ciniki-web-themes";

//
// Initialize Ciniki
//
$ciniki = array();
require_once($ciniki_root . '/ciniki-api/core/private/loadCinikiConfig.php');
if( ciniki_core_loadCinikiConfig($ciniki, $ciniki_root) == false ) {
	print_error('There is currently a configuration problem, please try again later.');
	exit;
}

// standard functions
require_once($ciniki_root . '/ciniki-api/core/private/dbQuote.php');

//
// Initialize Database
//
require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInit.php');
$rc = ciniki_core_dbInit($ciniki);
if( $rc['stat'] != 'ok' ) {
	return $rc;
}

//
// Setup the defaults
//
$ciniki['request'] = array('business_id'=>0, 'page'=>'', 'args'=>array());
$ciniki['session'] = array();
$ciniki['business'] = array('modules'=>array());

// 
// Split the request URI into parts
$uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);
$uri_split = preg_split('/\//', $uri);
if( !is_array($uri_split) ) {
	$uri_split = array($uri_split);
}

//
// Determine which site and page should be displayed
// FIXME: Check for redirects from sitename or domain names to primary domain name.
//
if( $ciniki['config']['web']['master.domain'] == $_SERVER['HTTP_HOST'] ) {
	//
	// Check which page, or if they requested a clients website
	//
	if( $uri == '' ) {
		$ciniki['request']['page'] = 'masterindex';
		$ciniki['request']['business_id'] = $ciniki['config']['core']['master_business_id'];
		exit;
	} elseif( $uri_split[0] == 'about' ) {
		$ciniki['request']['page'] = 'about';
		$ciniki['request']['business_id'] = $ciniki['config']['core']['master_business_id'];
		$uri_split = array_shift($uri_split);
		exit;
	} else {
		//
		// Lookup client name in database
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/lookupClientDomain.php');
		$rc = ciniki_web_lookupClientDomain($ciniki, $uri_split[0], 'sitename');
		if( $rc['stat'] != 'ok' ) {
			print_error('Unknown business ' . $uri_split[0]);
			exit;
		}
		$ciniki['request']['business_id'] = $rc['business_id'];
		$ciniki['business']['modules'] = $rc['modules'];

		//
		// Remove the client name from the URI list
		//
		$url_split = array_shift($uri_split);
		$ciniki['request']['page'] = $uri_split[0];
	}
} else {
	//
	// Lookup client domain in database
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/lookupClientDomain.php');
	$rc = ciniki_web_lookupClientDomain($ciniki, $_SERVER['HTTP_HOST'], 'domain');
	if( $rc['stat'] != 'ok' ) {
		print_error('Unknown business');
		exit;
	}
	$ciniki['request']['business_id'] = $rc['business_id'];
	$ciniki['business']['modules'] = $rc['modules'];

	$ciniki['request']['page'] = $uri_split[0];
	if( $ciniki['request']['page'] != '' ) {
		$url_split = array_shift($uri_split);
	}
}

//
// Get the web settings for the business
//
require_once($ciniki['config']['core']['modules_dir'] . '/web/private/settings.php');
$rc = ciniki_web_settings($ciniki, $ciniki['request']['business_id']);
if( $rc['stat'] != 'ok' ) {
	print_error('Website not configured.');
	exit;
}
$ciniki['business']['settings'] = $rc['settings'];
// Theme, pages, settings

//
// Check if home page is a redirect to another page
//
if( $ciniki['request']['page'] == 'home' && $ciniki['business']['settings']['home.page'] == 'yes' 
	&& $ciniki['business']['settings']['home.redirect'] != '' ) {
	$ciniki['request']['page'] = $ciniki['business']['settings']['home.redirect'];
}

//
// Process the request
//
if( $ciniki['request']['page'] == 'home' && $ciniki['business']['settings']['home.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateMasterIndex.php');
	$rc = ciniki_web_generateMasterIndex($ciniki);
} elseif( $ciniki['request']['page'] == 'home' && $ciniki['business']['settings']['home.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateHome.php');
	$rc = ciniki_web_generateHome($ciniki);
} elseif( $ciniki['request']['page'] == 'about' && $ciniki['business']['settings']['about.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateAbout.php');
	$rc = ciniki_web_generateAbout($ciniki);
} elseif( $ciniki['request']['page'] == 'contact' && $ciniki['business']['settings']['contact.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateContact.php');
	$rc = ciniki_web_generateContact($ciniki);
} elseif( $ciniki['request']['page'] == 'events' && $ciniki['business']['settings']['events.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateEvents.php');
	$rc = ciniki_web_generateEvents($ciniki);
} elseif( $ciniki['request']['page'] == 'friends' && $ciniki['business']['settings']['friends.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateFriends.php');
	$rc = ciniki_web_generateFriends($ciniki);
} elseif( $ciniki['request']['page'] == 'gallery' && $ciniki['business']['settings']['gallery.page'] == 'yes' ) {
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generateGallery.php');
	$rc = ciniki_web_generateGallery($ciniki);
} else {
	print_error('Unknown page ' . $ciniki['request']['page']);
}

if( $rc['stat'] != 'ok' ) {
	print_error('Unable to generate page.');
	exit;
}

//
// Output the page contents
// FIXME: Add caching in here
//
if( $ciniki['response']['content'] != '' ) {
	print $ciniki['response']['content'];
}

//
// Done
//
exit;

function print_master_index() {
	print "<html>";
	print "Master domain index page";
	//
	// Show about button, and login button
	//

	//
	// Show logo
	//

	//
	// Show list of customers
	//
	// $rc = ciniki_web_publicBusinesses($ciniki);
	//
	print "</html>";
	exit;
}

//
// Supporting functions for the main page
//

function print_error($msg) {
print "<!DOCTYPE html>\n";
?>
<html>
<head><title>Error</title></head>
<body>
<div id="m_error">
	<div id="me_content">
		<div id="mc_content_wrap" class="medium">
			<p>Oops, we seem to have hit a snag.</p>
			<table class="list header border" cellspacing='0' cellpadding='0'>
				<thead>
					<tr><th>Package</th><th>Code</th><th>Message</th></tr>
				</thead>
				<tbody>
					<tr><td>???</td><td>???</td><td><?php echo $msg; ?></td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
</body>
</html>
<?php
}


?>
