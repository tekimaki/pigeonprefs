<?php
$gBitSystem->verifyPermission( 'p_admin' );

require_once( PIGEONPREFS_PKG_PATH.'pigeonprefs_lib.php' );

// service preferences we want to configure
$pigeonRootContentIds = pigeonprefs_get_root_ids();
$gBitSmarty->assign( 'pigeonRootContentIds', $pigeonRootContentIds );
// vd( $pigeonRootContentIds );

// requires LCConfig pkg to store preferences
if( $gBitSystem->isPackageActive( 'lcconfig' ) ){
	require_once( LCCONFIG_PKG_PATH.'LCConfig.php' );
	$LCConfig = LCConfig::getInstance();

	//vd( $_REQUEST );

	// deal with service preferences
	if( !empty( $_REQUEST['save'] )) {
		$gBitUser->verifyTicket();
		$LCConfig->mDb->StartTrans();

		foreach( array_keys( $gLibertySystem->mContentTypes ) as $ctype ) {
			foreach( $pigeonRootContentIds as $index=>$data ) {
				$cid = $data['content_id'];
				// store pref for pigeonhole root_content_ids
				if( empty( $_REQUEST['pigeon_ids'][$cid][$ctype] ) || $_REQUEST['pigeon_ids'][$cid][$ctype] == 'n' ){
					$LCConfig->expungeConfig( 'service_pigeon_content_id_'.$cid, $ctype );
				}else{
					$LCConfig->storeConfig( 'service_pigeon_content_id_'.$cid, $ctype, $_REQUEST['pigeon_ids'][$cid][$ctype] );
				}
			}
		}

		if( empty( $feedback['error'] ) ){
			$LCConfig->mDb->CompleteTrans();
			$feedback['success'] = tra( "Services preferences were updated." );
			$LCConfig->reloadConfig();
		}
		else{
			$LCConfig->mDb->RollbackTrans();
			$LCConfig->reloadConfig();
		}
		//vd( $LCConfig->getAllConfig() );
	}
	$gBitSmarty->assign_by_ref( 'feedback', $feedback );

	// vd( $LCConfig->getAllConfig() );
	$gBitSmarty->assign_by_ref( 'LCConfigSettings', $LCConfig->getAllConfig() );
}
