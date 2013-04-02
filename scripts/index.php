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
$themes_root = "/ciniki-api/web/themes";
$themes_root_url = "/ciniki-web-themes";

//
// Initialize Ciniki
//
$ciniki = array();
require_once($ciniki_root . '/ciniki-api/core/private/loadCinikiConfig.php');
if( ciniki_core_loadCinikiConfig($ciniki, $ciniki_root) == false ) {
	print_error(NULL, 'There is currently a configuration problem, please try again later.');
	exit;
}

// standard functions
require_once($ciniki_root . '/ciniki-api/core/private/dbQuote.php');
require_once($ciniki_root . '/ciniki-api/core/private/loadMethod.php');

//
// Initialize Database
//
require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbInit.php');
$rc = ciniki_core_dbInit($ciniki);
if( $rc['stat'] != 'ok' ) {
	return $rc;
}

//
// Setup the defaults
//
$ciniki['request'] = array('business_id'=>0, 'page'=>'', 'args'=>array(), 
	'cache_url'=>'/ciniki-web-cache', 
	'cache_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/cache',
	'layout_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/layouts',
	'layout_url'=>'/ciniki-web-layouts',
	'theme_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/themes',
	'theme_url'=>'/ciniki-web-themes',
	);
session_start();
$ciniki['session'] = array();
if( isset($_SESSION['customer']) ) {
	$ciniki['session']['customer'] = $_SESSION['customer'];
	$ciniki['session']['change_log_id'] = $_SESSION['change_log_id'];
	$ciniki['session']['user'] = array('id'=>'-2');
}
$ciniki['business'] = array('modules'=>array());

// 
// Split the request URI into parts
//
$uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);
$u = preg_split('/\?/', $uri);
$ciniki['request']['uri_split'] = preg_split('/\//', $u[0]);
if( isset($u[1]) ) {
	$ciniki['request']['query_string'] = $u[1];
} else {
	$ciniki['request']['query_string'] = '';
}
if( !is_array($ciniki['request']['uri_split']) ) {
	$ciniki['request']['uri_split'] = array($ciniki['request']['uri_split']);
}

//
// Determine which site and page should be displayed
// FIXME: Check for redirects from sitename or domain names to primary domain name.
//
if( $ciniki['config']['ciniki.web']['master.domain'] != $_SERVER['HTTP_HOST'] ) {
	//
	// Lookup client domain in database
	//
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/lookupClientDomain.php');
	$rc = ciniki_web_lookupClientDomain($ciniki, $_SERVER['HTTP_HOST'], 'domain');
	if( $rc['stat'] != 'ok' ) {
		// Assume master business
//		print_error($rc, 'unknown business ' . $ciniki['request']['uri_split'][0]);
//		exit;
	}
	//
	// If a business if found, then setup the details
	//
	if( $rc['stat'] == 'ok' ) {
		$ciniki['request']['business_id'] = $rc['business_id'];
		$ciniki['business']['modules'] = $rc['modules'];
		if( isset($rc['redirect']) && $rc['redirect'] != '' ) {
			Header('HTTP/1.1 301 Moved Permanently'); 
			Header('Location: http://' . $rc['redirect'] . $_SERVER['REQUEST_URI']);
		}

		$ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
		if( $ciniki['request']['page'] != '' ) {
			$uris = $ciniki['request']['uri_split'];
			array_shift($uris);
			$ciniki['request']['uri_split'] = $uris;
		}
		$ciniki['request']['base_url'] = '';
	}
}

