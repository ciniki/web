<?php
//
// Description
// -----------
// Process the block content for a page.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlocks(&$ciniki, $settings, $business_id, $blocks) {

	$rsp = array('stat'=>'ok', 'content'=>'');

	//
	// Process the blocks of content
	//
	foreach($blocks as $block) {
		$processor = '';
		switch($block['type']) {
			case 'archivelist': $processor = 'processBlockArchiveList'; break;
			case 'asideimage': $processor = 'processBlockAsideImage'; break;
			case 'cilist': $processor = 'processBlockCIList'; break;
			case 'clist': $processor = 'processBlockCList'; break;
			case 'content': $processor = 'processBlockContent'; break;
			case 'details': $processor = 'processBlockDetails'; break;
			case 'files': $processor = 'processBlockFiles'; break;
			case 'image': $processor = 'processBlockImage'; break;
			case 'imagelist': $processor = 'processBlockImageList'; break;
			case 'gallery': $processor = 'processBlockGallery'; break;
			case 'galleryimage': $processor = 'processBlockGalleryImage'; break;
			case 'links': $processor = 'processBlockLinks'; break;
			case 'message': $processor = 'processBlockMessage'; break;
			case 'meta': $processor = 'processBlockMeta'; break;
			case 'multipagenav': $processor = 'processBlockMultiPageNav'; break;
			case 'prices': $processor = 'processBlockPrices'; break;
			case 'printoptions': $processor = 'processBlockPrintOptions'; break;
			case 'sharebuttons': $processor = 'processBlockShareButtons'; break;
			case 'sponsors': $processor = 'processBlockSponsors'; break;
			case 'tagcloud': $processor = 'processBlockTagCloud'; break;
			case 'tagimagelist': $processor = 'processBlockTagImageList'; break;
			case 'tagimages': $processor = 'processBlockTagImages'; break;
			case 'taglist': $processor = 'processBlockTagList'; break;
		}
		if( $processor != '' ) {
			$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', $processor);
			if( $rc['stat'] == 'ok' ) {
				$fn = "ciniki_web_$processor";
				$rc = $fn($ciniki, $settings, $business_id, $block);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['content']) ) {
					$rsp['content'] .= $rc['content'];
				}
			}
		}
	}

	return $rsp;
}
?>