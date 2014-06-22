<?php
/*
Plugin Name: Share Buttons by AddToAny
Plugin URI: http://www.addtoany.com/
Description: Share buttons for your pages including AddToAny's universal sharing button, Facebook, Twitter, Google+, Pinterest, StumbleUpon and many more.  [<a href="options-general.php?page=add-to-any.php">Settings</a>]
Version: 1.3.3
Author: AddToAny
Author URI: http://www.addtoany.com/
*/

if ( ! isset( $A2A_locale ) ) {
	$A2A_locale = '';
}
	
$A2A_SHARE_SAVE_plugin_basename = plugin_basename( dirname( __FILE__ ) );

// WordPress Must-Use?
if ( basename( dirname( __FILE__ ) ) == 'mu-plugins' ) {
	// __FILE__ expected in /wp-content/mu-plugins (parent directory for auto-execution)
	// /wp-content/mu-plugins/add-to-any
	$A2A_SHARE_SAVE_plugin_url_path = WPMU_PLUGIN_URL . '/add-to-any';
	$A2A_SHARE_SAVE_plugin_dir = WPMU_PLUGIN_DIR . '/add-to-any';
} 
else {
	// /wp-content/plugins/add-to-any
	$A2A_SHARE_SAVE_plugin_url_path = WP_PLUGIN_URL . '/' . $A2A_SHARE_SAVE_plugin_basename;
	$A2A_SHARE_SAVE_plugin_dir = WP_PLUGIN_DIR . '/' . $A2A_SHARE_SAVE_plugin_basename;
}


// Fix SSL
if ( is_ssl() ) {
	$A2A_SHARE_SAVE_plugin_url_path = str_replace( 'http:', 'https:', $A2A_SHARE_SAVE_plugin_url_path );
}

$A2A_SHARE_SAVE_options = get_option( 'addtoany_options' );

function A2A_SHARE_SAVE_init() {
	global $A2A_SHARE_SAVE_plugin_url_path,
		$A2A_SHARE_SAVE_plugin_basename, 
		$A2A_SHARE_SAVE_options;
	
	if ( get_option( 'A2A_SHARE_SAVE_button' ) ) {
		A2A_SHARE_SAVE_migrate_options();
		$A2A_SHARE_SAVE_options = get_option( 'addtoany_options' );
	}
	
	load_plugin_textdomain( 'add-to-any',
		$A2A_SHARE_SAVE_plugin_url_path . '/languages',
		$A2A_SHARE_SAVE_plugin_basename . '/languages' );
		
	if ( ! isset( $A2A_SHARE_SAVE_options['display_in_excerpts'] ) || $A2A_SHARE_SAVE_options['display_in_excerpts'] != '-1' ) {
		// Excerpts use strip_tags() for the_content, so cancel if Excerpt and append to the_excerpt instead
		add_filter( 'get_the_excerpt', 'A2A_SHARE_SAVE_remove_from_content', 9 );
		add_filter( 'the_excerpt', 'A2A_SHARE_SAVE_add_to_content', 98 );
	}
}
add_filter( 'init', 'A2A_SHARE_SAVE_init' );

function A2A_SHARE_SAVE_link_vars( $linkname = false, $linkurl = false ) {
	global $post;
	
	// Set linkname
	if ( ! $linkname ) {
		if ( isset( $post ) ) {
			$linkname = get_the_title( $post->ID );
		}
		else {
			$linkname = '';
		}
	}
	
	$linkname_enc = rawurlencode( html_entity_decode( $linkname, ENT_QUOTES, 'UTF-8' ) );
	
	// Set linkurl
	if ( ! $linkurl ) {
		if ( isset( $post ) ) {
			$linkurl = get_permalink( $post->ID );
		}
		else {
			$linkurl = '';
		}
	}
	
	$linkurl_enc = rawurlencode( $linkurl );
	
	return compact( 'linkname', 'linkname_enc', 'linkurl', 'linkurl_enc' );
}

include_once( $A2A_SHARE_SAVE_plugin_dir . '/addtoany.services.php' );