// 
// If nothing was found, assume the master business
//
if( $ciniki['request']['business_id'] == 0 ) {
	//
	// Check which page, or if they requested a clients website
	//
	if( $uri == '' ) {
		$ciniki['request']['page'] = 'masterindex';
		$ciniki['request']['business_id'] = $ciniki['config']['ciniki.core']['master_business_id'];
		$ciniki['request']['base_url'] = '';
	} elseif( $ciniki['request']['uri_split'][0] == 'about' 
		|| $ciniki['request']['uri_split'][0] == 'contact'
		|| $ciniki['request']['uri_split'][0] == 'signup'
		|| $ciniki['request']['uri_split'][0] == 'documentation'
		|| $ciniki['request']['uri_split'][0] == 'support'
		) {
		$ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
		$ciniki['request']['business_id'] = $ciniki['config']['ciniki.core']['master_business_id'];
		$ciniki['request']['base_url'] = '';
		$uris = $ciniki['request']['uri_split'];
		array_shift($uris);
		$ciniki['request']['uri_split'] = $uris;
	} else {
		//
		// Lookup client name in database
		//
		require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/lookupClientDomain.php');
		$rc = ciniki_web_lookupClientDomain($ciniki, $ciniki['request']['uri_split'][0], 'sitename');
		if( $rc['stat'] != 'ok' ) {
			print_error($rc, 'Unknown business ' . $ciniki['request']['uri_split'][0]);
			exit;
		}
		$ciniki['request']['business_id'] = $rc['business_id'];
		$ciniki['business']['modules'] = $rc['modules'];
		$ciniki['request']['base_url'] = '/' . $ciniki['request']['uri_split'][0];
		if( isset($rc['redirect']) && $rc['redirect'] != '' ) {
			Header('HTTP/1.1 301 Moved Permanently'); 
			Header('Location: http://' . $rc['redirect'] . preg_replace('/^\/[^\/]+/', '', $_SERVER['REQUEST_URI']));
		}

		//
		// Remove the client name from the URI list
		//
		if( count($ciniki['request']['uri_split']) > 1 ) {
			$uris = $ciniki['request']['uri_split'];
			array_shift($uris);
			$ciniki['request']['page'] = $uris[0];
			array_shift($uris);
			$ciniki['request']['uri_split'] = $uris;
		} else {
			$ciniki['request']['url_split'] = array();
			$ciniki['request']['page'] = '';
		}
	}
}

//
// Get the details for the business
//
require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/businesses/web/details.php');
$rc = ciniki_businesses_web_details($ciniki, $ciniki['request']['business_id']);
if( $rc['stat'] != 'ok' ) {
	print_error($rc, 'Website not configured.');
	exit;
}
$ciniki['business']['details'] = $rc['details'];
//
// Get the web settings for the business
//
require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/settings.php');
$rc = ciniki_web_settings($ciniki, $ciniki['request']['business_id']);
if( $rc['stat'] != 'ok' ) {
	print_error($rc, 'Website not configured.');
	exit;
}
$settings = $rc['settings'];

// print "<pre>"; print_r($ciniki); print "</pre>";

// Theme, pages, settings

//
// Check if no page specified, which means home page
//
if( $ciniki['request']['page'] == '' ) {
	$ciniki['request']['page'] = 'home';
}

//
// Check if home page is a redirect to another page
//
if( $ciniki['request']['page'] == 'home' && $settings['page-home-active'] == 'yes' 
	&& isset($settings['page-home-redirect']) && $settings['page-home-redirect'] != '' ) {
	$ciniki['request']['page'] = $settings['page-home-redirect'];
}
//
// If home page is not active, search for the next page to call home
//
if( $ciniki['request']['page'] == 'home' && $settings['page-home-active'] != 'yes' ) {
	if( $settings['page-about-active'] == 'yes' ) {
		$ciniki['request']['page'] = 'about';
	} elseif( $settings['page-gallery-active'] == 'yes' ) {
		$ciniki['request']['page'] = 'gallery';
	} elseif( $settings['page-contact-active'] == 'yes' ) {
		$ciniki['request']['page'] = 'contact';
	} elseif( $settings['page-events-active'] == 'yes' ) {
		$ciniki['request']['page'] = 'events';
	} elseif( $settings['page-links-active'] == 'yes' ) {
		$ciniki['request']['page'] = 'links';
	} else {
		print_error(NULL, 'Website not configured');
		exit;
	}
}

//
// Process the request
//

