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
function ciniki_web_processBlocks(&$ciniki, $settings, $tnid, $blocks, $args = array()) {

    $rsp = array('stat'=>'ok', 'content'=>'', 'css'=>'');

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
            case 'calendar': $processor = 'processBlockCalendar'; break;
            case 'chartoverlay': $processor = 'processBlockChartOverlay'; break;
            case 'cilist': $processor = 'processBlockCIList'; break;
            case 'clist': $processor = 'processBlockCIList'; break;
            case 'content': $processor = 'processBlockContent'; break;
            case 'decisionbuttons': $processor = 'processBlockDecisionButtons'; break;
            case 'details': $processor = 'processBlockDetails'; break;
            case 'files': $processor = 'processBlockFiles'; break;
            case 'formmessage': $processor = 'processBlockFormMessage'; break;
            case 'image': $processor = 'processBlockImage'; break;
            case 'imagelist': $processor = 'processBlockImageList'; break;
            case 'gallery': $processor = 'processBlockGallery'; break;
            case 'galleryimage': $processor = 'processBlockGalleryImage'; break;
            case 'links': $processor = 'processBlockLinks'; break;
            case 'login': 
                if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x0800) ) {
                    $processor = 'processBlockLoginAccount'; break;
                } else {
                    $processor = 'processBlockLogin'; break;
                }
            case 'map': $processor = 'processBlockMap'; break;
            case 'mappedtickets': $processor = 'processBlockMappedTickets'; break;
            case 'message': $processor = 'processBlockMessage'; break;
            case 'meta': $processor = 'processBlockMeta'; break;
            case 'monthlyavailability': $processor = 'processBlockMonthlyAvailability'; break;
            case 'multipagenav': $processor = 'processBlockMultiPageNav'; break;
            case 'orderdetails': $processor = 'processBlockOrderDetails'; break;
            case 'orderoptions': $processor = 'processBlockOrderOptions'; break;
            case 'orderrepeats': $processor = 'processBlockOrderRepeats'; break;
            case 'orderqueue': $processor = 'processBlockOrderQueue'; break;
            case 'ordersubstitutions': $processor = 'processBlockOrderSubstitutions'; break;
            case 'orderseason': $processor = 'processBlockOrderSeason'; break;
            case 'priceditems': $processor = 'processBlockPricedItems'; break;
            case 'prices': $processor = 'processBlockPrices'; break;
            case 'pricecards': $processor = 'processBlockPriceCards'; break;
            case 'pricelist': $processor = 'processBlockPriceList'; break;
            case 'pricetable': $processor = 'processBlockPriceTable'; break;
            case 'printoptions': $processor = 'processBlockPrintOptions'; break;
            case 'productcards': $processor = 'processBlockProductCards'; break;
            case 'registrationform': $processor = 'processBlockRegistrationForm'; break;
            case 'foldingtable': $processor = 'processBlockFoldingTable'; break;
            case 'sharebuttons': $processor = 'processBlockShareButtons'; break;
            case 'sectionedform': $processor = 'processBlockSectionedForm'; break;
            case 'slider': $processor = 'processBlockSlider'; break;
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
            if( isset($args['password_protected']) && $args['password_protected'] == 'yes' && $processor == 'processBlockShareButtons' ) {
                // Skip the addition of share buttons password protected pages
                continue;
            }
            $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', $processor);
            if( $rc['stat'] == 'ok' ) {
                $fn = "ciniki_web_$processor";
                $rc = $fn($ciniki, $settings, $tnid, $block);
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
                if( isset($rc['css']) ) {
                    if( !isset($ciniki['response']['blocks-css']) ) {
                        $ciniki['response']['blocks-css'] = '';
                    }
                    $ciniki['response']['blocks-css'] .= $rc['css'];
                }
                if( isset($rc['js']) ) {
                    if( !isset($ciniki['response']['blocks-js']) ) {
                        $ciniki['response']['blocks-js'] = '';
                    }
                    $ciniki['response']['blocks-js'] .= $rc['js'];
                }
            }
        }
    }

    return $rsp;
}
?>
