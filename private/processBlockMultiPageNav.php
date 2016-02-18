<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockMultiPageNav(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMeta');

	$content = '';

	if( isset($block['total_pages']) && isset($block['cur_page']) ) {
		if( $block['cur_page'] > 1 ) {
            //
            // Reverse lets the multipage nav start at the highest page
            //
            if( isset($block['reverse']) && $block['reverse'] == 'yes' ) {
                $content .= "<span class='multipage-nav-button multipage-nav-button-first'>"
                    . "<a href='" . $block['base_url'] . "?page=1'><span class='multipage-nav-button-text'>First</span></a>"
                    . "</span>";
                $content .= "<span class='multipage-nav-button multipage-nav-button-prev'>"
                    . "<a href='" . $block['base_url'] . ($block['cur_page']>2?"?page=".($block['cur_page']-1):'?page=1') . "'><span class='multipage-nav-button-text'>Prev</span></a>"
                    . "</span>";
            } else {
                $content .= "<span class='multipage-nav-button multipage-nav-button-first'>"
                    . "<a href='" . $block['base_url'] . "'><span class='multipage-nav-button-text'>First</span></a>"
                    . "</span>";
                $content .= "<span class='multipage-nav-button multipage-nav-button-prev'>"
                    . "<a href='" . $block['base_url'] . ($block['cur_page']>2?"?page=".($block['cur_page']-1):'') . "'><span class='multipage-nav-button-text'>Prev</span></a>"
                    . "</span>";
            }
		}
		$start = 1;
		$end = $block['total_pages'];
		if( $block['total_pages'] > 5 ) {
			$start = $block['cur_page'] - 2;
			if( $start < 1 ) { 
				$start = 1;
			}
			$end = $start + 4;
			if( $end > $block['total_pages'] ) {
				$end = $block['total_pages'];
				$start = $end - 4;
			}
		}
		for($i = $start; $i <= $end; $i++) {
            if( isset($block['reverse']) && $block['reverse'] == 'yes' ) {
                $content .= "<span class='multipage-nav-button" . ($i==$block['cur_page']?' multipage-nav-button-selected':'') . "'><a href='" . $block['base_url'] . "?page=$i" . "'>"
                    . "<span class='multipage-nav-button-text'>" . $i . "</span></a></span>";
            } else {
                $content .= "<span class='multipage-nav-button" . ($i==$block['cur_page']?' multipage-nav-button-selected':'') . "'><a href='" . $block['base_url'] . ($i>1?"?page=$i":'') . "'>"
                    . "<span class='multipage-nav-button-text'>" . $i . "</span></a></span>";
            }
		}
		if( $block['cur_page'] < $block['total_pages'] ) {
			$content .= "<span class='multipage-nav-button multipage-nav-button-next'>"
				. "<a href='" . $block['base_url'] . "?page=" . ($block['cur_page']+1) . "'><span class='multipage-nav-button-text'>Next</span></a>"
				. "</span>";
			$content .= "<span class='multipage-nav-button multipage-nav-button-last'>"
				. "<a href='" . $block['base_url'] . "?page=" . $block['total_pages'] . "'><span class='multipage-nav-button-text'>Last</span></a>"
				. "</span>";
		}
	}

	if( $content != '' ) {
		$content = "<div class='multipage-nav'><div class='multipage-nav-content'>" . $content . "</div></div>";
	}


	return array('stat'=>'ok', 'content'=>$content);
}
?>