// Master Home page
if( $ciniki['request']['page'] == 'masterindex' && $settings['page-home-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generateMasterIndex.php');
	$rc = ciniki_web_generateMasterIndex($ciniki, $settings);
} 
// Signup Page
elseif( $ciniki['request']['page'] == 'signup' && $settings['page-signup-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSignup.php');
	$rc = ciniki_web_generatePageSignup($ciniki, $settings);
} 
// API Page
elseif( $ciniki['request']['page'] == 'api' && $settings['page-api-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAPI.php');
	$rc = ciniki_web_generatePageAPI($ciniki, $settings);
} 
// Home Page
elseif( $ciniki['request']['page'] == 'home' && $settings['page-home-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageHome.php');
	$rc = ciniki_web_generatePageHome($ciniki, $settings);
} 
// About
elseif( $ciniki['request']['page'] == 'about' && $settings['page-about-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAbout.php');
	$rc = ciniki_web_generatePageAbout($ciniki, $settings);
} 
// Exhibitors
elseif( $ciniki['request']['page'] == 'exhibitors' && $settings['page-exhibitions-exhibitors-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageExhibitors.php');
	$rc = ciniki_web_generatePageExhibitors($ciniki, $settings);
}
// Sponsors
elseif( $ciniki['request']['page'] == 'sponsors' 
	&& ( 
		(isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes') 
		|| (isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes') 
	) ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSponsors.php');
	$rc = ciniki_web_generatePageSponsors($ciniki, $settings);
}
// Members
elseif( $ciniki['request']['page'] == 'members' && $settings['page-members-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageMembers.php');
	$rc = ciniki_web_generatePageMembers($ciniki, $settings);
}
// Gallery
elseif( $ciniki['request']['page'] == 'gallery' && $settings['page-gallery-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageGallery.php');
	$rc = ciniki_web_generatePageGallery($ciniki, $settings);
}
// Events
elseif( $ciniki['request']['page'] == 'events' && $settings['page-events-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageEvents.php');
	$rc = ciniki_web_generatePageEvents($ciniki, $settings);
} 
// Links
elseif( $ciniki['request']['page'] == 'links' && $settings['page-links-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageLinks.php');
	$rc = ciniki_web_generatePageLinks($ciniki, $settings);
} 
// Newsletters
elseif( $ciniki['request']['page'] == 'newsletters' && $settings['page-newsletters-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageNewsletters.php');
	$rc = ciniki_web_generatePageNewsletters($ciniki, $settings);
} 
// Downloads
elseif( $ciniki['request']['page'] == 'downloads' && $settings['page-downloads-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageDownloads.php');
	$rc = ciniki_web_generatePageDownloads($ciniki, $settings);
} 
// Account
elseif( $ciniki['request']['page'] == 'account' && $settings['page-account-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAccount.php');
	$rc = ciniki_web_generatePageAccount($ciniki, $settings);
} 
// Contact
elseif( $ciniki['request']['page'] == 'contact' && $settings['page-contact-active'] == 'yes' ) {
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageContact.php');
	$rc = ciniki_web_generatePageContact($ciniki, $settings);
} 
// Unknown page
else {
	print_error($rc, 'Unknown page ' . $ciniki['request']['page']);
	exit;
}

if( $rc['stat'] != 'ok' ) {
	print_error($rc, 'Unable to generate page.');
	exit;
}

//
// Output the page contents
// FIXME: Add caching in here
//
if( isset($rc['content']) && $rc['content'] != '' ) {
	print $rc['content'];
}

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
<head><title>Error</title></head>
<body>
<div id="m_error">
	<div id="me_content">
		<div id="mc_content_wrap" class="medium">
			<p>Oops, we seem to have hit a snag.  <?php echo $msg; ?></p>
			<?php if($rc != NULL && $rc['stat'] != 'ok' ) { ?>
			<table class="list header border" cellspacing='0' cellpadding='0'>
				<thead>
					<tr><th>Package</th><th>Code</th><th>Message</th></tr>
				</thead>
				<tbody>
					<?php
					print "<tr><td>" . $rc['err']['pkg'] . "</td><td>" . $rc['err']['code'] . "</td><td>" . $rc['err']['msg'] . "</td></tr>\n";
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
