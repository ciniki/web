<?php
//
// Description
// -----------
// This method will update any valid page settings and content in the database.
//
// The contact display values are taken from the business settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:							The ID of the business to update the settings for.
// page-home-active:					(optional) Display the home page (yes or no)
// page-about-active:					(optional) Display the about page (yes or no)
// page-about-image:					(optional) The image_id from the ciniki images module to be displayed on the about page.
// page-exhibitions-exhibition:			(optional) The ID of the exhibition to be used for exhibitors and sponsors.  This should be the currently active exhibition.
// page-exhibitions-exhibitors-active:	(optional) Display the exhibitors page (yes or no)
// page-exhibitions-sponsors-active:	(optional) Display the sponsors page (yes or no)
// page-gallery-active:					(optional) Display the gallery page (yes or no)
// page-events-active:					(optional) Display the events page (yes or no)
// page-events-past:					(optional) Display the past events (yes or no)
// page-links-active:					(optional) Display the links page (yes or no)
// page-contact-active:					(optional) Display the contact page (yes or no)
// page-contact-business-name-display:	(optional) Display the business name as part of the contact info (yes or no)
// page-contact-person-name-display:	(optional) Display the business contact person name (yes or no)
// page-contact-address-display:		(optional) Display the business address (yes or no)
// page-contact-phone-display:			(optional) Display the business phone number (yes or no)
// page-contact-fax-display:			(optional) Display the business fax number (yes or no)
// page-contact-email-display:			(optional) Display the business email address (yes or no)
// page-downloads-active:				(optional) Display the download page (yes or no)
// page-downloads-name:					(optional) The name to be used in the menu for the downloads page.  eg (Reports, Newletters, etc)
// page-account-active:					(optional) Allow customers to login and display an account page (yes or no)
// page-signup-active:					(optional) Display a signup page, only valid for master business (ciniki.com)
// page-api-active:						(optional) Display api documentation, only valid for master business (ciniki.com)
// site-theme:							(optional) The theme to use for the website.  (default, black)
// site-header-image:					(optional) The ID of the image from the ciniki images module to display in the site header.
// site-header-title:					(optional) Display the business name and tagline.  Allows user to turn off if they have a header image as logo.
// site-logo-display:					(optional) Display the business logo in the site header (yes or no)
// site-google-analytics-account:		(optional) The google account code for google analytics.
// site-featured:						(optional) Display the site name as a featured site on the master business homepage (ciniki.com)
// page-home-content:					(optional) The content to be displayed on the home page.
// page-about-content:					(optional) The content to be displayed on the about page.
// page-contact-content:				(optional) The content to be displayed on the contact page.
// page-signup-content:					(optional) The content to be displayed on the signup page.
// page-signup-agreement:				(optional) The content of the signup agreement statement.
// page-signup-submit:					(optional) The content to be displayed after the submission on the signup page.
// page-signup-success:					(optional) The content to be displayed after sucessful signup.
// page-account-content:				(optional) The content to be displayed on the account page.
// page-account-content-subscriptions:	(optional) The content to be displayed on the account page for subscriptions.
//
// Returns
// -------
// <rsp stat="ok" />
//
function ciniki_web_siteSettingsUpdate(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// The list of valid settings for web pages
	//
	$settings_fields = array(
		'page-home-active',
		'page-home-slider',
		'page-home-gallery-latest',
		'page-home-gallery-latest-title',
		'page-home-gallery-random',
		'page-home-gallery-random-title',
		'page-home-latest-recipes',
		'page-home-upcoming-events',
		'page-home-upcoming-workshops',
		'page-home-upcoming-artgalleryexhibitions',
		'page-home-image',
		'page-home-image-caption',
		'page-home-image-url',
		'page-home-url',		// Used if different from home of Ciniki hosted website, 
								// used to redirect back to main site for subdomains.
		'page-about-active',
		'page-about-history-active',
		'page-about-artiststatement-active',
		'page-about-cv-active',
		'page-about-awards-active',
		'page-about-history-active',
		'page-about-donations-active',
		'page-about-membership-active',
		'page-about-boardofdirectors-active',
		'page-about-facilities-active',
		'page-about-warranty-active',
		'page-about-testimonials-active',
		'page-about-reviews-active',
		'page-about-greenpolicy-active',
		'page-about-whyus-active',
		'page-about-privacypolicy-active',
//		'page-abouthistory-active',
//		'page-abouthistory-image',
//		'page-abouthistory-image-caption',
//		'page-aboutdonations-active',
//		'page-aboutdonations-image',
//		'page-aboutdonations-image-caption',
//		'page-aboutboardofdirectors-active',
//		'page-aboutboardofdirectors-image',
//		'page-aboutboardofdirectors-image-caption',
//		'page-aboutmembership-active',
//		'page-aboutmembership-image',
//		'page-aboutmembership-image-caption',
//		'page-aboutartiststatement-active',
//		'page-aboutartiststatement-image',
//		'page-aboutartiststatement-image-caption',
//		'page-aboutcv-active',
//		'page-aboutcv-image',
//		'page-aboutcv-image-caption',
//		'page-aboutawards-active',
//		'page-aboutawards-image',
//		'page-aboutawards-image-caption',
//		'page-about-image',
//		'page-about-image-caption',
		'page-about-business-name-display',
		'page-about-person-name-display',
		'page-about-address-display',
		'page-about-phone-display',
		'page-about-fax-display',
		'page-about-email-display',
		'page-about-bios-title',					// What is the title to display in the page
		'page-about-bios-display',					// How the bios should be display on the about page.
		'page-features-active',
		'page-artgalleryexhibitions-image',
		'page-artgalleryexhibitions-image-caption',
		'page-artgalleryexhibitions-active',
		'page-artgalleryexhibitions-past',
		'page-artgalleryexhibitions-application-details',
		'page-blog-active',
		'page-blog-name',
		'page-memberblog-active',
		'page-memberblog-name',
		'page-exhibitions-exhibition',
		'page-exhibitions-exhibitors-active',
		'page-exhibitions-sponsors-active',
		'page-exhibitions-tourexhibitors-active',
		'page-exhibitions-tourexhibitors-name',
		'page-products-active',
		'page-products-name',
		'page-recipes-active',
		'page-recipes-name',
		'page-recipes-tags',
		'page-gallery-active',
		'page-gallery-name',
		'page-gallery-artcatalog-format',			// Split the menu into types
		'page-gallery-artcatalog-split',			// Split the menu into types
		'page-gallery-artcatalog-paintings',			// Split the menu into types
		'page-gallery-artcatalog-photographs',			// Split the menu into types
		'page-gallery-artcatalog-jewelry',			// Split the menu into types
		'page-gallery-artcatalog-sculptures',			// Split the menu into types
		'page-gallery-share-buttons',			// Share buttons for facebook, twitter, etc
		'page-courses-active',
		'page-courses-name',
		'page-courses-image',
		'page-courses-image-caption',
		'page-courses-image-url',
		'page-courses-upcoming-active',
		'page-courses-upcoming-name',
		'page-courses-current-active',
		'page-courses-current-name',
		'page-courses-past-active',
		'page-courses-past-name',
		'page-courses-catalog-download-active',
		'page-courses-registration-active',
		'page-courses-registration-image',
		'page-courses-registration-image-caption',
		'page-members-active',
		'page-members-membership-details',
		'page-members-list-format',
		'page-members-categories-display',
		'page-members-name',
		'page-sponsors-active',
		'page-newsletters-active',
		'page-surveys-active',
		'page-workshops-active',
		'page-workshops-past',
		'page-events-active',
		'page-events-past',
		'page-directory-active',
		'page-links-active',
		'page-contact-active',
		'page-contact-google-map',
		'page-contact-map-latitude',
		'page-contact-map-longitude',
		'page-contact-business-name-display',
		'page-contact-person-name-display',
		'page-contact-address-display',
		'page-contact-phone-display',
		'page-contact-fax-display',
		'page-contact-email-display',
		'page-contact-bios-display',					// How the bios should be display on the contact page.
		'page-downloads-active',
		'page-downloads-name',
		'page-account-active',
		'page-account-signin-redirect',
		'page-cart-active',
		'page-signup-active',
		'page-signup-menu',
		'page-api-active',
		'page-faq-active',
		'site-theme',
		'site-layout',
		'site-header-image',
		'site-header-title',
//		'site-logo-display',
		'site-google-analytics-account',
		'site-featured',
		'site-custom-css',
		'site-social-facebook-header-active',
		'site-social-facebook-footer-active',
		'site-social-twitter-header-active',
		'site-social-twitter-footer-active',
		'site-social-flickr-header-active',
		'site-social-flickr-footer-active',
		'site-social-pinterest-header-active',
		'site-social-pinterest-footer-active',
		'site-social-etsy-header-active',
		'site-social-etsy-footer-active',
		'site-social-tumblr-header-active',
		'site-social-tumblr-footer-active',
		'site-social-youtube-header-active',
		'site-social-youtube-footer-active',
		'site-social-vimeo-header-active',
		'site-social-vimeo-footer-active',
		'site-social-instagram-header-active',
		'site-social-instagram-footer-active',
		'site-social-email-header-active',
		'site-social-email-footer-active',
		);

	//
	// The list of valid content for web pages
	//
	$content_fields = array(
		'page-home-content',
		'page-about-content',
//		'page-abouthistory-content',
//		'page-aboutdonations-content',
//		'page-aboutboardofdirectors-content',
//		'page-aboutartiststatement-content',
//		'page-aboutcv-content',
//		'page-aboutawards-content',
		'page-contact-content',
		'page-courses-content',
		'page-signup-content',
		'page-signup-agreement',
		'page-signup-submit',
		'page-signup-success',
		'page-account-content',
		'page-account-content-subscriptions',
		'page-artgalleryexhibitions-content',
		);

	//
	// Check access to business_id as owner, and load module list
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.siteSettingsUpdate');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}
	$modules = $ac['modules'];

	//
	// Grab the existing settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id',
		$args['business_id'], 'ciniki.web', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		$settings = array();
	} else {
		$settings = $rc['settings'];
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Add any dynamic setting/content field names. The must be lowercase, and reduced to only letters/numbers
	//
	if( isset($modules['ciniki.courses']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'courseTypes');
		$rc = ciniki_courses_web_courseTypes($ciniki, $settings, $args['business_id']);
		if( isset($rc['types']) ) {
			foreach($rc['types'] as $type_name => $type ) {
				$name = preg_replace('/[^a-z0-9]/', '', strtolower($type_name));
				array_push($settings_fields, 'page-courses-' . $name . '-image');
				array_push($settings_fields, 'page-courses-' . $name . '-image-caption');
				array_push($content_fields, 'page-courses-' . $name . '-content');
			}
		}
	}

	//
	// **** Settings ****
	//
	
	//
	// Check if the field was passed, and then try an insert, but if that fails, do an update
	//
	foreach($settings_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 
				2, 'ciniki_web_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
			$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
				'args'=>array('id'=>$field));

			//
			// Check for image updates
			//
			if( ($field == 'page-home-image' 
					|| $field == 'page-about-image' 
					|| $field == 'site-header-image' )
				&& (!isset($settings[$field]) 
					|| $settings[$field] != $ciniki['request']['args'][$field] )
				) {
				if( isset($settings[$field]) && $settings[$field] != '0' ) {
					//
					// Remove the old reference
					//
					ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefClear');
					$rc = ciniki_core_objectRefClear($ciniki, $args['business_id'], 'ciniki.images.image', array(
						'object'=>'ciniki.web.setting', 
						'object_id'=>$field));
					if( $rc['stat'] == 'fail' ) {
						ciniki_core_dbTransactionRollback($ciniki, 'ciniki.gallery');
						return $rc;
					}
				} 
				if( $ciniki['request']['args'][$field] != '0' && $ciniki['request']['args'][$field] != '' ) {
					//
					// Add the new reference
					//
					ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefAdd');
					$rc = ciniki_core_objectRefAdd($ciniki, $args['business_id'], 'ciniki.images.image', array(
						'ref_id'=>$ciniki['request']['args'][$field], 
						'object'=>'ciniki.web.setting', 
						'object_id'=>$field,
						'object_field'=>'detail_value'));
					if( $rc['stat'] != 'ok' ) {
						ciniki_core_dbTransactionRollback($ciniki, 'ciniki.gallery');
						return $rc;
					}
				}
			}
		}
	}

	//
	// Check for page-custom fields
	//
	foreach($ciniki['request']['args'] as $field => $field_value ) {
		// page-custom-001-active
		// page-custom-001-name
		// page-custom-001-parent
		// page-custom-001-permalink
		// page-custom-001-image
		// page-custom-001-image-caption
		if( preg_match('/^page-custom-([0-9][0-9][0-9])-(active|name|title|parent|permalink|image|image-caption|content)$/', $field, $matches) == 1 ) {
			$page_number = $matches[1];
			$page_name = $matches[2];
			if( $page_name == 'content' ) {
				$strsql = "INSERT INTO ciniki_web_content (business_id, detail_key, detail_value, date_added, last_updated) "
					. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
					. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
					. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
					. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
					. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
					. ", last_updated = UTC_TIMESTAMP() "
					. "";
				$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
				if( $rc['stat'] != 'ok' ) {
					ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
					return $rc;
				}
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 
					2, 'ciniki_web_content', $field, 'detail_value', $ciniki['request']['args'][$field]);
				$ciniki['syncqueue'][] = array('push'=>'ciniki.web.content',
					'args'=>array('id'=>$field));
			} else {
				$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
					. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
					. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
					. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
					. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
					. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
					. ", last_updated = UTC_TIMESTAMP() "
					. "";
				$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
				if( $rc['stat'] != 'ok' ) {
					ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
					return $rc;
				}
				ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 
					2, 'ciniki_web_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
				$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
					'args'=>array('id'=>$field));
			}


			//
			// Check for image updates
			//
			if( $page_name == 'image' && (!isset($settings[$field]) || $settings[$field] != $ciniki['request']['args'][$field]) ) {
				if( isset($settings[$field]) && $settings[$field] != '0' ) {
					//
					// Remove the old reference
					//
					ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefClear');
					$rc = ciniki_core_objectRefClear($ciniki, $args['business_id'], 'ciniki.images.image', array(
						'object'=>'ciniki.web.setting', 
						'object_id'=>$field));
					if( $rc['stat'] == 'fail' ) {
						ciniki_core_dbTransactionRollback($ciniki, 'ciniki.gallery');
						return $rc;
					}
				} 
				if( $ciniki['request']['args'][$field] != '0' && $ciniki['request']['args'][$field] != '' ) {
					//
					// Add the new reference
					//
					ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefAdd');
					$rc = ciniki_core_objectRefAdd($ciniki, $args['business_id'], 'ciniki.images.image', array(
						'ref_id'=>$ciniki['request']['args'][$field], 
						'object'=>'ciniki.web.setting', 
						'object_id'=>$field,
						'object_field'=>'detail_value'));
					if( $rc['stat'] != 'ok' ) {
						ciniki_core_dbTransactionRollback($ciniki, 'ciniki.gallery');
						return $rc;
					}
				}
			}
		}

		//
		// Check for triggers by changing settings
		//
		if( $field == 'page-members-list-format' ) {
			$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'settingChange');
			if( $rc['stat'] == 'ok' ) {
				$rc = ciniki_customers_web_settingChange($ciniki, $args['business_id'], $field, $field_value);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			}
		}
	}

	$user_prefix_fields = array(
		'page-contact-user-display-flags',
		'page-about-user-display-flags',
		);
	//
	// Check the list of business users to see if their information should be displayed on the website
	//
	$strsql = "SELECT DISTINCT ciniki_business_users.user_id AS id "
		. "FROM ciniki_business_users "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'user');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) ) {
		$users = $rc['rows'];
		foreach($users as $unum => $user) {
			$uid = $user['id'];
			foreach($user_prefix_fields as $field) {
				$field .= "-$uid";
				if( isset($ciniki['request']['args'][$field]) ) {
					$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
						. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
						. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
						. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
						. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
						. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
						. ", last_updated = UTC_TIMESTAMP() "
						. "";
					$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
					if( $rc['stat'] != 'ok' ) {
						ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
						return $rc;
					}
					ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 
						2, 'ciniki_web_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
					$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
						'args'=>array('id'=>$field));
				}
			}
		}
		//
		// Update the page-contact-user-display field
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'updateUserDisplay');
		$rc = ciniki_web_updateUserDisplay($ciniki, $args['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
	}

	//
	// **** Content ****
	//

	//
	// Check if the field was passed, and then try an insert, but if that fails, do an update
	//
	foreach($content_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			$strsql = "INSERT INTO ciniki_web_content (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 
				2, 'ciniki_web_content', $field, 'detail_value', $ciniki['request']['args'][$field]);
			$ciniki['syncqueue'][] = array('push'=>'ciniki.web.content',
				'args'=>array('id'=>$field));
		}
	}

	//
	// Commit the changes to the database
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

	return array('stat'=>'ok');
}
?>