// Combine ADDTOANY_SHARE_SAVE_ICONS and ADDTOANY_SHARE_SAVE_BUTTON
function ADDTOANY_SHARE_SAVE_KIT( $args = false ) {
	global $_addtoany_counter;
	
	$_addtoany_counter++;
	
	$options = get_option( 'addtoany_options' );
	
	// If universal button disabled, and not manually disabled through args
	if ( isset( $options['button'] ) && $options['button'] == 'NONE' && ! isset( $args['no_universal_button'] ) ) {
		// Pass this setting on to ADDTOANY_SHARE_SAVE_BUTTON
		// (and only via this ADDTOANY_SHARE_SAVE_KIT function because it is used for automatic placement)
		$args['no_universal_button'] = true;
	}
	
	// Custom icons enabled?
	$custom_icons = ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' && isset( $options['custom_icons_url'] ) ) ? true : false;
	
	// Set a2a_kit_size_## class name unless "icon_size" is set to '16' or custom icons are enabled
	if ( $custom_icons ) {
		$icon_size = '';
	// a2a_kit_size_32 if no icon size, or no_small_icons arg is true
	} elseif ( ! isset( $options['icon_size'] ) || isset( $args['no_small_icons'] ) && true == $args['no_small_icons'] ) {
		$icon_size = ' a2a_kit_size_32';
	} elseif ( isset( $options['icon_size'] ) && $options['icon_size'] == '16' ) {
		$icon_size = '';
	} else {
		$icon_size = ' a2a_kit_size_' . $options['icon_size'] . '';
	}
	
	$kit_additional_classes = '';
	$kit_style = '';
	
	// Add additional classNames to .a2a_kit
	if ( isset( $args['kit_additional_classes'] ) ) {
		// Append space and className(s)
		$kit_additional_classes .= ' ' . $args['kit_additional_classes'];
	}
	
	// Add addtoany_list className unless disabled (for floating buttons)
	if ( ! isset( $args['no_addtoany_list_classname'] ) ) {
		$kit_additional_classes .= ' addtoany_list';
	}
	
	// Add style attribute if set
	if ( isset( $args['kit_style'] ) ) {
		$kit_style = ' style="' . $args['kit_style'] . '"';
	}
	
	if ( ! isset( $args['html_container_open'] ) ) {
		$args['html_container_open'] = '<div class="a2a_kit' . $icon_size . $kit_additional_classes . ' a2a_target"';
		$args['html_container_open'] .= ' id="wpa2a_' . $_addtoany_counter . '"'; // ID is later removed by JS (for AJAX)
		$args['html_container_open'] .= $kit_style;
		$args['html_container_open'] .= '>';
		$args['is_kit'] = true;
	}
	if ( ! isset( $args['html_container_close'] ) )
		$args['html_container_close'] = "</div>";
	// Close container element in ADDTOANY_SHARE_SAVE_BUTTON, not prematurely in ADDTOANY_SHARE_SAVE_ICONS
	$html_container_close = $args['html_container_close']; // Cache for _BUTTON
	unset($args['html_container_close']); // Avoid passing to ADDTOANY_SHARE_SAVE_ICONS since set in _BUTTON
				
	if ( ! isset( $args['html_wrap_open'] ) )
		$args['html_wrap_open'] = "";
	if ( ! isset( $args['html_wrap_close'] ) )
		$args['html_wrap_close'] = "";
	
	$kit_html = ADDTOANY_SHARE_SAVE_ICONS( $args );
	
	$args['html_container_close'] = $html_container_close; // Re-set because unset above for _ICONS
	unset( $args['html_container_open'] ); // Avoid passing to ADDTOANY_SHARE_SAVE_BUTTON since set in _ICONS
	
	$kit_html .= ADDTOANY_SHARE_SAVE_BUTTON( $args );
	
	if ( isset( $args['output_later'] ) && $args['output_later'] )
		return $kit_html;
	else
		echo $kit_html;
}

function ADDTOANY_SHARE_SAVE_ICONS( $args = array() ) {
	// $args array: output_later, html_container_open, html_container_close, html_wrap_open, html_wrap_close, linkname, linkurl
	
	global $A2A_SHARE_SAVE_plugin_url_path, 
		$A2A_SHARE_SAVE_services;
	
	$linkname = ( isset( $args['linkname'] ) ) ? $args['linkname'] : FALSE;
	$linkurl = ( isset( $args['linkurl'] ) ) ? $args['linkurl'] : FALSE;
	
	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $linkname, $linkurl ) ); // linkname_enc, etc.
	
	$defaults = array(
		'linkname'             => '',
		'linkurl'              => '',
		'linkname_enc'         => '',
		'linkurl_enc'          => '',
		'output_later'         => false,
		'html_container_open'  => '',
		'html_container_close' => '',
		'html_wrap_open'       => '',
		'html_wrap_close'      => '',
		'no_universal_button'  => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	// Make available services extensible via plugins, themes (functions.php), etc.
	$A2A_SHARE_SAVE_services = apply_filters( 'A2A_SHARE_SAVE_services', $A2A_SHARE_SAVE_services );
	
	$service_codes = ( is_array( $A2A_SHARE_SAVE_services ) ) ? array_keys( $A2A_SHARE_SAVE_services ) : array();
	
	// Include Facebook Like and Twitter Tweet etc. unless no_special_services arg is true
	if ( ! isset( $no_special_services ) || false == $no_special_services ) {
		array_unshift( $service_codes, 'facebook_like', 'twitter_tweet', 'google_plusone', 'google_plus_share', 'pinterest_pin' );
	}
	
	$options = get_option( 'addtoany_options' );
	
	// False if "icon_size" is set to '16' or no_small_icons arg is true
	$large_icons = ( isset( $options['icon_size'] ) && $options['icon_size'] == '16' && 
		( ! isset( $no_small_icons ) || false == $no_small_icons ) 
	) ? false : true;
	
	// Directory of either custom icons or the packaged icons
	if ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' && isset( $options['custom_icons_url'] ) ) {
		// Custom icons expected at a specified URL, i.e. //example.com/blog/uploads/addtoany/icons/custom/
		$icons_dir = $options['custom_icons_url'];
		$custom_icons = true;
	} else {
		// Packaged 16px icons
		$icons_dir = $A2A_SHARE_SAVE_plugin_url_path . '/icons/';
	}
	
	// Use default services if services have not been selected yet
	$active_services = ( isset( $options['active_services'] ) ) ? $options['active_services'] : array( 'facebook', 'twitter', 'google_plus' );
	
	$ind_html = "" . $html_container_open;
	
	foreach( $active_services as $active_service ) {
		
		if ( ! in_array( $active_service, $service_codes ) )
			continue;

		if ( $active_service == 'facebook_like' || $active_service == 'twitter_tweet' || $active_service == 'google_plusone' || $active_service == 'google_plus_share' || $active_service == 'pinterest_pin' ) {
			$special_args = $args;
			$special_args['output_later'] = true;
			$link = ADDTOANY_SHARE_SAVE_SPECIAL( $active_service, $special_args );
		} else {
			$service = $A2A_SHARE_SAVE_services[ $active_service ];
			$safe_name = $active_service;
			$name = $service['name'];
			
			// If HREF specified, presume custom service (except if it's "print")
			if ( isset( $service['href'] ) && $safe_name != 'print' ) {
				$custom_service = true;
				$href = $service['href'];
				if ( isset( $service['href_js_esc'] ) ) {
					$href_linkurl = str_replace( "'", "\'", $linkurl );
					$href_linkname = str_replace( "'", "\'", $linkname );
				} else {
					$href_linkurl = $linkurl_enc;
					$href_linkname = $linkname_enc;
				}
				$href = str_replace( "A2A_LINKURL", $href_linkurl, $href );
				$href = str_replace( "A2A_LINKNAME", $href_linkname, $href );
				$href = str_replace( " ", "%20", $href );
			} else {
				$custom_service = false;
			}
	
			$icon_url = ( isset( $service['icon_url'] ) ) ? $service['icon_url'] : false;
			$icon = ( isset( $service['icon'] ) ) ? $service['icon'] : 'default'; // Just the icon filename
			$width = ( isset( $service['icon_width'] ) ) ? $service['icon_width'] : '16';
			$height = ( isset( $service['icon_height'] ) ) ? $service['icon_height'] : '16';
			
			$url = ( $custom_service ) ? $href : "http://www.addtoany.com/add_to/" . $safe_name . "?linkurl=" . $linkurl_enc . "&amp;linkname=" . $linkname_enc;
			$src = ( $icon_url ) ? $icon_url : $icons_dir . $icon . ".png";
			$class_attr = ( $custom_service ) ? "" : " class=\"a2a_button_$safe_name\"";
			
			// Remove all dimention values if using custom icons
			if ( isset( $custom_icons ) ) {
				$width = '';
				$height = '';
			}
			
			$link = $html_wrap_open . "<a$class_attr href=\"$url\" title=\"$name\" rel=\"nofollow\" target=\"_blank\">";
			$link .= ( $large_icons && ! isset( $custom_icons ) ) ? "" : "<img src=\"$src\" width=\"$width\" height=\"$height\" alt=\"$name\"/>";
			$link .= "</a>" . $html_wrap_close;
		}
		
		$ind_html .= $link;
	}
	
	$ind_html .= $html_container_close;
	
	if ( isset( $output_later ) && $output_later == true )
		return $ind_html;
	else
		echo $ind_html;
}

