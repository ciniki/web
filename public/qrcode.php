<?php
//
// Description
// -----------
// Generate a QR code image
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_web_qrcode(&$ciniki) {

    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'url'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'URL'), 
        'output'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Format'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf_barcodes_2d.php');

    // set the barcode content and type
    $barcodeobj = new TCPDF2DBarcode($args['url'], 'QRCODE,H');

    // output the barcode as SVG image
    if( $args['output'] == 'png' ) {
        header("Content-type: image/png");
        $barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
        return array('stat'=>'exit');
    } else {
        header("Content-type: image/svg+xml");
        $barcodeobj->getBarcodeSVG(6, 6, 'black');
        return array('stat'=>'exit');
    }

    return array('stat'=>'ok');
}
?>
