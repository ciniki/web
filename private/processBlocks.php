<?php
//
// Description
// -----------
// Process the block content for a page.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
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
            case 'asidecontent': $processor = 'processBlockAsideContent'; break;
            case 'asideimage': $processor = 'processBlockAsideImage'; break;
            case 'audiolist': $processor = 'processBlockAudioList'; break;
            case 'audiopricelist': $processor = 'processBlockAudioPriceList'; break;
            case 'chartoverlay': $processor = 'processBlockChartOverlay'; break;
            case 'cilist': $processor = 'processBlockCIList'; break;
            case 'clist': $processor = 'processBlockCList'; break;
            case 'content': $processor = 'processBlockContent'; break;
            case 'details': $processor = 'processBlockDetails'; break;
            case 'files': $processor = 'processBlockFiles'; break;
            case 'formmessage': $processor = 'processBlockFormMessage'; break;
            case 'image': $processor = 'processBlockImage'; break;
            case 'imagelist': $processor = 'processBlockImageList'; break;
            case 'gallery': $processor = 'processBlockGallery'; break;
            case 'galleryimage': $processor = 'processBlockGalleryImage'; break;
            case 'links': $processor = 'processBlockLinks'; break;
            case 'map': $processor = 'processBlockMap'; break;
            case 'message': $processor = 'processBlockMessage'; break;
            case 'meta': $processor = 'processBlockMeta'; break;
            case 'multipagenav': $processor = 'processBlockMultiPageNav'; break;
            case 'orderdetails': $processor = 'processBlockOrderDetails'; break;
            case 'orderoptions': $processor = 'processBlockOrderOptions'; break;
            case 'orderrepeats': $processor = 'processBlockOrderRepeats'; break;
            case 'orderqueue': $processor = 'processBlockOrderQueue'; break;
            case 'ordersubstitutions': $processor = 'processBlockOrderSubstitutions'; break;
            case 'prices': $processor = 'processBlockPrices'; break;
            case 'pricelist': $processor = 'processBlockPriceList'; break;
            case 'pricetable': $processor = 'processBlockPriceTable'; break;
            case 'printoptions': $processor = 'processBlockPrintOptions'; break;
            case 'productcards': $processor = 'processBlockProductCards'; break;
            case 'sharebuttons': $processor = 'processBlockShareButtons'; break;
            case 'sponsors': $processor = 'processBlockSponsors'; break;
            case 'table': $processor = 'processBlockTable'; break;
            case 'tableslide': $processor = 'processBlockTableSlide'; break;
            case 'tabs': $processor = 'processBlockTabs'; break;
            case 'tagcloud': $processor = 'processBlockTagCloud'; break;
            case 'tagimagelist': $processor = 'processBlockTagImageList'; break;
            case 'tagimages': $processor = 'processBlockTagImages'; break;
            case 'taglist': $processor = 'processBlockTagList'; break;
            case 'tradingcards': $processor = 'processBlockTradingCards'; break;
            case 'videolinks': $processor = 'processBlockVideoLinks'; break;
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
                    if( isset($block['section']) && $block['section'] != '' ) {
                        $display = '';
                        if( isset($block['display']) && $block['display'] != '' ) {
                            $display = 'display: ' . $block['display'] . ';';
                        }
                        $rsp['content'] .= "<div " . (isset($block['id']) && $block['id'] != '' ? "id='" . $block['id'] . "' " : '') . "class='block block-" . $block['section'] . "'"
                            . ($display != '' ? " style='" . $display . "'" : "")
                            . ">"
                            . $rc['content']
                            . "</div>";
                    } else {
                        $rsp['content'] .= $rc['content'];
                    }
                }
            }
        }
    }

    return $rsp;
}
?>