function ADDTOANY_SHARE_SAVE_BUTTON( $args = array() ) {
	
	// $args array = output_later, html_container_open, html_container_close, html_wrap_open, html_wrap_close, linkname, linkurl, no_universal_button

	global $A2A_SHARE_SAVE_plugin_url_path, $_addtoany_targets, $_addtoany_counter, $_addtoany_init;
	
	$linkname = (isset($args['linkname'])) ? $args['linkname'] : false;
	$linkurl = (isset($args['linkurl'])) ? $args['linkurl'] : false;
	$_addtoany_targets = ( isset( $_addtoany_targets ) ) ? $_addtoany_targets : array();

	$args = array_merge($args, A2A_SHARE_SAVE_link_vars($linkname, $linkurl)); // linkname_enc, etc.
	
	$defaults = array(
		'linkname' => '',
		'linkurl' => '',
		'linkname_enc' => '',
		'linkurl_enc' => '',
		'use_current_page' => false,
		'output_later' => false,
		'is_kit' => false,
		'html_container_open' => '',
		'html_container_close' => '',
		'html_wrap_open' => '',
		'html_wrap_close' => '',
		'no_small_icons' => false,
		'no_universal_button' => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	// If not enclosed in an AddToAny Kit, count & target this button (instead of Kit) for async loading
	if ( ! $args['is_kit'] ) {
		$_addtoany_counter++;
		$button_class = ' a2a_target';
		$button_id = ' id="wpa2a_' . $_addtoany_counter . '"';  // ID is later removed by JS (for AJAX)
	} else {
		$button_class = '';
		$button_id = '';
	}
	
	/* AddToAny button */
	
	$is_feed = is_feed();
	$button_target = '';
	$button_href_querystring = ($is_feed) ? '#url=' . $linkurl_enc . '&amp;title=' . $linkname_enc : '';
	$options = get_option( 'addtoany_options' );
	
	// If universal button is enabled
	if ( ! $args['no_universal_button'] ) {
	
		if ( ! isset( $options['button'] ) || 'A2A_SVG_32' == $options['button'] || isset( $no_small_icons ) && true == $no_small_icons ) {
			// Skip button IMG for A2A icon insertion
			$button_text = '';
		} else if ( isset( $options['button'] ) && 'CUSTOM' == $options['button'] ) {
			$button_src		= $options['button_custom'];
			$button_width	= '';
			$button_height	= '';
		} else if ( isset( $options['button'] ) && 'TEXT' == $options['button'] ) {
			$button_text	= stripslashes( $options[ 'button_text'] );
		} else {
			$button_attrs	= explode( '|', $options['button'] );
			$button_fname	= $button_attrs[0];
			$button_width	= ' width="' . $button_attrs[1] . '"';
			$button_height	= ' height="' . $button_attrs[2] . '"';
			$button_src		= $A2A_SHARE_SAVE_plugin_url_path . '/' . $button_fname;
			$button_text	= ( isset( $options['button_text'] ) ) ? stripslashes( $options['button_text'] ) : 'Share' ;
		}
		
		$style = '';
		
		if ( isset( $button_fname ) && ( $button_fname == 'favicon.png' || $button_fname == 'share_16_16.png' ) ) {
			if ( ! $is_feed ) {
				$style_bg	= 'background:url(' . $A2A_SHARE_SAVE_plugin_url_path . '/' . $button_fname . ') no-repeat scroll 4px 0px !important;';
				$style		= ' style="' . $style_bg . 'padding:0 0 0 25px;display:inline-block;height:16px;vertical-align:middle"'; // padding-left:21+4 (4=other icons padding)
			}
		}
		
		if ( isset( $button_text ) && ( ! isset( $button_fname) || ! $button_fname || $button_fname == 'favicon.png' || $button_fname == 'share_16_16.png' ) ) {
			$button = $button_text;
		} else {
			$style = '';
			$button	= '<img src="' . $button_src . '"' . $button_width . $button_height . ' alt="Share"/>';
		}
		
		$button_html = $html_container_open . $html_wrap_open . '<a class="a2a_dd' . $button_class . ' addtoany_share_save" href="http://www.addtoany.com/share_save' .$button_href_querystring . '"' . $button_id
			. $style . $button_target
			. '>' . $button . '</a>';
	
	} else {
		// Universal button disabled
		$button_html = '';
	}
	
	// Hook to disable script output
	// Example: add_filter( 'addtoany_script_disabled', '__return_true' );
	$script_disabled = apply_filters( 'addtoany_script_disabled', false );
	
	// If not a feed, not admin, and script is not disabled
	if ( ! $is_feed && ! is_admin() && ! $script_disabled ) {
		if ($use_current_page) {
			$button_config = "\n{title:document.title,"
				. "url:location.href}";
			$_addtoany_targets[] = $button_config;
		} else {
			$button_config = "\n{title:'". esc_js($linkname) . "',"
				. "url:'" . $linkurl . "'}";
			$_addtoany_targets[] = $button_config;
		}
		
		// If doing AJAX (the DOING_AJAX constant can be unreliable)
		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
			$javascript_button_config = "<script type=\"text/javascript\"><!--\n"
				. "wpa2a.targets.push("
					. $button_config
				. ");\n";
			
			if ( ! $_addtoany_init) {
				// Catch post-load event to support infinite scroll (and more?)
				$javascript_button_config .= "\nif('function'===typeof(jQuery))"
					. "jQuery(document).ready(function($){"
						. "$('body').on('post-load',function(){"
							. "if(wpa2a.script_ready)wpa2a.init();"
							. "wpa2a.script_load();" // Load external script if not already called
						. "});"
					. "});";
			}
			
			$javascript_button_config .= "\n//--></script>\n";
		}
		else $javascript_button_config = '';
		
		if ( ! $_addtoany_init) {
			$javascript_load_early = "\n<script type=\"text/javascript\"><!--\n"
				. "wpa2a.script_load();"
				. "\n//--></script>\n";
		}
		else $javascript_load_early = "";
		
		$button_html .= $javascript_load_early . $javascript_button_config;
		$_addtoany_init = true;
	}
	
	// Closing tags come after <script> to validate in case the container is a list element
	$button_html .= $html_wrap_close . $html_container_close;
	
	if ( isset( $output_later ) && $output_later == true )
		return $button_html;
	else
		echo $button_html;
}

