<?php

// edit service
function pigeonprefs_content_edit( $pObject=NULL ) {
	// load up pigeonroot ids for services content type has
	// expected options hash $pigeonOptions[$id][$cats];
	global $gBitSmarty, $gBitUser, $gBitSystem;
	$pigeonOptions = array();
	$pigeonOptionsLabels = array();

	if( is_object($pObject) && isset($pObject->mContentTypeGuid) &&
		$gBitUser->hasPermission( 'p_pigeonholes_insert_member' ) ) {

		$lcconfig = LCConfig::getInstance();

		$pigeonholes = new Pigeonholes();

		// get list of services for content type
		foreach( $lcconfig->getAllConfig( $pObject->mContentTypeGuid ) as $config=>$value ){
			if( strpos( $config, 'service_pigeon_content_id' ) !== false  && $value != 'n' ){
				$cid = substr( $config, strrpos( $config, '_' )+1 );

				// get pigeonholes path list
				$hash = array();
				$hash['children_of_content_id'] = $cid;
				$hash['truncate'] = ( $gBitSystem->isFeatureActive( 'pigeonholes_use_jstab' ) ? FALSE : 100 );
				$hash['content_id'] = $pObject->mContentId;
				$pigeonOptions[$cid] = pigeonprefs_get_pathlist( $hash ); 
				$pigeonOptionsLabels[$cid] = $gBitSystem->mDb->getOne( "SELECT title FROM liberty_content WHERE content_id = ?", array( $cid ) ); 
			}
		}
	}
	//vd( $pigeonOptions );
	$gBitSmarty->assign( 'pigeonOptions', $pigeonOptions );
	$gBitSmarty->assign( 'pigeonOptionsLabels', $pigeonOptionsLabels );
}


// preview service
function pigeonprefs_content_preview(){
}

// store service
function pigeonprefs_content_store( $pObject, $pParamHash ) {
	global $gBitSmarty, $gBitUser, $gBitSystem;
	if( is_object($pObject) && isset($pObject->mContentTypeGuid) &&
		$gBitUser->hasPermission( 'p_pigeonholes_insert_member' ) ) {

		// if pigeon values submitted
		// expected submit hash $pParamHash['pigeon_options'][$id][$cats] 
		//vd( $pParamHash );
		if( !empty( $pParamHash['pigeon_options'] ) ){
			$storeHash['pigeonholes'] = array();
			foreach( array_keys( $pParamHash['pigeon_options'] ) as $cid ){
				// check if content has this root
				if( $pObject->hasService( 'pigeon_content_id_'.$cid ) && !empty( $pParamHash['pigeon_options'][$cid] ) ){
					// make sure we're not trying to store an empty string
					if( count( $pParamHash['pigeon_options'][$cid] ) > 1 || 
						!empty( $pParamHash['pigeon_options'][$cid][0] ) )
					{
						if( !isset( $storeHash['pigeonholes']['pigeonhole'] ) ){
							$storeHash['pigeonholes']['pigeonhole'] = array();
						}
						// pass the sub selections to pigeonholes service
						$storeHash['pigeonholes']['pigeonhole'] = array_merge( $storeHash['pigeonholes']['pigeonhole'], $pParamHash['pigeon_options'][$cid] ); 
						// vd( $storeHash );
					}
				}
			}
			// vd( $storeHash );
			$gBitSystem->setConfig('pigeonhole_no_'.$pObject->mContentTypeGuid, FALSE);
			pigeonholes_content_store( $pObject, $storeHash );
			$gBitSystem->setConfig('pigeonhole_no_'.$pObject->mContentTypeGuid, TRUE);
		}
	}
}

function pigeonprefs_get_root_ids(){
	global $gBitSystem;
	$query = "SELECT p.content_id, lc.title 
				FROM pigeonholes p
				INNER JOIN liberty_structures ls ON ls.structure_id = p.structure_id
				INNER JOIN liberty_content lc ON p.content_id = lc.content_id
				WHERE ls.structure_id = ls.root_structure_id ORDER BY lc.title";	
	$ret = $gBitSystem->mDb->getArray( $query );
	return $ret;
}

