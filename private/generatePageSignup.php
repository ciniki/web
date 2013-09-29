<?php
//
// Description
// -----------
// This function will generate the sign up page for the master website (ciniki.com).
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSignup(&$ciniki, $settings) {

	//
	// This page must be run from SSL, unless otherwise specified in the config.
	// If SSL is turned off in the config, then this is a development machine,
	// and don't need to worry, just use https for Production.  If ssl is turned on, then
	// check to make sure that signup was called from https, if not redirect.
	//
	if( isset($ciniki['config']) && isset($ciniki['config']['ciniki.core']) && isset($ciniki['config']['ciniki.core']['ssl']) 
		&& $ciniki['config']['ciniki.core']['ssl'] == 'off' 
		&& (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ) { 
		$verify_base_url = 'http://' . $_SERVER['HTTP_HOST'] . $ciniki['request']['base_url'] . '/signup/verify';
	} else {
		//  
		// Check if secure connection
		//  
		if( (isset($_SERVER['HTTP_CLUSTER_HTTPS']) && $_SERVER['HTTP_CLUSTER_HTTPS'] == 'on') 
			|| (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ) )  {
			$verify_base_url = 'https://' . $_SERVER['HTTP_HOST'] . $ciniki['request']['base_url'] . '/signup/verify';
		} else {
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}   
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$err = 0;
	$content = '';
	$page_content = '';
	$aside_content = '';
	$page_title = 'Sign Up';
	$display_page = 'form';		// Default to displaying the form

	$invalid_business_names = array('home', 'about', 'events', 'links', 'contact',
		'admin', 'manage', 'signup', 'api', 'documentation', 'logout', 'login', 'signin',
		'plans', 'plan', 'sysadmin', 'ciniki', 'ciniki-ca', 'ciniki ca',
		'downloads', 'exhibitions', 'exhibits', 'members', 'sponsors', 'studiotour', 'newsletters', 
		'mail', 'mailing', 'mailings', 'mailinglist', 'mailing-list', 'mailing list',
		);

	//
	// Error messages for form
	//
	$page_err = '';
	$plan_err = '';
	$firstname_err = '';
	$lastname_err = '';
	$business_name_err = '';
	$email_address_err = '';
	$username_err = '';
	$password_err = '';
	$useragrees_err = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Signup', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Grab the content required for the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-signup');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$page_details = $rc['content'];

	//
	// User clicked verification link, create the business, and display the success message
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'verify' ) {
		session_start();
		if( !isset($_SESSION['firstname']) 
			|| !isset($_SESSION['lastname']) 
			|| !isset($_SESSION['business_name']) 
			|| !isset($_SESSION['sitename']) 
			|| !isset($_SESSION['email_address']) 
			|| !isset($_SESSION['username']) 
			|| !isset($_SESSION['password']) 
			|| !isset($_SESSION['user_id']) 
			|| !isset($_SESSION['key']) 
			|| !isset($_SESSION['time']) 
			|| !isset($_SESSION['plan_monthly']) 
			|| !isset($_SESSION['plan_trial_days']) 
			|| !isset($_SESSION['plan_modules']) 
			) {
			$page_err = "Make sure you have cookies enabled, and try again.";
			$err = 1;
		}
		// Check the session is not older than 5 minutes
		if( $_SESSION['time'] < (time()-300) ) {
			error_log('WEB-ERR: Session timed out');
			$page_err = "I'm sorry, but for security reasons you did not complete this action in time.  Please start again.";
			$err = 2;
		}

		if( $_SESSION['key'] != $_GET['t'] ) {
			error_log('WEB-ERR: Session key miss-match ' . $_SESSION['key'] . '-' . $_GET['t']);
			$page_err = "I'm sorry, but we were unable to verify your email.  For security purposes, the link must be followed within 5 minutes of signing up.  <br/><br/>If you are still having difficulty, please email support at <a href=\"mailto:andrew@ciniki.ca\">andrew@ciniki.ca</a> and include your signup details.";
			$err = 3;
		}

		//  
		// Turn off autocommit
		//  
		if( $err == 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
			$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 23;
			}   
		}	

		//
		// Create user
		//
		if( $err == 0 && $_SESSION['user_id'] == 0 ) {
			$strsql = "INSERT INTO ciniki_users (uuid, date_added, email, username, firstname, lastname, display_name, "
				. "perms, status, timeout, password, temp_password, temp_password_date, last_updated) VALUES ("
				. "UUID(), "
				. "UTC_TIMESTAMP()" 
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['email_address']) . "'" 
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['username']) . "'" 
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['firstname']) . "'" 
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['lastname']) . "'" 
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['firstname'] . " " . $_SESSION['lastname'][0]) . "'" 
				. ", 0, 1, 0, "
				. "SHA1('" . ciniki_core_dbQuote($ciniki, $_SESSION['password']) . "'), "
				. "SHA1('" . ciniki_core_dbQuote($ciniki, '') . "'), "
				. "UTC_TIMESTAMP(), "
				. "UTC_TIMESTAMP())";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.users');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 25;
			} else {
				$user_id = $rc['insert_id'];
				$ciniki['session'] = array('user'=>array('id'=>$user_id), 'change_log_id'=>'SIGNUP');
			}
		} elseif( $err == 0 && $_SESSION['user_id'] > 0 ) {
			$user_id = $_SESSION['user_id'];
			$ciniki['session'] = array('user'=>array('id'=>$user_id), 'change_log_id'=>'SIGNUP');
		}

		//
		// Create business
		//
		$business_id = 0;
		if( $err == 0 ) {
			$strsql = "INSERT INTO ciniki_businesses (uuid, name, sitename, status, date_added, last_updated) VALUES ("
				. "UUID(), "
				. "'" . ciniki_core_dbQuote($ciniki, $_SESSION['business_name']) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $_SESSION['sitename']) . "' "
				. ", 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 22;
			}
			if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 4;
			} else {
				$business_id = $rc['insert_id'];
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.businesses', 'ciniki_business_history', $business_id, 
					1, 'ciniki_businesses', '', 'name', $_SESSION['business_name']);
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.businesses', 'ciniki_business_history', $business_id, 
					1, 'ciniki_businesses', '', 'sitename', $_SESSION['sitename']);
			}
		}

		//
		// Setup business contact
		//
		if( $err == 0 ) {
			$strsql = "INSERT INTO ciniki_business_details (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
				. "'contact.person.name', "
				. "'" . ciniki_core_dbQuote($ciniki, $_SESSION['firstname'] . " " . $_SESSION['lastname']) . "', "
				. "UTC_TIMESTAMP(), UTC_TIMESTAMP()) ";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 5;
			} else {
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.businesses', 'ciniki_business_history', $business_id, 
					1, 'ciniki_business_details', 'contact.person.name', 'detail_value', $_SESSION['firstname'] . " " . $_SESSION['lastname']);
			}
		}
		if( $err == 0 ) {
			$strsql = "INSERT INTO ciniki_business_details (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
				. "'contact.email.address', "
				. "'" . ciniki_core_dbQuote($ciniki, $_SESSION['email_address']) . "', "
				. "UTC_TIMESTAMP(), UTC_TIMESTAMP()) ";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 6;
			} else {
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.businesses', 'ciniki_business_history', $business_id, 
					1, 'ciniki_business_details', 'contact.email.address', 'detail_value', $_SESSION['email_address']);
			}
		}

		//
		// Add business owner
		//
		if( $err == 0 ) {
			$strsql = "INSERT INTO ciniki_business_users (business_id, user_id, "
				. "package, permission_group, status, date_added, last_updated) VALUES ("
				. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $user_id) . "' "
				. ", 'ciniki', 'owners', 10, UTC_TIMESTAMP(), UTC_TIMESTAMP())";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 21;
			} 
		}

		//
		// Enable modules
		//
		if( $err == 0 ) {
			$modules = preg_split('/,/', $_SESSION['plan_modules']);
			foreach($modules as $module) {
				$mod = preg_split('/\./', $module);
				$strsql = "INSERT INTO ciniki_business_modules (business_id, "
					. "package, module, status, ruleset, date_added, last_updated, last_change) VALUES ("
					. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
					. "'" . ciniki_core_dbQuote($ciniki, $mod[0]) . "', "
					. "'" . ciniki_core_dbQuote($ciniki, $mod[1]) . "', "
					. "1, '', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())";
				$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
				if( $rc['stat'] != 'ok' ) {
					ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
					$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
						. "you continue to have problems, contact support.";
					$err = 7;
				}
				//
				// Check if there is an initialization script for the module when the business is enabled
				//
				if( $mod[0] == 'ciniki' 
					&& file_exists($ciniki['config']['ciniki.core']['modules_dir'] . '/' . $mod[1] . '/private/moduleInitialize.php') 
					) {
					ciniki_core_loadMethod($ciniki, 'ciniki', '' . $mod[1] . '', 'private', 'moduleInitialize');
					$method_function = $mod[0] . '_' . $mod[1] . '_moduleInitialize';
					if( is_callable($method_function) ) {
						$method_function($ciniki, $business_id);
					}
				}
			}
		}

		//
		// Setup subscription
		//
		if( $err == 0 ) {
			$strsql = "INSERT INTO ciniki_business_subscriptions (business_id, status, "
				. "signup_date, trial_start_date, trial_days, currency, "
				. "monthly, discount_percent, discount_amount, payment_type, payment_frequency, "
				. "date_added, last_updated) VALUES ("
				. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
				. "2, UTC_TIMESTAMP(), UTC_TIMESTAMP(), '60', 'USD', "
				. "'" . ciniki_core_dbQuote($ciniki, $_SESSION['plan_monthly']) . "', "
				. "0, 0, 'paypal', 10, "
				. "UTC_TIMESTAMP(), UTC_TIMESTAMP())";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.businesses');
				$page_error = "We seem to have hit a snag, and were unable to setup your business.  Please try again and if "
					. "you continue to have problems, contact support.";
				$err = 8;
			} 
		}

		//
		// Commit, and display success message
		//
		if( $err == 0 ) {
			ciniki_core_dbTransactionCommit($ciniki, 'ciniki.businesses');

			//
			// Update the last_change date in the business modules
			// Ignore the result, as we don't want to stop user updates if this fails.
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
			ciniki_businesses_updateModuleChangeDate($ciniki, $business_id, 'ciniki', 'businesses');

			//
			// Email user welcome message
			//
			$subject = "Welcome to Ciniki";
			$msg = 'Thank you for choosing the Ciniki platform to manage your business.  '
				. 'We hope you have a pleasant experience and we look forward to your feedback.  '
				. 'Please use the "Help" feature to ask a question, report a bug, or request a feature.  '
				. 'You may also find that other users have already provided advice and suggestions that may be helpful.  '
				. 'The Ciniki platform is a community effort and everybody benefits when you contribute.  '
				. 'Thanks for doing your part to improve small business management.'
				. "\n\n"
				. "Please save this e-mail for future reference.  We've included some important information and links below."
				. "\n\n"
				. "Email: " . $_SESSION['email_address'] . "\n"
				. "Username: " . $_SESSION['username'] . "\n"
				. "Manage: " . $ciniki['config']['ciniki.core']['manage.url'] . "\n";
			if( preg_match('/ciniki\.web/', $_SESSION['plan_modules']) ) {
				$msg .= "Your website: http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $_SESSION['sitename'] . "\n";
			}
			$msg .= "\n\n";
			$ciniki['emailqueue'][] = array('to'=>$_SESSION['email_address'],
				'subject'=>$subject,
				'textmsg'=>$msg,
				);

			//
			// Email a notification to the owners of the master business
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
	
			$strsql = "SELECT user_id FROM ciniki_business_users "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['config']['ciniki.core']['master_business_id']) . "' "
				. "AND permission_group = 'owners' "
				. "";
			$rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.businesses', 'user_ids', 'user_id');
			if( $rc['stat'] == 'ok' ) {
				foreach($rc['user_ids'] as $uid) {
					// 
					// Don't email the submitter, they will get a separate email
					//
					if( $uid != $ciniki['session']['user']['id'] ) {
						$ciniki['emailqueue'][] = array('user_id'=>$uid,
							'subject'=>'Sign Up: ' . $_SESSION['business_name'],
							'textmsg'=>"New business added: " . $_SESSION['business_name'] . "\n"
								. "User: " . $_SESSION['firstname'] . " " . $_SESSION['lastname'] . "\n"
								. "Email: " . $_SESSION['email_address'] . "\n"
								. "\n\n"
								);
					}
				}
			}

			$page_title = 'Verification';
			if( isset($page_details['page-signup-success']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $page_details['page-signup-success']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
			$display_page = '';
		}
	}

	//
	// User submitted the form, store informatin in session, send verification email
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'submit' ) {
		$err = 0;
		//
		// Check for variables
		//
		if( !isset($_POST['plan']) || $_POST['plan'] == '' ) {
			$plan_err = 'You must choose a plan';
			$err = 9;
		}
		if( !isset($_POST['firstname']) || $_POST['firstname'] == '' ) {
			$firstname_err = 'You must specify a first name.';
			$err = 10;
		}
		if( !isset($_POST['lastname']) || $_POST['lastname'] == '' ) {
			$lastname_err = 'You must specify a last name.';
			$err = 11;
		}
		if( !isset($_POST['business_name']) || $_POST['business_name'] == '' || strlen($_POST['business_name']) < 2 ) {
			$business_name_err = 'You must specify a business name.';
			$err = 12;
		}
		// Check for invalid business names
		if( in_array(strtolower($_POST['business_name']), $invalid_business_names) ) {
			$business_name_err = "This business name is restricted, please choose another.";
			$err = 13;
		}
		$sitename = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_POST['business_name']));
		if( in_array($sitename, $invalid_business_names) ) {
			$business_name_err = "The business name you choose is restricted, please choose another.";
			$err = 14;
		}

		if( !isset($_POST['password']) || $_POST['password'] == '' || strlen($_POST['password']) < 8 ) {
			$password_err = 'yes';
			$err = 15;
		}

		if( $_POST['password'] != $_POST['password2'] ) {
			$password_err = "I'm sorry, but the passwords you entered did not match.";
			$err = 26;
		}

		if( !preg_match('/[0-9]/', $_POST['password']) ) {
			$password_err = 'yes';
			$err = 16;
		}

		if( !isset($_POST['useragrees']) || $_POST['useragrees'] != 'yes' ) {
			$useragrees_err = 'You must agree to the user agreement if you would like to sign up.';
			$err = 31;
		}

		//
		// Check the business name or sitename does not already exist
		//
		$strsql = "SELECT id "
			. "FROM ciniki_businesses "
			. "WHERE name = '" . ciniki_core_dbQuote($ciniki, $_POST['business_name']) . "' "
			. "OR sitename = '" . ciniki_core_dbQuote($ciniki, $sitename) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['business']) ) {
			$business_name_err = "We're sorry, but this business already exists.  Please choose another name";
			$err = 17;
		}

		//
		// Check if email address and/or username already exists, then check if password matches
		//
		if( $err == 0 ) {
			$strsql = "SELECT email, username "
				. "FROM ciniki_users "
				. "WHERE username = '" . ciniki_core_dbQuote($ciniki, $_POST['username']) . "' "
				. "OR email = '" . ciniki_core_dbQuote($ciniki, $_POST['email_address']) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'user');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$user_id = 0;
			if( isset($rc['user']) ) {
				// User exists, check if email different
				if( $rc['user']['email'] != $_POST['email_address'] ) {
					// Username matches, but email doesn't, they are trying to create a new account
					$username_err = 'Username already taken, please choose another.';
					$err = 18;
				}
				else {
					// email matches, doesn't matter if username matches, it will be ignored
					// check if password matches
					$strsql = "SELECT id,email, username "
						. "FROM ciniki_users "
						. "WHERE email = '" . ciniki_core_dbQuote($ciniki, $_POST['email_address']) . "' "
						. "AND password = SHA1('" . ciniki_core_dbQuote($ciniki, $_POST['password']) . "') "
						. "";
					$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'user');
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					// Password is not correct for account
					if( !isset($rc['user']) ) {
						$email_address_err = 'Email address is already setup in the system, please enter your account password.';
						$err = 19;
					} else {
						$user_id = $rc['user']['id'];
					}
				}
			}
		}

		//
		// Validate and load plan details
		//
		if( $err == 0 ) {
			session_start();
			$strsql = "SELECT id, name, monthly, trial_days, modules "
				. "FROM ciniki_business_plans "
				. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $_POST['plan']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['config']['ciniki.core']['master_business_id']) . "' "
				. "AND (flags&0x01) = 1 "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'plan');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( !isset($rc['plan']) ) {
				$plan_err = "Something went wrong with selecting a plan, please try again.";
				$err = 20;
			} else {
				$_SESSION['plan_monthly'] = $rc['plan']['monthly'];
				$_SESSION['plan_trial_days'] = $rc['plan']['trial_days'];
				$_SESSION['plan_modules'] = $rc['plan']['modules'];
			}
		}
		

		//
		// Setup the session, and send the email.
		// 
		if( $err == 0 ) {
			$_SESSION['firstname'] = $_POST['firstname'];
			$_SESSION['lastname'] = $_POST['lastname'];
			$_SESSION['business_name'] = $_POST['business_name'];
			$_SESSION['sitename'] = $sitename;
			$_SESSION['email_address'] = $_POST['email_address'];
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['password'] = $_POST['password'];
			$_SESSION['user_id'] = $user_id;
			$_SESSION['time'] = time();
			$_SESSION['key'] = md5(date('Y-m-d-H-i-s') . rand());	
			session_write_close();
			$verify_url = $verify_base_url . "?t=" . $_SESSION['key'];
			
			//
			// Send email to user
			//
			$subject = "Email verification";
			$msg = "Please click on the following link to verify your email address and your business will be setup in Ciniki.  "
				. "\n\n"
//				. "<a href='$verify_url'>$verify_url</a>";
				. "$verify_url"
				. "\n\n";
			$ciniki['emailqueue'][] = array('to'=>$_SESSION['email_address'],
				'subject'=>$subject,
				'textmsg'=>$msg,
				);

		}

		//
		// If no errors, then display the success message
		//
		if( $err == 0 ) {
			$page_title = 'Submitted';
			if( isset($page_details['page-signup-submit']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $page_details['page-signup-submit']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content = $rc['content'];
			}
			$display_page = '';		// Stop the display of the form
		}
	}

	//
	// Generate the standard content of the page, with the signup form
	//
	if( $display_page == 'form' ) {
		//
		// Generate the content of the page
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-signup');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = "<form action='" . $ciniki['request']['base_url'] . "/signup/submit' method='post'>\n";

		if( isset($page_details['page-signup-content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $page_details['page-signup-content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$aside_content .= "<b class='entry-title'>Requirements</b>" . $rc['content'];
		}
		if( isset($page_details['page-signup-agreement']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $page_details['page-signup-agreement']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$aside_content .= "<b class='entry-title'>User Agreement</b>" . $rc['content'];
		}

		// 
		// Grab submitted information incase there was a form error, we can keep it filled in
		//
		$selected_plan = 0;
		if( isset($_POST['plan']) ) { $selected_plan = $_POST['plan']; }
		$firstname = '';
		if( isset($_POST['firstname']) ) { $firstname = $_POST['firstname']; }
		$lastname = '';
		if( isset($_POST['lastname']) ) { $lastname = $_POST['lastname']; }
		$business_name = '';
		if( isset($_POST['business_name']) ) { $business_name = $_POST['business_name']; }
		$sitename = '';
		if( isset($_POST['sitename']) ) { $sitename = $_POST['sitename']; }
		$email_address = '';
		if( isset($_POST['email_address']) ) { $email_address = $_POST['email_address']; }
		$username = '';
		if( isset($_POST['username']) ) { $username = $_POST['username']; }
		$useragrees = '';
		if( isset($_POST['useragrees']) ) { $useragrees = $_POST['useragrees']; }

		//
		// Check for a page error
		//
		if( $page_err != '' ) {
			$page_content .= "<p class='pageerror'>$page_err  (E:$err)</p>";
		}

		//
		// Get the list of plans from the database and display, allowing user to select one to signup for
		//
		$page_content .= "<b class='entry-title'>Choose a plan</b>";
		$strsql = "SELECT id, name, description, monthly, trial_days "
			. "FROM ciniki_business_plans "
			. "WHERE (flags&0x01) = 1 "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['config']['ciniki.core']['master_business_id']) . "' "
			. "ORDER BY name "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
		$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.businesses', 'plans', 'id');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $plan_err != '' ) {
			$page_content .= "<p class='formerror'>$plan_err</p>";
		}
		$page_content .= "<dl>";
		foreach($rc['plans'] as $plan_id => $plan) {
			$page_content .= "<dt><input type='radio' name='plan' value='$plan_id' ";
			if( count($rc['plans']) == 1 || $plan_id == $selected_plan) { $page_content .= " checked"; }
			$page_content .= "/> " . $plan['name'] . " ($" . $plan['monthly'] . "/month)</dt>"
				. "<dd>" . $plan['description'] . "</dd>"; 
		}
		$page_content .= "</dl>";

		$page_content .= "<b class='entry-title'>Your information</b>";
	// Form fields: first_name, last_name, business_name, sitename, email_address, username, password, password_again
		$page_content .= "<div class='input'><label for='firstname'>First Name</label><input type='text' class='text' name='firstname' value='$firstname'>";
		if( $firstname_err != '' ) {
			$page_content .= "<p class='formerror'>$firstname_err</p>";
		}
		$page_content .= "</div>";
		$page_content .= "<div class='input'><label for='lastname'>Last Name</label><input type='text' class='text' name='lastname' value='$lastname'>";
		if( $lastname_err != '' ) {
			$page_content .= "<p class='formerror'>$lastname_err</p>";
		}
		$page_content .= "</div>";
		$page_content .= "<div class='input'><label for='business_name'>Business Name</label><input type='text' class='text' name='business_name' value='$business_name'>";
		if( $business_name_err != '' ) {
			$page_content .= "<p class='formerror'>$business_name_err</p>";
		}
		$page_content .= "<p class='formhelp'>If you don't have a business name, just use your first and last name.</p></div>";
//		$page_content .= "<label for='sitename'>Site Name</label><input type='text' class='text' name='sitename' value='$sitename'>";
		$page_content .= "<div class='input'><label for='email_address'>Email</label><input type='text' class='text' name='email_address' value='$email_address'>";
		if( $email_address_err != '' ) {
			$page_content .= "<p class='formerror'>$email_address_err</p>";
		}
		$page_content .= "</div>";
		$page_content .= "<div class='input'><label for='username'>Username</label><input type='text' class='text' name='username' value='$username'>";
		if( $username_err != '' ) {
			$page_content .= "<p class='formerror'>$username_err</p>";
		}
		$page_content .= "</div>";
		$page_content .= "<div class='input'><label for='password'>Password</label><input type='password' class='text' name='password' value=''>";
		if( $password_err != '' && $password_err != 'yes' ) {
			$page_content .= "<p class='formerror'>$password_err</p>";
		}
		if( $password_err != '' && $password_err == 'yes' ) {
			$page_content .= "<p class='formerror'>";
		} else {
			$page_content .= "<p class='formhelp'>";
		}
		$page_content .= "Password must be at least 8 characters, and contain 1 number.  This password will protect your business information, so longer and more cryptic is better.</p>";
		$page_content .= "</div>";
		$page_content .= "<div class='input'><label for='password2'>Retype Password</label><input type='password' class='text' name='password2' value=''>";
		$page_content .= "<label for='useragrees'></label><input type='checkbox' class='' name='useragrees' value='yes'";
		if( $useragrees == 'yes' ) { $page_content .= " checked"; }
		$page_content .= "> I agree to the User Agreement.";
		if( $useragrees_err != '' ) {
			$page_content .= "<p class='formerror'>$useragrees_err</p>";
		}
		$page_content .= "</div>";

		// Submit button
		$page_content .= "<div class='bigsubmit'><input type='submit' class='bigsubmit' name='signup' value='Sign up'></div>";

	}

	$content .= "<div id='content' class='evensplit'>\n"
		. "<article class='page'>\n"
		. "<aside>$aside_content</aside>"
//		. "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
		. "<div class='entry-content'>\n"
		. $page_content
		. "</div>"
		. "</article>"
		. "</div>"
		. "";
	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
