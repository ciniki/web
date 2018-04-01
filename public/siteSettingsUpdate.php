<?php
//
// Description
// -----------
// This method will update any valid page settings and content in the database.
//
// The contact display values are taken from the tenant settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:                         The ID of the tenant to update the settings for.
// page-home-active:                    (optional) Display the home page (yes or no)
// page-about-active:                   (optional) Display the about page (yes or no)
// page-about-image:                    (optional) The image_id from the ciniki images module to be displayed on the about page.
// page-exhibitions-exhibition:         (optional) The ID of the exhibition to be used for exhibitors and sponsors.  This should be the currently active exhibition.
// page-exhibitions-exhibitors-active:  (optional) Display the exhibitors page (yes or no)
// page-exhibitions-sponsors-active:    (optional) Display the sponsors page (yes or no)
// page-gallery-active:                 (optional) Display the gallery page (yes or no)
// page-events-active:                  (optional) Display the events page (yes or no)
// page-events-past:                    (optional) Display the past events (yes or no)
// page-links-active:                   (optional) Display the links page (yes or no)
// page-contact-active:                 (optional) Display the contact page (yes or no)
// page-contact-tenant-name-display:  (optional) Display the tenant name as part of the contact info (yes or no)
// page-contact-person-name-display:    (optional) Display the tenant contact person name (yes or no)
// page-contact-address-display:        (optional) Display the tenant address (yes or no)
// page-contact-phone-display:          (optional) Display the tenant phone number (yes or no)
// page-contact-fax-display:            (optional) Display the tenant fax number (yes or no)
// page-contact-email-display:          (optional) Display the tenant email address (yes or no)
// page-downloads-active:               (optional) Display the download page (yes or no)
// page-downloads-name:                 (optional) The name to be used in the menu for the downloads page.  eg (Reports, Newletters, etc)
// page-account-active:                 (optional) Allow customers to login and display an account page (yes or no)
// page-signup-active:                  (optional) Display a signup page, only valid for master tenant (ciniki.com)
// page-api-active:                     (optional) Display api documentation, only valid for master tenant (ciniki.com)
// site-theme:                          (optional) The theme to use for the website.  (default, black)
// site-header-image:                   (optional) The ID of the image from the ciniki images module to display in the site header.
// site-header-title:                   (optional) Display the tenant name and tagline.  Allows user to turn off if they have a header image as logo.
// site-logo-display:                   (optional) Display the tenant logo in the site header (yes or no)
// site-google-analytics-account:       (optional) The google account code for google analytics.
// site-featured:                       (optional) Display the site name as a featured site on the master tenant homepage (ciniki.com)
// page-home-content:                   (optional) The content to be displayed on the home page.
// page-about-content:                  (optional) The content to be displayed on the about page.
// page-contact-content:                (optional) The content to be displayed on the contact page.
// page-signup-content:                 (optional) The content to be displayed on the signup page.
// page-signup-agreement:               (optional) The content of the signup agreement statement.
// page-signup-submit:                  (optional) The content to be displayed after the submission on the signup page.
// page-signup-success:                 (optional) The content to be displayed after sucessful signup.
// page-account-content:                (optional) The content to be displayed on the account page.
// page-account-content-subscriptions:  (optional) The content to be displayed on the account page for subscriptions.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
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
        'page-home-seo-title',
        'page-home-seo-description',
        'page-home-slider',
        'page-home-gallery-slider-type',
        'page-home-gallery-slider-size',
        'page-home-gallery-slider-title',
        'page-home-gallery-latest',
        'page-home-gallery-latest-title',
        'page-home-gallery-random',
        'page-home-gallery-random-title',
        'page-home-membergallery-slider-type',
        'page-home-membergallery-slider-size',
        'page-home-membergallery-latest',
        'page-home-membergallery-latest-title',
        'page-home-membergallery-random',
        'page-home-membergallery-random-title',
        'page-home-latest-blog',
        'page-home-latest-blog-title',
        'page-home-latest-blog-more',
        'page-home-latest-blog-number',
        'page-home-recipes-latest',
        'page-home-recipes-latest-title',
        'page-home-recipes-latest-more',
        'page-home-recipes-latest-number',
        'page-home-products-latest',
        'page-home-products-latest-title',
        'page-home-products-latest-more',
        'page-home-products-latest-number',
        'page-home-current-events',
        'page-home-current-events-title',
        'page-home-current-events-more',
        'page-home-current-events-number',
        'page-home-upcoming-events',
        'page-home-upcoming-events-title',
        'page-home-upcoming-events-more',
        'page-home-upcoming-events-number',
        'page-home-upcoming-workshops',
        'page-home-upcoming-workshops-title',
        'page-home-upcoming-workshops-more',
        'page-home-upcoming-workshops-number',
        'page-home-current-artgalleryexhibitions',
        'page-home-current-artgalleryexhibitions-title',
        'page-home-current-artgalleryexhibitions-more',
        'page-home-current-artgalleryexhibitions-number',
        'page-home-upcoming-artgalleryexhibitions',
        'page-home-upcoming-artgalleryexhibitions-title',
        'page-home-upcoming-artgalleryexhibitions-more',
        'page-home-upcoming-artgalleryexhibitions-number',
        'page-home-writings-covers',
        'page-home-writings-covers-title',
        'page-home-image',
        'page-home-image-caption',
        'page-home-image-url',
        'page-home-image2',
        'page-home-image2-caption',
        'page-home-image2-url',
        'page-home-menu-title',      // Title for menu<head><title>
        'page-home-title',      // Title used on homepage, but not in <head><title>
        'page-home-url',        // Used if different from home of Ciniki hosted website, 
                                // used to redirect back to main site for subdomains.
        'page-home-collections-display',
        'page-home-collections-title',
        'page-home-quicklinks-title',
        'page-home-quicklinks-001-name',
        'page-home-quicklinks-001-url',
        'page-home-quicklinks-002-name',
        'page-home-quicklinks-002-url',
        'page-home-quicklinks-003-name',
        'page-home-quicklinks-003-url',
        'page-home-quicklinks-004-name',
        'page-home-quicklinks-004-url',
        'page-home-quicklinks-005-name',
        'page-home-quicklinks-005-url',
        'page-home-quicklinks-006-name',
        'page-home-quicklinks-006-url',
        'page-home-quicklinks-007-name',
        'page-home-quicklinks-007-url',
        'page-home-quicklinks-008-name',
        'page-home-quicklinks-008-url',
        'page-home-quicklinks-009-name',
        'page-home-quicklinks-009-url',
        'page-home-content-layout',
        'page-home-number-photos',
        'page-home-content-sequence',
        'page-home-gallery-slider-sequence',
        'page-home-gallery-sequence',
        'page-home-membergallery-sequence',
        'page-home-blog-sequence',
        'page-home-events-sequence',
        'page-about-active',
        'page-about-title',
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
        'page-about-volunteer-active',
        'page-about-rental-active',
        'page-about-financialassistance-active',
        'page-about-artists-active',
        'page-about-employment-active',
        'page-about-staff-active',
        'page-about-sponsorship-active',
        'page-about-jobs-active',
        'page-about-extended-bio-active',
        'page-about-committees-active',
        'page-about-bylaws-active',
        'page-info-active',
        'page-info-title',
        'page-info-defaultcontenttype',
        'page-info-history-active',
        'page-info-artiststatement-active',
        'page-info-cv-active',
        'page-info-awards-active',
        'page-info-history-active',
        'page-info-donations-active',
        'page-info-membership-active',
        'page-info-boardofdirectors-active',
        'page-info-facilities-active',
        'page-info-warranty-active',
        'page-info-testimonials-active',
        'page-info-reviews-active',
        'page-info-greenpolicy-active',
        'page-info-whyus-active',
        'page-info-privacypolicy-active',
        'page-info-volunteer-active',
        'page-info-rental-active',
        'page-info-financialassistance-active',
        'page-info-artists-active',
        'page-info-employment-active',
        'page-info-staff-active',
        'page-info-sponsorship-active',
        'page-info-jobs-active',
        'page-info-committees-active',
        'page-info-bylaws-active',