function ADDTOANY_SHARE_SAVE_SPECIAL( $special_service_code, $args = array() ) {
	// $args array = output_later, linkname, linkurl
	
	$options = get_option( 'addtoany_options' );
	
	$linkname = ( isset( $args['linkname'] ) ) ? $args['linkname'] : FALSE;
	$linkurl = ( isset( $args['linkurl'] ) ) ? $args['linkurl'] : FALSE;
	
	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $linkname, $linkurl ) ); // linkname_enc, etc.
	extract( $args );
	
	$special_anchor_template = '<a class="a2a_button_%1$s addtoany_special_service"%2$s></a>';
	$custom_attributes = '';
	
	if ( $special_service_code == 'facebook_like' ) {
		$custom_attributes .= ( $options['special_facebook_like_options']['verb'] == 'recommend' ) ? ' data-action="recommend"' : '';
		$custom_attributes .= ' data-href="' . $linkurl . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'twitter_tweet' ) {
		$custom_attributes .= ( $options['special_twitter_tweet_options']['show_count'] == '1' ) ? ' data-count="horizontal"' : ' data-count="none"';
		$custom_attributes .= ' data-url="' . $linkurl . '"';
		$custom_attributes .= ' data-text="' . $linkname . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'google_plusone' ) {
		$custom_attributes .= ( $options['special_google_plusone_options']['show_count'] == '1' ) ? '' : ' data-annotation="none"';
		$custom_attributes .= ' data-href="' . $linkurl . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'google_plus_share' ) {
		$custom_attributes .= ( $options['special_google_plus_share_options']['show_count'] == '1' ) ? '' : ' data-annotation="none"';
		$custom_attributes .= ' data-href="' . $linkurl . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'pinterest_pin' ) {
		$custom_attributes .= ( $options['special_pinterest_pin_options']['show_count'] == '1' ) ? '' : ' data-pin-config="none"';
		$custom_attributes .= ' data-url="' . $linkurl . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	if ( isset( $output_later ) && $output_later == true )
		return $special_html;
	else
		echo $special_html;
}