/**
* @returns an array of page_info arrays.
*/
function pigeonprefs_get_path( $pStructureId ) {
	$ls = new LibertyStructure();
	$structure_path = array();
	$page_info = $ls->getNode($pStructureId);
	if ($page_info["parent_id"]) {
		$structure_path = pigeonprefs_get_path($page_info["parent_id"]);
		$structure_path[] = $page_info;
	}
	return $structure_path;
}


/**
 * get an array of paths for all pigeonholes. used for pages where data can be inserted into pigeonholes
 * ripped and refactored from Pigeonholes::getPigeonholesPathList
 *
 * @param numeric $pContentId content id of pigeonhole.
 * @param numeric $pTruncate Setting this to a number will do some smart truncations depending on how many parents there are
 *                           setting it to 60 will allow 30 chars for all parents combined and 30 for the actual title
 * @access public
 * @return TRUE on success, FALSE if there is no pigeonhole
 */
function pigeonprefs_get_pathlist( $pParamHash ){
	global $gBitSystem;
	$where = $join = ''; $bindVars = array();

	$pContentId = !empty( $pParamHash['content_id'] ) ? $pParamHash['content_id'] : NULL;
	$pTruncate = !empty( $pParamHash['truncate'] ) ? $pParamHash['truncate'] : FALSE;
	$pShowAll = !empty( $pParamHash['showall'] ) ? $pParamHash['showall'] : FALSE;

	if( $gBitSystem->isFeatureActive( 'pigeonholes_allow_forbid_insertion' ) && !$pShowAll ) {
		$where .= empty( $where ) ? ' WHERE ' : ' AND ';
		$where .= ' lcp.`pref_value` IS NULL OR lcp.`pref_value` != \'on\' ';
		$join .= ' LEFT JOIN `'.BIT_DB_PREFIX.'liberty_content_prefs` lcp ON (pig.`content_id` = lcp.`content_id` AND lcp.`pref_name` = \'no_insert\') ';
	}
	
	if( @BitBase::verifyId( $pParamHash['children_of_content_id'] ) ){ 
		$join .= 'inner join liberty_structures ls2 ON ls.root_structure_id = ls2.structure_id ';
		$where .= ( empty( $where )?' WHERE ':' AND ' ).' ls2.content_id = ? AND ls.content_id != ?';
		$bindVars[] = $pParamHash['children_of_content_id'];
		$bindVars[] = $pParamHash['children_of_content_id'];
	}

	$query = "SELECT pig.`content_id`, pig.`structure_id`
		FROM `".BIT_DB_PREFIX."pigeonholes` pig
		INNER JOIN `".BIT_DB_PREFIX."liberty_structures` ls ON ( ls.`structure_id` = pig.`structure_id` )
		$join
		$where
		ORDER BY ls.`root_structure_id`, ls.`structure_id` ASC";
	$result = $gBitSystem->mDb->query( $query, $bindVars );
	$pigeonholes = $result->getRows();

	$Pig = new Pigeonholes();
	foreach( $pigeonholes as $pigeonhole ) {
		$ret[$pigeonhole['content_id']] = $Pig->getPigeonholePath( $pigeonhole['structure_id'] );
	}

	if( !empty( $ret ) ) {
		if( $pTruncate ) {
			foreach( $ret as $cid => $path ) {
				// count here to minimise speed loss
				$count = count( $path );
				foreach( $path as $pos => $pig ) {
					// calculate limit at which category is truncated
					if( $count == 1 ) {
						$limit = $pTruncate;
					} elseif( $pos == $count - 1 ) {
							$limit = ceil( $pTruncate / 2 );
					} else {
						$limit = ceil( $pTruncate / 2 / $count );
					}
					$ret[$cid][$pos]['title'] = substr( $pig['title'], 0, $limit ).( ( strlen( $pig['title'] ) <= $limit ) ? '' : '...' );
				}
			}
		}

		// sort the pathlist to make the display nicer
		uasort( $ret, 'pigeonholes_pathlist_sorter' );

		if( @BitBase::verifyId( $pContentId ) && $assigned = $Pig->getPigeonholesFromContentId( $pContentId ) ) {
			foreach( $assigned as $a ) {
				if( !empty( $ret[$a['content_id']] ) ){
					$ret[$a['content_id']]['selected'] = TRUE;
				}
			}
		}
	}

	return( !empty( $ret ) ? $ret : array() );
}