//      'page-abouthistory-active',
//      'page-abouthistory-image',
//      'page-abouthistory-image-caption',
//      'page-aboutdonations-active',
//      'page-aboutdonations-image',
//      'page-aboutdonations-image-caption',
//      'page-aboutboardofdirectors-active',
//      'page-aboutboardofdirectors-image',
//      'page-aboutboardofdirectors-image-caption',
//      'page-aboutmembership-active',
//      'page-aboutmembership-image',
//      'page-aboutmembership-image-caption',
//      'page-aboutartiststatement-active',
//      'page-aboutartiststatement-image',
//      'page-aboutartiststatement-image-caption',
//      'page-aboutcv-active',
//      'page-aboutcv-image',
//      'page-aboutcv-image-caption',
//      'page-aboutawards-active',
//      'page-aboutawards-image',
//      'page-aboutawards-image-caption',
//      'page-about-image',
//      'page-about-image-caption',
        'page-about-tenant-name-display',
        'page-about-person-name-display',
        'page-about-address-display',
        'page-about-phone-display',
        'page-about-fax-display',
        'page-about-email-display',
        'page-about-bios-title',                    // What is the title to display in the page
        'page-about-bios-display',                  // How the bios should be display on the about page.
        'page-features-active',
        'page-artgalleryexhibitions-image',
        'page-artgalleryexhibitions-image-caption',
        'page-artgalleryexhibitions-active',
        'page-artgalleryexhibitions-past',
        'page-artgalleryexhibitions-initial-number',
        'page-artgalleryexhibitions-archive-number',
        'page-artgalleryexhibitions-application-details',
        'page-propertyrentals-active',
        'page-propertyrentals-name',
        'page-propertyrentals-rented',
        'page-jiji-active',
        'page-jiji-name',
        'page-blog-active',
        'page-blog-name',
        'page-blog-share-buttons',
        'page-blog-submenu',
        'page-blog-sidebar',
        'page-blog-list-image-version',
        'page-blog-more-button-text',
        'page-blog-thumbnail-format',
        'page-blog-thumbnail-padding-color',
        'page-blog-num-past-months',
        'page-memberblog-active',
        'page-memberblog-menu-active',
        'page-memberblog-name',
        'page-memberblog-num-past-months',
        'page-exhibitions-exhibition',
        'page-exhibitions-exhibitors-active',
        'page-exhibitions-exhibitors-name',
        'page-exhibitions-sponsors-active',
        'page-exhibitions-tourexhibitors-active',
        'page-exhibitions-tourexhibitors-name',
        'page-herbalist-active',
        'page-herbalist-name',
        'page-herbalist-share-buttons',
        'page-products-active',
        'page-products-name',
        'page-products-share-buttons',
        'page-products-categories-format',
        'page-products-categories-size',
        'page-products-subcategories-size',
        'page-products-thumbnail-format',
        'page-products-thumbnail-padding-color',
        'page-pdfcatalogs-active',
        'page-pdfcatalogs-name',
        'page-pdfcatalogs-thumbnail-format',
        'page-pdfcatalogs-thumbnail-padding-color',
        'page-products-path',
        'page-recipes-active',
        'page-recipes-name',
        'page-recipes-tags',
        'page-patents-active',
        'page-patents-name',
        'page-patents-share-buttons',
        'page-gallery-active',
        'page-gallery-name',
        'page-gallery-image-quality',
        'page-gallery-image-size',
        'page-gallery-artcatalog-format',           // Split the menu into types
        'page-gallery-artcatalog-split',            // Split the menu into types
        'page-gallery-artcatalog-paintings',            // Split the menu into types
        'page-gallery-artcatalog-photographs',          // Split the menu into types
        'page-gallery-artcatalog-jewelry',          // Split the menu into types
        'page-gallery-artcatalog-sculptures',           // Split the menu into types
        'page-gallery-artcatalog-printmaking',          // Split the menu into types
        'page-gallery-album-sort',          // How the albums should be sorted for website
        'page-gallery-share-buttons',           // Share buttons for facebook, twitter, etc
        'page-writings-active',
        'page-writings-name',
        'page-classes-active',
        'page-classes-name',
        'page-classes-title',
        'page-fatt-active',
        'page-fatt-name',
        'page-fatt-menu-categories',
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
        'page-courses-level-display',
        'page-courses-submenu-categories',
        'page-courses-list-format',
        'page-members-active',
        'page-members-membership-details',
        'page-members-application-details',
        'page-members-list-format',
        'page-members-categories-display',
        'page-members-name',
        'page-dealers-active',
        'page-dealers-name',
        'page-dealers-categories-display',
        'page-dealers-locations-display',
        'page-dealers-locations-map-names',
        'page-dealers-map-display',
        'page-dealers-list-format',
        'page-distributors-active',
        'page-distributors-name',
        'page-distributors-categories-display',
        'page-distributors-locations-display',
        'page-distributors-locations-map-names',
        'page-distributors-map-display',
        'page-distributors-list-format',
        'page-sponsors-active',
        'page-sponsors-sponsorship-active',
        'page-newsletters-active',
        'page-newsletters-title',
        'page-surveys-active',
        'page-workshops-active',
        'page-workshops-past',
        'page-events-active',
        'page-events-upcoming-empty-hide',
        'page-events-title',
        'page-events-current',
        'page-events-past',
        'page-events-categories-display',
        'page-events-image',
        'page-events-image-caption',
        'page-events-content',
        'page-events-thumbnail-format',
        'page-events-thumbnail-padding-color',
        'page-musicfestivals-active',
        'page-musicfestivals-title',
        'page-musicfestivals-festivalid',
        'page-filmschedule-active',
        'page-filmschedule-title',
        'page-filmschedule-past',
        'page-directory-active',
        'page-directory-title',
        'page-directory-layout',
        'page-links-active',
        'page-links-title',
        'page-links-categories-format',
        'page-links-tags-format',
        'page-membersonly-active',
        'page-membersonly-menu-active',
        'page-membersonly-name',
        'page-membersonly-password',
        'page-membersonly-message',
        'page-contact-active',
        'page-contact-google-map',
        'page-contact-map-latitude',
        'page-contact-map-longitude',
        'page-contact-mailchimp-signup',
        'page-contact-mailchimp-submit-url',
        'page-contact-form-display',
        'page-contact-form-emails',
        'page-contact-form-phone',
        'page-contact-form-intro-message',
        'page-contact-form-submitted-message',
        'page-contact-tenant-name-display',
        'page-contact-person-name-display',
        'page-contact-address-display',
        'page-contact-phone-display',
        'page-contact-fax-display',
        'page-contact-email-display',
        'page-contact-bios-display',                    // How the bios should be display on the contact page.
        'page-contact-subscriptions-signup',
        'page-contact-subscriptions-intro-message',
        'page-downloads-active',
        'page-downloads-name',
        'page-account-active',
        'page-account-dealers-only',
        'page-account-child-logins',
        'page-account-header-buttons',
        'page-account-sidebar',
        'page-account-password-change',
        'page-account-children-update',
        'page-account-children-member-10-update',
        'page-account-children-member-20-update',
        'page-account-children-member-30-update',
        'page-account-children-member-40-update',
        'page-account-children-member-110-update',
        'page-account-children-member-150-update',
        'page-account-children-member-lifetime-update',
        'page-account-children-member-non-update',
        'page-account-phone-update',
        'page-account-email-update',
        'page-account-address-update',
        'page-account-signin-redirect',
        'page-account-header-signin-text',
        'page-account-timeout',
        'page-account-invoices-list',
        'page-account-invoices-view-details',
        'page-account-invoices-view-pdf',
        'page-cart-active',
        'page-cart-inventory-customers-display',    // Display current inventory to customers
        'page-cart-inventory-members-display',      // Display current inventory to members
        'page-cart-inventory-dealers-display',      // Display current inventory to dealers
        'page-cart-inventory-distributors-display',     // Display current inventory to distributors
        'page-cart-product-search',                 // Allow users to search products
        'page-cart-product-list',                   // Show list of all products
        'page-cart-po-number',
        'page-cart-customer-notes',
        'page-cart-currency-display',
        'page-cart-registration-child-select',      // Show a list of children to select for a registration
        'page-cart-dealersubmit-email-template',
        'page-cart-dealersubmit-email-textmsg',
        'page-cart-account-create-button',
        'page-cart-child-create-button',
        'page-cart-noaccount-message',
        'page-cart-payment-success-message',
        'page-cart-payment-success-emails',         // Emails to send notification to when order paid for
        'page-signup-active',
        'page-signup-menu',
        'page-search-active',
        'page-api-active',
        'page-tutorials-active',
        'page-tutorials-image',
        'page-tutorials-image-caption',
        'page-tutorials-content',
        'page-faq-active',
        'page-merchandise-active',
        'page-merchandise-name',
        'site-theme',
        'site-layout',