if ( ! function_exists( 'A2A_menu_locale' ) ) {
	function A2A_menu_locale() {
		global $A2A_locale;
		$locale = get_locale();
		if ( $locale == 'en_US' || $locale == 'en' || $A2A_locale != '' )
			return false;
			
		$A2A_locale = 'a2a_localize = {
	Share: "' . __( "Share", "add-to-any" ) . '",
	Save: "' . __( "Save", "add-to-any" ) . '",
	Subscribe: "' . __( "Subscribe", "add-to-any" ) . '",
	Email: "' . __( "Email", "add-to-any" ) . '",
	Bookmark: "' . __( "Bookmark", "add-to-any" ) . '",
	ShowAll: "' . __( "Show all", "add-to-any" ) . '",
	ShowLess: "' . __( "Show less", "add-to-any" ) . '",
	FindServices: "' . __( "Find service(s)", "add-to-any" ) . '",
	FindAnyServiceToAddTo: "' . __( "Instantly find any service to add to", "add-to-any" ) . '",
	PoweredBy: "' . __( "Powered by", "add-to-any" ) . '",
	ShareViaEmail: "' . __( "Share via email", "add-to-any" ) . '",
	SubscribeViaEmail: "' . __( "Subscribe via email", "add-to-any" ) . '",
	BookmarkInYourBrowser: "' . __( "Bookmark in your browser", "add-to-any" ) . '",
	BookmarkInstructions: "' . __( "Press Ctrl+D or \u2318+D to bookmark this page", "add-to-any" ) . '",
	AddToYourFavorites: "' . __( "Add to your favorites", "add-to-any" ) . '",
	SendFromWebOrProgram: "' . __( "Send from any email address or email program", "add-to-any" ) . '",
	EmailProgram: "' . __( "Email program", "add-to-any" ) . '"
};
';
		return $A2A_locale;
	}
}

function ADDTOANY_SHARE_SAVE_FLOATING( $args = array() ) {
	$options = get_option( 'addtoany_options' );
	
	$floating_html = '';
	$vertical_type = ( isset( $options['floating_vertical'] ) && 'none' != $options['floating_vertical'] ) ? $options['floating_vertical'] : false;
	$horizontal_type = ( isset( $options['floating_horizontal'] ) && 'none' != $options['floating_horizontal'] ) ? $options['floating_horizontal'] : false;

	// Args are just passed on to ADDTOANY_SHARE_SAVE_KIT for now
	$defaults = array(
		'linkname' => '',
		'linkurl' => '',
		'linkname_enc' => '',
		'linkurl_enc' => '',
		'use_current_page' => true,
		'output_later' => true,
		'is_kit' => true,
		'no_addtoany_list_classname' => true,
		'no_special_services' => true,
		'no_small_icons' => true,
		'kit_additional_classes' => '',
		'kit_style' => '',
	);
	
	$args = wp_parse_args( $args, $defaults );

	// If either floating type is enabled
	if ( $vertical_type || $horizontal_type ) {
		// Vertical type?
		if ( $vertical_type ) {
			// Top position
			$position = ( isset( $options['floating_vertical_position'] ) ) ? $options['floating_vertical_position'] . 'px' : '100px';
			// Left or right offset
			$offset = ( isset( $options['floating_vertical_offset'] ) ) ? $options['floating_vertical_offset'] . 'px' : '0px';
		
			// Add a2a_vertical_style className to Kit classes
			$args['kit_additional_classes'] = 'a2a_floating_style a2a_vertical_style';
			
			// Add declarations to Kit style attribute
			if ( 'left_docked' == $vertical_type ) {
				$args['kit_style'] = 'left:' . $offset . ';top:' . $position . ';';
			} elseif ( 'right_docked' == $vertical_type ) {
				$args['kit_style'] = 'right:' . $offset . ';top:' . $position . ';';
			}
			
			$floating_html .= ADDTOANY_SHARE_SAVE_KIT( $args );
		}
		
		// Horizontal type?
		if ( $horizontal_type ) {
			// Left or right position
			$position = ( isset( $options['floating_horizontal_position'] ) ) ? $options['floating_horizontal_position'] . 'px' : '0px';
			// Bottom offset
			$offset = ( isset( $options['floating_horizontal_offset'] ) ) ? $options['floating_horizontal_offset'] . 'px' : '0px';

			// Add a2a_default_style className to Kit classes
			$args['kit_additional_classes'] = 'a2a_floating_style a2a_default_style';
			
			// Add declarations to Kit style attribute
			if ( 'left_docked' == $horizontal_type ) {
				$args['kit_style'] = 'bottom:' . $offset . ';left:' . $position . ';';
			} elseif ( 'right_docked' == $horizontal_type ) {
				$args['kit_style'] = 'bottom:' . $offset . ';right:' . $position . ';';
			}
			
			$floating_html .= ADDTOANY_SHARE_SAVE_KIT( $args );
		}
	}
	
	if ( isset( $args['output_later'] ) && $args['output_later'] == true )
		return $floating_html;
	else
		echo $floating_html;
}


