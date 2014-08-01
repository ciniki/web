<?php
//
// Description
// -----------
// This function will prepare a single product page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processProduct(&$ciniki, $settings, $business_id, $base_url, $product, $args) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = '';

	//
	// Add primary image
	//
	if( isset($product['image_id']) && $product['image_id'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $product['image_id'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		// Setup the og image
		$ciniki['response']['head']['og']['image'] = $rc['domain_url'];

		$content .= "<aside><div class='image-wrap'><div class='image'>"
			. "<img title='' alt='" . $product['name'] . "' src='" . $rc['url'] . "' />"
			. "</div></div></aside>";
	}
	
	//
	// Add description
	//
	if( isset($product['long_description']) && $product['long_description'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $product['long_description']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	} elseif( isset($product['short_description']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $product['short_description']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	}

	//
	// Display any audio sample
	//
	if( isset($product['audio']) && count($product['audio']) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processAudio');
		$rc = ciniki_web_processAudio($ciniki, $settings, $business_id, $product['audio'], array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['content'] != '' ) {
			$content .= '<p>' . $rc['content'] . '</p>';
		}
	}

	//
	// Display the files for the products
	//
	if( isset($product['files']) && count($product['files']) > 0 ) {
		$content .= "<p>";
		foreach($product['files'] as $file) {
			$url = $ciniki['request']['base_url'] . '/products/p/' . $ciniki['request']['uri_split'][1] . '/download/' . $file['permalink'] . '.' . $file['extension'];
//				$content .= "<span class='downloads-title'>";
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
			} else {
				$content .= $file['name'];
			}
//				$content .= "</span>";
			if( isset($file['description']) && $file['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
			}
			$content .= "<br/>";
		}
		$content .= "</p>";
	}

	//
	// Display the prices if the product is for sale
	//
	if( isset($product['prices']) && count($product['prices']) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cartSetupPrices');
		$rc = ciniki_web_cartSetupPrices($ciniki, $settings, $business_id, $product['prices']);
		if( $rc['stat'] != 'ok' ) {
			error_log("Error in formatting prices.");
		} else {
			$content .= $rc['content'];
		}
	}

	if( !isset($settings['page-products-share-buttons']) || $settings['page-products-share-buttons'] == 'yes' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'shortenURL');
		$surl = ciniki_web_shortenURL($ciniki, $ciniki['request']['business_id'],
			$ciniki['response']['head']['og']['url']);
		$content .= "<p class='share-buttons-wrap'><span class='share-buttons'><span class='socialtext'>Share on: </span>";
		$content .= "<a href='https://www.facebook.com/sharer.php?u=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
			. "<span title='Share on Facebook' class='socialsymbol social-facebook'>&#xe227;</span>"
			. "</a>";
	
		$msg = $ciniki['business']['details']['name'] . ' - ' . $args['title'];
		if( isset($ciniki['business']['social']['social-twitter-username']) 
			&& $ciniki['business']['social']['social-twitter-username'] != '' ) {
			$msg .= ' @' . $ciniki['business']['social']['social-twitter-username'];
		}
		$tags = array_unique($args['tags']);
		foreach($tags as $tag) {
			if( $tag == '' ) { continue; }
			if( (strlen($surl) + 1 + strlen($msg) + 2 + strlen($tag)) < 140 ) {
				$msg .= ' #' . $tag;
			}
		}
		$content .= "<a href='https://twitter.com/share?url=" . urlencode($surl) . "&text=" . urlencode($msg) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
			. "<span title='Share on Twitter' class='socialsymbol social-twitter'>&#xe286;</span>"
			. "</a>";

		$content .= "<a href='http://www.pinterest.com/pin/create/button?url=" . urlencode($ciniki['response']['head']['og']['url']) . "&media=" . urlencode($ciniki['response']['head']['og']['image']) . "&description=" . urlencode($ciniki['business']['details']['name'] . ' - ' . $args['title']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
			. "<span title='Share on Pinterest' class='socialsymbol social-pinterest'>&#xe264;</span>"
			. "</a>";

		$content .= "<a href='https://plus.google.com/share?url=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
			. "<span title='Share on Google+' class='socialsymbol social-googleplus'>&#xe239;</span>"
			. "</a>";

		$content .= "</span></p>";
	}


	//
	// Display the additional images for the product
	//
	if( isset($product['images']) && count($product['images']) > 0 ) {
		$content .= "<h2 class='entry-subtitle'>Gallery</h2>\n";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
		$img_base_url = $base_url . "/gallery";
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, 
			$img_base_url, $product['images'], 125);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
	}

	//
	// Display the similar products
	//
	if( isset($product['similar']) && count($product['similar']) > 0 ) {
		$content .= "<h2 class='entry-subtitle'>Similar Products</h2>\n";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
		$base_url = $ciniki['request']['base_url'] . "/products/product";
		$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
			'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
			'list'=>$product['similar'])), array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	}

	//
	// Display the recommended recipes
	//
	if( isset($product['recipes']) && count($product['recipes']) > 0 ) {
		$content .= "<h2 class='entry-subtitle'>Recommended Recipes</h2>\n";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
		$base_url = $ciniki['request']['base_url'] . "/recipes";
		$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
			'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
			'list'=>$product['recipes'])), array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>

