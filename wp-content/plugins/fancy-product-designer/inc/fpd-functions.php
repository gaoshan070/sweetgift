<?php

//checks if a product has fancy product enabled
function is_fancy_product( $post_id ) {

    return fpd_has_content( $post_id ) !== false;

}

function fpd_not_empty($value) {

	$value = gettype($value) === 'string' ? trim($value) : $value;
	return $value == '0' || !empty($value);

}

function fpd_convert_string_value_to_int($value) {

	if($value == 'yes') { return 1; }
	else if($value == 'no') { return 0; }
	else { return $value; }

}

function fpd_table_exists( $table_name ) {

	global $wpdb;
	return $wpdb->query( $wpdb->prepare("SHOW TABLES LIKE '%s'", $table_name) ) == 0 ? false : true;

}

function fpd_get_option( $key ) {
	return FPD_Settings::$radykal_settings->get_option( $key );
}

function fpd_convert_obj_string_to_array( $string ) {
	return json_decode( html_entity_decode( stripslashes( $string ) ), true );
}

function fpd_update_image_source( $string ) {

	$replace_i0_i1 = array('i0.wp.com/', 'i1.wp.com/');
	$string = str_replace($replace_i0_i1, '', $string); //remove i0/i1 sub-domains

	$url = site_url();
	$url_parts = parse_url($url);
	$domain = $url_parts['scheme'].'://'.$url_parts['host'].'/';

	$temp = @preg_replace('/(thumbnail|source)(\":\")(http|https):\/\/([^\/?#]+)(?:[\/?#])/i', '$1$2'.$domain, $string);

	if( empty($temp) )
		return $string;
	else
		return $temp;

}

function fpd_has_content( $post_id ) {

	$source_type = get_post_meta( $post_id, 'fpd_source_type', true );

	if( empty($source_type) || $source_type == 'category' ) {

		if( !fpd_table_exists(FPD_CATEGORIES_TABLE) )
			return false;

	}
	else {

		if( !fpd_table_exists(FPD_VIEWS_TABLE) )
			return false;

	}

	//get assigned categories
	$product_settings = new FPD_Product_Settings($post_id);
	$ids = $product_settings->get_content_ids();

	//check if categories are not empty
	return empty($ids) ? false : $ids;

}

function fpd_sort_terms_hierarchicaly(Array &$cats, Array &$into, $parent_id = 0) {

    foreach ($cats as $i => $cat) {
        if ($cat->parent == $parent_id) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $top_cat) {
        $top_cat->children = array();
        fpd_sort_terms_hierarchicaly($cats, $top_cat->children, $top_cat->term_id);
    }

}

function fpd_strip_multi_slahes( $str ) {

	json_decode($str);
	if (json_last_error() !== JSON_ERROR_NONE)
		$str = stripslashes( $str );
	else
		return $str;

	json_decode($str);
	if (json_last_error() !== JSON_ERROR_NONE)
		$str = stripslashes( $str );
	else
		return $str;

	json_decode($str);
	if (json_last_error() !== JSON_ERROR_NONE)
		$str = stripslashes( $str );
	else
		return $str;

	json_decode($str);
	if (json_last_error() !== JSON_ERROR_NONE)
		$str = stripslashes( $str );
	else
		return $str;

}

function fpd_wc_get_order_item_meta( $item_id, $order_id ) {

	if( function_exists('wc_get_order_item_meta') ) { //WC 3.0
		$fpd_data = wc_get_order_item_meta( $item_id, 'fpd_data' );
		//V3.4.9: data stored in _fpd_data
		$fpd_data = empty($fpd_data) ? wc_get_order_item_meta( $item_id, '_fpd_data' ) : $fpd_data;
	}
	else {
		$wc_order = wc_get_order( $order_id );
		$fpd_data = $wc_order->get_item_meta( $item_id, 'fpd_data', true);
		//V3.4.9: data stored in _fpd_data
		$fpd_data = empty($fpd_data) ? $wc_order->get_item_meta( $item_id, '_fpd_data', true) : $fpd_data;
	}

	return $fpd_data;

}

function fpd_get_files_from_uploads_by_type( $dir, $types ) {

	$urls = array();
	$path = FPD_WP_CONTENT_DIR . '/uploads/'.$dir.'/';

	if( file_exists($path) ) {

	  	$folder = opendir($path);

		while ($file = readdir ($folder)) {

			if( in_array(substr(strtolower($file), strrpos($file,".") + 1), $types) )
				$urls[] = content_url('/uploads/'.$dir.'/'.$file, FPD_PLUGIN_ROOT_PHP );

		}

		closedir($folder);
	}

	return $urls;

}

?>