function A2A_SHARE_SAVE_head_script() {
	// Hook to disable script output
	// Example: add_filter( 'addtoany_script_disabled', '__return_true' );
	$script_disabled = apply_filters( 'addtoany_script_disabled', false );
	
	if ( is_admin() || is_feed() || $script_disabled )
		return;
		
	$options = get_option( 'addtoany_options' );
	
	$http_or_https = ( is_ssl() ) ? 'https' : 'http';
	
	global $A2A_SHARE_SAVE_external_script_called;
	if ( ! $A2A_SHARE_SAVE_external_script_called ) {
		// Use local cache?
		$cache = ( isset( $options['cache'] ) && '1' == $options['cache'] ) ? true : false;
		$upload_dir = wp_upload_dir();
		$static_server = ( $cache ) ? $upload_dir['baseurl'] . '/addtoany' : $http_or_https . '://static.addtoany.com/menu';
		
		// Enternal script call + initial JS + set-once variables
		$additional_js = ( isset( $options['additional_js_variables'] ) ) ? $options['additional_js_variables'] : '' ;
		$script_configs = ( ( $cache ) ? "\n" . 'a2a_config.static_server="' . $static_server . '";' : '' )
			. ( ( isset( $options['onclick'] ) && '1' == $options['onclick'] ) ? "\n" . 'a2a_config.onclick=1;' : '' )
			. ( ( isset( $options['show_title'] ) && '1' == $options['show_title'] ) ? "\n" . 'a2a_config.show_title=1;' : '' )
			. ( ( $additional_js ) ? "\n" . stripslashes( $additional_js ) : '' );
		$A2A_SHARE_SAVE_external_script_called = true;
	}
	else {
		$script_configs = "";
	}
	
	$javascript_header = "\n" . '<script type="text/javascript">' . "<!--\n"
	
			. "var a2a_config=a2a_config||{},"
			. "wpa2a={done:false,"
			. "html_done:false,"
			. "script_ready:false,"
			. "script_load:function(){"
				. "var a=document.createElement('script'),"
					. "s=document.getElementsByTagName('script')[0];"
				. "a.type='text/javascript';a.async=true;"
				. "a.src='" . $static_server . "/page.js';"
				. "s.parentNode.insertBefore(a,s);"
				. "wpa2a.script_load=function(){};"
			. "},"
			. "script_onready:function(){"
				. "if(a2a.type=='page'){" // Check a2a internal var to ensure script loaded is page.js not feed.js
					. "wpa2a.script_ready=true;"
					. "if(wpa2a.html_done)wpa2a.init();"
				. "}"
			. "},"
			. "init:function(){"
				. "for(var i=0,el,target,targets=wpa2a.targets,length=targets.length;i<length;i++){"
					. "el=document.getElementById('wpa2a_'+(i+1));"
					. "target=targets[i];"
					. "a2a_config.linkname=target.title;"
					. "a2a_config.linkurl=target.url;"
					. "if(el){"
						. "a2a.init('page',{target:el});"
						. "el.id='';" // Remove ID so AJAX can reuse the same ID
					. "}"
					. "wpa2a.done=true;"
				. "}"
				. "wpa2a.targets=[];" // Empty targets array so AJAX can reuse from index 0
			. "}"
		. "};"
		
		. "a2a_config.tracking_callback=['ready',wpa2a.script_onready];"
		. A2A_menu_locale()
		. $script_configs
		
		. "\n//--></script>\n";
	
	 echo $javascript_header;
}

add_action( 'wp_head', 'A2A_SHARE_SAVE_head_script' );

function A2A_SHARE_SAVE_footer_script() {
	global $_addtoany_targets;
	
	// Hook to disable script output
	// Example: add_filter( 'addtoany_script_disabled', '__return_true' );
	$script_disabled = apply_filters( 'addtoany_script_disabled', false );
	
	if ( is_admin() || is_feed() || $script_disabled )
		return;
		
	$_addtoany_targets = ( isset( $_addtoany_targets ) ) ? $_addtoany_targets : array();
	
	$floating_html = ADDTOANY_SHARE_SAVE_FLOATING( array( 'output_later' => true ) );
	
	$javascript_footer = "\n" . '<script type="text/javascript">' . "<!--\n"
		. "wpa2a.targets=["
			. implode( ",", $_addtoany_targets )
		. "];\n"
		. "wpa2a.html_done=true;"
		. "if(wpa2a.script_ready&&!wpa2a.done)wpa2a.init();" // External script may load before html_done=true, but will only init if html_done=true.  So call wpa2a.init() if external script is ready, and if wpa2a.init() hasn't been called already.  Otherwise, wait for callback to call wpa2a.init()
		. "wpa2a.script_load();" // Load external script if not already called with the first AddToAny button.  Fixes issues where first button code is processed internally but without actual code output
		. "\n//--></script>\n";
	
	echo $floating_html . $javascript_footer;
}

add_action( 'wp_footer', 'A2A_SHARE_SAVE_footer_script' );



function A2A_SHARE_SAVE_theme_hooks_check() {
	$template_directory = get_template_directory();
	
	// If footer.php exists in the current theme, scan for "wp_footer"
	$file = $template_directory . '/footer.php';
	if ( is_file( $file ) ) {
		$search_string = "wp_footer";
		$file_lines = @file( $file );
		
		foreach ( $file_lines as $line ) {
			$searchCount = substr_count( $line, $search_string );
			if ( $searchCount > 0 ) {
				return true;
			}
		}
		
		// wp_footer() not found:
		echo "<div class=\"update-nag\">" . __( "Your theme needs to be fixed. To fix your theme, use the <a href=\"theme-editor.php\">Theme Editor</a> to insert <code>&lt;?php wp_footer(); ?&gt;</code> just before the <code>&lt;/body&gt;</code> line of your theme's <code>footer.php</code> file." ) . "</div>";
	}
	
	// If header.php exists in the current theme, scan for "wp_head"
	$file = $template_directory . '/header.php';
	if ( is_file( $file ) ) {
		$search_string = "wp_head";
		$file_lines = @file( $file );
		
		foreach ( $file_lines as $line ) {
			$searchCount = substr_count( $line, $search_string );
			if ( $searchCount > 0 ) {
				return true;
			}
		}
		
		// wp_footer() not found:
		echo "<div class=\"update-nag\">" . __( "Your theme needs to be fixed. To fix your theme, use the <a href=\"theme-editor.php\">Theme Editor</a> to insert <code>&lt;?php wp_head(); ?&gt;</code> just before the <code>&lt;/head&gt;</code> line of your theme's <code>header.php</code> file." ) . "</div>";
	}
}