//      'site-subscription-agreement',
//      'site-privacy-policy',
        'site-background-image',
        'site-background-overlay-colour',
        'site-background-overlay-percent',
        'site-background-position-x',
        'site-background-position-y',
        'site-header-image',
        'site-header-image-size',
        'site-header-og-image',
        'site-header-title',
        'site-header-title-override',
        'site-header-address',
        'site-header-landingpage1-title',
        'site-header-landingpage1-permalink',
//      'site-logo-display',
        'site-google-analytics-account',
        'site-google-site-verification',
        'site-pinterest-site-verification',
        'site-featured',
        'site-custom-css',
        'site-meta-robots',
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
        'site-social-share-buttons',
        'site-ssl-active',
        'site-ssl-force-cart',
        'site-ssl-force-account',
        'site-ssl-shop',
        'site-footer-copyright-name',
        'site-footer-copyright-message',
        'site-footer-subscription-agreement',
        'site-footer-privacy-policy',
        'site-footer-landingpage1-title',
        'site-footer-landingpage1-permalink',
        'site-footer-message',
        'site-mylivechat-enable',
        'site-mylivechat-userid',
        );

    //
    // The list of valid content for web pages
    //
    $content_fields = array(
        'page-home-content',
        'page-about-content',
//      'page-abouthistory-content',
//      'page-aboutdonations-content',
//      'page-aboutboardofdirectors-content',
//      'page-aboutartiststatement-content',
//      'page-aboutcv-content',
//      'page-aboutawards-content',
        'page-contact-content',
        'page-courses-content',
        'page-signup-content',
        'page-signup-agreement',
        'page-signup-submit',
        'page-signup-success',
        'page-account-content',
        'page-account-content-subscriptions',
        'page-artgalleryexhibitions-content',
        'site-subscription-agreement',
        'site-privacy-policy',
        );

    //
    // Check access to tnid as owner, and load module list
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.siteSettingsUpdate');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }
    $modules = $ac['modules'];

    //
    // Grab the existing settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid',
        $args['tnid'], 'ciniki.web', 'settings', '');
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
        $rc = ciniki_courses_web_courseTypes($ciniki, $settings, $args['tnid']);
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
            $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
                . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
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
            ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
                2, 'ciniki_web_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
            $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
                'args'=>array('id'=>$field));

            //
            // Check for image updates
            //
            if( ($field == 'page-home-image' 
                    || $field == 'page-about-image2' 
                    || $field == 'page-about-image' 
                    || $field == 'site-background-image'
                    || $field == 'site-header-image' )
                && (!isset($settings[$field]) 
                    || $settings[$field] != $ciniki['request']['args'][$field] )
                ) {
                if( isset($settings[$field]) && $settings[$field] != '0' ) {
                    //
                    // Remove the old reference
                    //
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefClear');
                    $rc = ciniki_core_objectRefClear($ciniki, $args['tnid'], 'ciniki.images.image', array(
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
                    $rc = ciniki_core_objectRefAdd($ciniki, $args['tnid'], 'ciniki.images.image', array(
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
                $strsql = "INSERT INTO ciniki_web_content (tnid, detail_key, detail_value, date_added, last_updated) "
                    . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
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
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
                    2, 'ciniki_web_content', $field, 'detail_value', $ciniki['request']['args'][$field]);
                $ciniki['syncqueue'][] = array('push'=>'ciniki.web.content',
                    'args'=>array('id'=>$field));
            } else {
                $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
                    . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
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
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
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
                    $rc = ciniki_core_objectRefClear($ciniki, $args['tnid'], 'ciniki.images.image', array(
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
                    $rc = ciniki_core_objectRefAdd($ciniki, $args['tnid'], 'ciniki.images.image', array(
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
        if( $field == 'page-members-list-format'
            || $field == 'page-dealers-list-format' 
            || $field == 'page-distributors-list-format' 
            ) {
            $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'settingChange');
            if( $rc['stat'] == 'ok' ) {
                $rc = ciniki_customers_web_settingChange($ciniki, $args['tnid'], $field, $field_value);
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
    // Check the list of tenant users to see if their information should be displayed on the website
    //
    $strsql = "SELECT DISTINCT ciniki_tenant_users.user_id AS id "
        . "FROM ciniki_tenant_users "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'user');
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
                    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
                        . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
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
                    ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
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
        $rc = ciniki_web_updateUserDisplay($ciniki, $args['tnid']);
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
            $strsql = "INSERT INTO ciniki_web_content (tnid, detail_key, detail_value, date_added, last_updated) "
                . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
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
            ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
