<?php
//
// Description
// -----------
// This function will generate the faq page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageFAQ($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'FAQ';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	$page_content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>FAQ</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";
	//
	// Get the list of questions, organized by category
	//
	$strsql = "SELECT ciniki_web_faqs.id, "
		. "ciniki_web_faqs.category, "
		. "ciniki_web_faqs.question, "
		. "ciniki_web_faqs.answer "
		. "FROM ciniki_web_faqs "
		. "WHERE ciniki_web_faqs.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
		. "AND (ciniki_web_faqs.flags&0x01) = 0 "
		. "ORDER BY ciniki_web_faqs.category "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'categories', 'fname'=>'category', 'name'=>'category',
			'fields'=>array('name'=>'category')),
		array('container'=>'faqs', 'fname'=>'id', 'name'=>'faq',
			'fields'=>array('id', 'category', 'question', 'answer')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	
	if( !isset($rc['categories']) ) {
		$page_content .= "<p>I'm sorry but we don't have any questions yet.</p>";

	} else {
		$page_content .= "<table class='clist'><tbody>";
		$categories = $rc['categories'];
		foreach($categories as $c => $category) {
			$page_content .= "<tr><th><span class='clist-category'>" 
				. $category['category']['name']
				. "</span></th><td>";
			foreach($category['category']['faqs'] as $f => $faq) {
				$faq = $faq['faq'];
				$page_content .= "<p class='clist-title'>" . $faq['question'] . "</p>";
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $faq['answer'], 'clist-description');	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
			$page_content .= "</td></tr>";
		}
		$page_content .= "</tbody></table>";
	}

	$page_content .= "</div>\n"
		. "</article>\n"
		. "";

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
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