function A2A_SHARE_SAVE_auto_placement( $title ) {
	global $A2A_SHARE_SAVE_auto_placement_ready;
	$A2A_SHARE_SAVE_auto_placement_ready = true;
	
	return $title;
}


/**
 * Remove the_content filter and add it for next time 
 */
function A2A_SHARE_SAVE_remove_from_content( $content ) {
	remove_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
	add_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content_next_time', 98 );
	
	return $content;
}

/**
 * Apply the_content filter "next time"
 */
function A2A_SHARE_SAVE_add_to_content_next_time( $content ) {
	add_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
	
	return $content;
}


function A2A_SHARE_SAVE_add_to_content( $content ) {
	global $A2A_SHARE_SAVE_auto_placement_ready;
	$is_feed = is_feed();
	$options = get_option( 'addtoany_options' );
	$sharing_disabled = get_post_meta( get_the_ID(), 'sharing_disabled', true );
	$sharing_disabled = apply_filters( 'addtoany_sharing_disabled', $sharing_disabled );
	
	if ( ! $A2A_SHARE_SAVE_auto_placement_ready )
		return $content;
		
	if ( get_post_status( get_the_ID() ) == 'private' )
		return $content;
		
	// Disabled for this post?
	if ( ! empty( $sharing_disabled ) )
		return $content;
	
	if ( 
		( 
			// Legacy tags
			// <!--sharesave--> tag
			strpos( $content, '<!--sharesave-->' ) === false || 
			// <!--nosharesave--> tag
			strpos( $content, '<!--nosharesave-->' ) !== false
		) &&
		(
			// Posts
			// All posts
			( ! is_page() && isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) ||
			// Front page posts		
			( is_home() && isset( $options['display_in_posts_on_front_page'] ) && $options['display_in_posts_on_front_page'] == '-1' ) ||
			// Archive page posts (Category, Tag, Author and Date pages)
			( is_archive() && isset( $options['display_in_posts_on_archive_pages'] ) && $options['display_in_posts_on_archive_pages'] == '-1' ) ||
			// Search results posts (same as Archive page posts option)
			( is_search() && isset( $options['display_in_posts_on_archive_pages'] ) && $options['display_in_posts_on_archive_pages'] == '-1' ) || 
			// Posts in feed
			( $is_feed && isset( $options['display_in_feed'] ) && $options['display_in_feed'] == '-1' ) ||
			
			// Pages
			// Individual pages
			( is_page() && isset( $options['display_in_pages'] ) && $options['display_in_pages'] == '-1' ) ||
			// <!--nosharesave--> legacy tag
			( (strpos( $content, '<!--nosharesave-->') !== false ) )
		)
	) {
		return $content;
	}
	
	$kit_args = array(
		"output_later" => true,
		"is_kit" => ( $is_feed ) ? false : true,
	);
	
	if ( ! $is_feed ) {
		$container_wrap_open = '<div class="addtoany_share_save_container %s">'; // Contains placeholder
		$container_wrap_close = '</div>';
	} else { // Is feed
		$container_wrap_open = '<p>';
		$container_wrap_close = '</p>';
		$kit_args['html_container_open'] = '';
		$kit_args['html_container_close'] = '';
		$kit_args['html_wrap_open'] = '';
		$kit_args['html_wrap_close'] = '';
	}
	
	$options['position'] = isset( $options['position'] ) ? $options['position'] : 'bottom';
	
	if ($options['position'] == 'both' || $options['position'] == 'top') {
		// Prepend to content
		$content = sprintf( $container_wrap_open, 'addtoany_content_top' ) . ADDTOANY_SHARE_SAVE_KIT($kit_args) . $container_wrap_close . $content;
	}
	if ( $options['position'] == 'bottom' || $options['position'] == 'both') {
		// Append to content
		$content .= sprintf( $container_wrap_open, 'addtoany_content_bottom' ) . ADDTOANY_SHARE_SAVE_KIT($kit_args) . $container_wrap_close;
	}
	
	return $content;
}

// Only automatically output button code after the_title has been called - to avoid premature calling from misc. the_content filters (especially meta description)
add_filter( 'the_title', 'A2A_SHARE_SAVE_auto_placement', 9 );
add_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );


// [addtoany url="http://example.com/page.html" title="Some Example Page"]
function A2A_SHARE_SAVE_shortcode( $attributes ) {
	extract( shortcode_atts( array(
		'url' => 'something',
		'title' => 'something else',
	), $attributes ) );
	$linkname = ( isset( $attributes['title'] ) ) ? $attributes['title'] : false;
	$linkurl = ( isset( $attributes['url'] ) ) ? $attributes['url'] : false;
	$output_later = TRUE;

	return '<div class="addtoany_shortcode">'
		. ADDTOANY_SHARE_SAVE_KIT( compact( 'linkname', 'linkurl', 'output_later' ) )
		. '</div>';
}

add_shortcode( 'addtoany', 'A2A_SHARE_SAVE_shortcode' );


