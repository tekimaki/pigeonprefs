<?php /* -*- Mode: php; tab-width: 4; indent-tabs-mode: t; c-basic-offset: 4; -*- */
/**
 * $Header: $
 *
 * Copyright (c) 2010 Tekimaki LLC http://tekimaki.com
 *
 * All Rights Reserved. See below for details and a complete list of authors.
 *
 *
 * $Id: $
 * @package pigeonprefs
 * @subpackage class
 */


global $gBitSystem;

define( 'LIBERTY_SERVICE_PIGEONPREFS', 'pigeonhole_prefs' );

$registerHash = array(
	'package_name' => 'pigeonprefs',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

// If package is active
if( $gBitSystem->isPackageActive( 'pigeonholes' ) && $gBitSystem->isPackageActive( 'pigeonprefs' ) ) {
	require_once( PIGEONPREFS_PKG_PATH.'pigeonprefs_lib.php' );

	$gLibertySystem->registerService( 
		LIBERTY_SERVICE_PIGEONPREFS, 
		PIGEONPREFS_PKG_NAME, 
		array(
			'content_edit_function'  => 'pigeonprefs_content_edit',
			'content_store_function'  => 'pigeonprefs_content_store',
			'content_preview_function'  => 'pigeonprefs_content_preview',
			'content_edit_mini_tpl' => 'bitpackage:pigeonprefs/service_edit_mini_inc.tpl',
		),
		array( 
			'description' => 'Enables targeting pigeonhole taxonomies to individual content types.'
		)
	);
}
