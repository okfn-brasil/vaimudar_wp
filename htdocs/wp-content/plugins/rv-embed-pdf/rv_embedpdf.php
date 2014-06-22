<?php
/*
Plugin Name: RV Embed PDF
Description: When you upload PDF and insert a link to it with the Add Media button, it will be automatically embedded in the page using Google Docs Viewer.
Author: Rong Vang Media
Author URI: http://www.rongvang.cz
Version: 1.1
*/

function rv_embedpdf($html, $id){

	$attachment = get_post($id);
	$out = "";
	if ($attachment->post_mime_type == 'application/pdf') {
		$out .= '<iframe src="http://docs.google.com/gview?url='.$attachment->guid.'&embedded=true" style="width:100%; height:500px;" frameborder="0"></iframe>';
	}

	$out .= $html;

	return $out;
}
add_filter ( 'media_send_to_editor', 'rv_embedpdf', 20, 3);