function A2A_SHARE_SAVE_stylesheet() {
	global $A2A_SHARE_SAVE_options, $A2A_SHARE_SAVE_plugin_url_path;
	
	$options = $A2A_SHARE_SAVE_options;
	
	// Use stylesheet?
	if ( ! isset( $options['inline_css'] ) || $options['inline_css'] != '-1' && ! is_admin() ) {
	
		wp_enqueue_style( 'A2A_SHARE_SAVE', $A2A_SHARE_SAVE_plugin_url_path . '/addtoany.min.css', false, '1.6' );
	
		// wp_add_inline_style requires WP 3.3+
		if ( '3.3' <= get_bloginfo( 'version' ) ) {
		
			// Prepare inline CSS for media queries on floating bars
			$inline_css = '';
			
			$vertical_type = ( isset( $options['floating_vertical'] ) && 'none' != $options['floating_vertical'] ) ? $options['floating_vertical'] : false;
			$horizontal_type = ( isset( $options['floating_horizontal'] ) && 'none' != $options['floating_horizontal'] ) ? $options['floating_horizontal'] : false;
			
			// If vertical bar is enabled
			if ( $vertical_type && 
				// and respsonsiveness is enabled
				( ! isset( $options['floating_vertical_responsive'] ) || '-1' != $options['floating_vertical_responsive'] )
			) {
				
				// Get min-width for media query
				$vertical_max_width = ( 
					isset( $options['floating_vertical_responsive_max_width'] ) && 
					is_numeric( $options['floating_vertical_responsive_max_width'] ) 
				) ? $options['floating_vertical_responsive_max_width'] : '980';
				
				// Set media query
				$inline_css .= '@media screen and (max-width:' . $vertical_max_width . 'px){' . "\n"
					. '.a2a_floating_style.a2a_vertical_style{display:none;}' . "\n"
					. '}';
				
			}
			
			// If horizontal bar is enabled
			if ( $horizontal_type && 
				// and respsonsiveness is enabled
				( ! isset( $options['floating_horizontal_responsive'] ) || '-1' != $options['floating_horizontal_responsive'] )
			) {
				
				// Get max-width for media query
				$horizontal_min_width = ( 
					isset( $options['floating_horizontal_responsive_min_width'] ) && 
					is_numeric( $options['floating_horizontal_responsive_min_width'] ) 
				) ? $options['floating_horizontal_responsive_min_width'] : '981';
				
				// If there is inline CSS already
				if ( 0 < strlen( $inline_css ) ) {
					// Insert newline
					$inline_css .= "\n";
				}
				
				// Set media query
				$inline_css .= '@media screen and (min-width:' . $horizontal_min_width . 'px){' . "\n"
					. '.a2a_floating_style.a2a_default_style{display:none;}' . "\n"
					. '}';
				
			}
			
			// If there is inline CSS
			if ( 0 < strlen( $inline_css ) ) {
			
				// Insert inline CSS
				wp_add_inline_style( 'A2A_SHARE_SAVE', $inline_css );	
			}
		
		}
		
	}
	
}

add_action( 'wp_print_styles', 'A2A_SHARE_SAVE_stylesheet' );



/**
 * Cache AddToAny
 */

function A2A_SHARE_SAVE_refresh_cache() {
	$contents = wp_remote_fopen( 'http://www.addtoany.com/ext/updater/files_list/' );
	$file_urls = explode( "\n", $contents, 20 );
	$upload_dir = wp_upload_dir();
	
	// Make directory if needed
	if ( ! wp_mkdir_p( dirname( $upload_dir['basedir'] . '/addtoany/foo' ) ) ) {
		$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), dirname( $new_file ) );
		return array( 'error' => $message );
	}
	
	if ( count( $file_urls ) > 0 ) {
		for ( $i = 0; $i < count( $file_urls ); $i++ ) {
			// Download files
			$file_url = trim( $file_urls[ $i ] );
			$file_name = substr( strrchr( $file_url, '/' ), 1, 99 );
			
			// Place files in uploads/addtoany directory
			wp_get_http( $file_url, $upload_dir['basedir'] . '/addtoany/' . $file_name );
		}
	}
}

function A2A_SHARE_SAVE_schedule_cache() {
	// WP "Cron" requires WP version 2.1
	$timestamp = wp_next_scheduled( 'A2A_SHARE_SAVE_refresh_cache' );
	if ( ! $timestamp) {
		// Only schedule if currently unscheduled
		wp_schedule_event( time(), 'daily', 'A2A_SHARE_SAVE_refresh_cache' );
	}
}

function A2A_SHARE_SAVE_unschedule_cache() {
	$timestamp = wp_next_scheduled( 'A2A_SHARE_SAVE_refresh_cache' );
	wp_unschedule_event( $timestamp, 'A2A_SHARE_SAVE_refresh_cache' );
}



/**
 * Admin Options
 */

if ( is_admin() ) {
	include_once( $A2A_SHARE_SAVE_plugin_dir . '/addtoany.admin.php' );
}

function A2A_SHARE_SAVE_add_menu_link() {
	if ( current_user_can( 'manage_options' ) ) {
		$page = add_options_page(
			'AddToAny: '. __( "Share/Save", "add-to-any" ) . " " . __( "Settings" )
			, __( "AddToAny", "add-to-any" )
			, 'activate_plugins' 
			, basename( __FILE__ )
			, 'A2A_SHARE_SAVE_options_page'
		);
		
		/* Using registered $page handle to hook script load, to only load in AddToAny admin */
		add_filter( 'admin_print_scripts-' . $page, 'A2A_SHARE_SAVE_scripts' );
	}
}

add_filter( 'admin_menu', 'A2A_SHARE_SAVE_add_menu_link' );

function A2A_SHARE_SAVE_widget_init() {
	global $A2A_SHARE_SAVE_plugin_dir;
	
	include_once( $A2A_SHARE_SAVE_plugin_dir . '/addtoany.widget.php' );
	register_widget( 'A2A_SHARE_SAVE_Widget' );
}

add_action( 'widgets_init', 'A2A_SHARE_SAVE_widget_init' );

// Place in Option List on Settings > Plugins page 
function A2A_SHARE_SAVE_actlinks( $links, $file ) {
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	
	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}
	
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=add-to-any.php">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	
	return $links;
}

add_filter( 'plugin_action_links', 'A2A_SHARE_SAVE_actlinks', 10, 2 );
