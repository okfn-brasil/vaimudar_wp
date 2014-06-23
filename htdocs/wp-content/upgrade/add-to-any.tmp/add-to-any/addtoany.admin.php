<?php

/**
 * Post options
 */
function A2A_SHARE_SAVE_add_meta_box() {
	// get_post_types() only included in WP 2.9/3.0
	$post_types = ( function_exists( 'get_post_types' ) ) ? get_post_types( array( 'public' => true ) ) : array( 'post', 'page' ) ;
	
	$title = apply_filters( 'A2A_SHARE_SAVE_meta_box_title', __( 'AddToAny', 'add-to-any' ) );
	foreach( $post_types as $post_type ) {
		add_meta_box( 'A2A_SHARE_SAVE_meta', $title, 'A2A_SHARE_SAVE_meta_box_content', $post_type, 'advanced', 'high' );
	}
}

function A2A_SHARE_SAVE_meta_box_content( $post ) {
	do_action( 'start_A2A_SHARE_SAVE_meta_box_content', $post );

	$disabled = get_post_meta( $post->ID, 'sharing_disabled', true ); ?>

	<p>
		<label for="enable_post_addtoany_sharing">
			<input type="checkbox" name="enable_post_addtoany_sharing" id="enable_post_addtoany_sharing" value="1" <?php checked( empty( $disabled ) ); ?>>
			<?php _e( 'Show sharing buttons.' , 'add-to-any'); ?>
		</label>
		<input type="hidden" name="addtoany_sharing_status_hidden" value="1" />
	</p>

	<?php
	do_action( 'end_A2A_SHARE_SAVE_meta_box_content', $post );
}

function A2A_SHARE_SAVE_meta_box_save( $post_id ) {
	// If this is an autosave, this form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	// Save sharing_disabled if "Show sharing buttons" checkbox is unchecked
	if ( isset( $_POST['post_type'] ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			if ( isset( $_POST['addtoany_sharing_status_hidden'] ) ) {
				if ( !isset( $_POST['enable_post_addtoany_sharing'] ) ) {
					update_post_meta( $post_id, 'sharing_disabled', 1 );
				} else {
					delete_post_meta( $post_id, 'sharing_disabled' );
				}
			}
		}
	}

	return $post_id;
}

add_action( 'admin_init', 'A2A_SHARE_SAVE_add_meta_box' );
add_action( 'save_post', 'A2A_SHARE_SAVE_meta_box_save' );

/**
 * Migrate old AddToAny options
 */
function A2A_SHARE_SAVE_migrate_options() {
	
	$options = array(
		'inline_css' => '1', // Modernly used for "Use CSS Stylesheet?"
		'cache' => '-1',
		'display_in_posts_on_front_page' => '1',
		'display_in_posts_on_archive_pages' => '1',
		'display_in_posts' => '1',
		'display_in_pages' => '1',
		'display_in_feed' => '1',
		'show_title' => '-1',
		'onclick' => '-1',
		'button' => 'A2A_SVG_32',
		'button_custom' => '',
		'additional_js_variables' => '',
		'button_text' => 'Share',
		'display_in_excerpts' => '1',
		'active_services' => Array(),
	);
	
	$namespace = 'A2A_SHARE_SAVE_';
	
	foreach ( $options as $option_name => $option_value ) {
		$old_option_name = $namespace . $option_name;
		$old_option_value = get_option( $old_option_name );
		
		if( $old_option_value === false ) {
			// Default value
			$options[ $option_name ] = $option_value;
		} else {
			// Old value
			$options[ $option_name ] = $old_option_value;
		}
		
		delete_option( $old_option_name );
	}
	
	update_option( 'addtoany_options', $options );
	
	$deprecated_options = array(
		'button_opens_new_window',
		'hide_embeds',
	);
	
	foreach ( $deprecated_options as $option_name ) {
		delete_option( $namespace . $option_name );
	}
	
}

/**
 * Adds a WordPress pointer to Settings menu, so user knows where to configure AddToAny
 */
function A2A_SHARE_SAVE_enqueue_pointer_script_style( $hook_suffix ) {
	
	// Requires WP 3.3
	if ( get_bloginfo( 'version' ) < '3.3' ) {
		return;
	}
	
	// Assume pointer shouldn't be shown
	$enqueue_pointer_script_style = false;

	// Get array list of dismissed pointers for current user and convert it to array
	$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

	// Check if our pointer is not among dismissed ones
	if ( !in_array( 'addtoany_settings_pointer', $dismissed_pointers ) ) {
		$enqueue_pointer_script_style = true;
		
		// Add footer scripts using callback function
		add_action( 'admin_print_footer_scripts', 'A2A_SHARE_SAVE_pointer_print_scripts' );
	}

	// Enqueue pointer CSS and JS files, if needed
	if ( $enqueue_pointer_script_style ) {
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}
	
}
if ( ! $A2A_SHARE_SAVE_options ) {
	// Only show the pointer when no AddToAny options have been set
	add_action( 'admin_enqueue_scripts', 'A2A_SHARE_SAVE_enqueue_pointer_script_style' );
}

function A2A_SHARE_SAVE_pointer_print_scripts() {

	$pointer_content  = '<h3>AddToAny Sharing Settings</h3>';
	$pointer_content .= '<p>To customize your AddToAny share buttons, click &quot;AddToAny&quot; in the Settings menu.</p>';
?>
	
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		$('#menu-settings').pointer({
			content:		'<?php echo $pointer_content; ?>',
			position:		{
								edge:	'left', // arrow direction
								align:	'center' // vertical alignment
							},
			pointerWidth:	350,
			close:			function() {
								$.post( ajaxurl, {
										pointer: 'addtoany_settings_pointer', // pointer ID
										action: 'dismiss-wp-pointer'
								});
							}
		}).pointer('open');
	});
	//]]>
	</script>

<?php
}

function A2A_SHARE_SAVE_options_page() {

	global $A2A_SHARE_SAVE_plugin_url_path,
		$A2A_SHARE_SAVE_services;
	
	// Require admin privs
	if ( ! current_user_can( 'manage_options' ) )
		return false;
	
	$new_options = array();
	
	$namespace = 'A2A_SHARE_SAVE_';
	
	// Make available services extensible via plugins, themes (functions.php), etc.
	$A2A_SHARE_SAVE_services = apply_filters( 'A2A_SHARE_SAVE_services', $A2A_SHARE_SAVE_services );
	
	// Which tab is selected?
	$possible_screens = array( 'default', 'floating' );
	$current_screen = ( isset( $_GET['action'] ) && in_array( $_GET['action'], $possible_screens ) ) ? $_GET['action'] : 'default';
	
	if ( isset( $_POST['Submit'] ) ) {
		
		// Nonce verification 
		check_admin_referer( 'add-to-any-update-options' );
		
		if ( 'floating' == $current_screen ) {
			// Floating options screen
			
			$possible_floating_values = array( 'left_docked', 'right_docked', 'none' );
			
			$new_options['floating_vertical'] = ( in_array( $_POST['A2A_SHARE_SAVE_floating_vertical'], $possible_floating_values ) ) ? $_POST['A2A_SHARE_SAVE_floating_vertical'] : 'none';
			$new_options['floating_horizontal'] = ( in_array( $_POST['A2A_SHARE_SAVE_floating_horizontal'], $possible_floating_values ) ) ? $_POST['A2A_SHARE_SAVE_floating_horizontal'] : 'none';
			
			$new_options['floating_vertical_position'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_position'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_position'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_position'] : '100';
			
			$new_options['floating_vertical_offset'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] : '0';
			
			$new_options['floating_vertical_responsive'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive'] ) && 
				'1' == $_POST['A2A_SHARE_SAVE_floating_vertical_responsive']
			) ? '1' : '-1';
			
			$new_options['floating_vertical_responsive_max_width'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] : '980';
			
			$new_options['floating_horizontal_position'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] : '0';
			
			$new_options['floating_horizontal_offset'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] : '0';
			
			
			$new_options['floating_horizontal_responsive'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive'] ) && 
				'1' == $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive']
			) ? '1' : '-1';
			
			$new_options['floating_horizontal_responsive_min_width'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] : '981';
			
		} else {
			// Standard options screen
			
			$new_options['position'] = ( isset( $_POST['A2A_SHARE_SAVE_position'] ) ) ? $_POST['A2A_SHARE_SAVE_position'] : 'bottom';
			$new_options['display_in_posts_on_front_page'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts_on_front_page'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts_on_front_page'] == '1' ) ? '1' : '-1';
			$new_options['display_in_posts_on_archive_pages'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts_on_archive_pages'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts_on_archive_pages'] == '1' ) ? '1' : '-1';
			$new_options['display_in_excerpts'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_excerpts'] ) && $_POST['A2A_SHARE_SAVE_display_in_excerpts'] == '1' ) ? '1' : '-1';
			$new_options['display_in_posts'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts'] == '1' ) ? '1' : '-1';
			$new_options['display_in_pages'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_pages'] ) && $_POST['A2A_SHARE_SAVE_display_in_pages'] == '1' ) ? '1' : '-1';
			$new_options['display_in_feed'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_feed'] ) && $_POST['A2A_SHARE_SAVE_display_in_feed'] == '1' ) ? '1' : '-1';
			$new_options['show_title'] = ( isset( $_POST['A2A_SHARE_SAVE_show_title'] ) && $_POST['A2A_SHARE_SAVE_show_title'] == '1' ) ? '1' : '-1';
			$new_options['onclick'] = ( isset( $_POST['A2A_SHARE_SAVE_onclick'] ) && $_POST['A2A_SHARE_SAVE_onclick'] == '1' ) ? '1' : '-1';
			$new_options['icon_size'] = ( isset( $_POST['A2A_SHARE_SAVE_icon_size'] ) ) ? $_POST['A2A_SHARE_SAVE_icon_size'] : '';
			$new_options['button'] = ( isset( $_POST['A2A_SHARE_SAVE_button'] ) ) ? $_POST['A2A_SHARE_SAVE_button'] : '';
			$new_options['button_custom'] = ( isset( $_POST['A2A_SHARE_SAVE_button_custom'] ) ) ? $_POST['A2A_SHARE_SAVE_button_custom'] : '';
			$new_options['additional_js_variables'] = ( isset( $_POST['A2A_SHARE_SAVE_additional_js_variables'] ) ) ? trim( $_POST['A2A_SHARE_SAVE_additional_js_variables'] ) : '';
			$new_options['custom_icons'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons'] ) && $_POST['A2A_SHARE_SAVE_custom_icons'] == 'url' ) ? 'url' : '-1';
			$new_options['custom_icons_url'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons_url'] ) ) ? trailingslashit( $_POST['A2A_SHARE_SAVE_custom_icons_url'] ) : '';
			$new_options['inline_css'] = ( isset( $_POST['A2A_SHARE_SAVE_inline_css'] ) && $_POST['A2A_SHARE_SAVE_inline_css'] == '1') ? '1' : '-1';
			$new_options['cache'] = ( isset( $_POST['A2A_SHARE_SAVE_cache'] ) && $_POST['A2A_SHARE_SAVE_cache'] == '1' ) ? '1' : '-1';
			
			// Schedule cache refresh?
			if ( isset( $_POST['A2A_SHARE_SAVE_cache'] ) && $_POST['A2A_SHARE_SAVE_cache'] == '1' ) {
				A2A_SHARE_SAVE_schedule_cache();
				A2A_SHARE_SAVE_refresh_cache();
			} else {
				A2A_SHARE_SAVE_unschedule_cache();
			}
			
			// Store desired text if 16 x 16px buttons or text-only is chosen:
			if ( $new_options['button'] == 'favicon.png|16|16' )
				$new_options['button_text'] = $_POST['A2A_SHARE_SAVE_button_favicon_16_16_text'];
			elseif ( $new_options['button'] == 'share_16_16.png|16|16' )
				$new_options['button_text'] = $_POST['A2A_SHARE_SAVE_button_share_16_16_text'];
			else
				$new_options['button_text'] = ( trim( $_POST['A2A_SHARE_SAVE_button_text'] ) != '' ) ? $_POST['A2A_SHARE_SAVE_button_text'] : __('Share','add-to-any');
				
			// Store chosen individual services to make active
			$active_services = array();
			if ( ! isset( $_POST['A2A_SHARE_SAVE_active_services'] ) )
				$_POST['A2A_SHARE_SAVE_active_services'] = array();
			foreach ( $_POST['A2A_SHARE_SAVE_active_services'] as $dummy=>$sitename )
				$active_services[] = substr( $sitename, 7 );
			$new_options['active_services'] = $active_services;
			
			// Store special service options
			$new_options['special_facebook_like_options'] = array(
				'verb' => ( ( isset( $_POST['addtoany_facebook_like_verb'] ) && $_POST['addtoany_facebook_like_verb'] == 'recommend') ? 'recommend' : 'like' )
			);
			$new_options['special_twitter_tweet_options'] = array(
				'show_count' => ( ( isset( $_POST['addtoany_twitter_tweet_show_count'] ) && $_POST['addtoany_twitter_tweet_show_count'] == '1' ) ? '1' : '-1' )
			);
			$new_options['special_google_plusone_options'] = array(
				'show_count' => ( ( isset( $_POST['addtoany_google_plusone_show_count'] ) && $_POST['addtoany_google_plusone_show_count'] == '1' ) ? '1' : '-1' )
			);
			$new_options['special_google_plus_share_options'] = array(
				'show_count' => ( ( isset( $_POST['addtoany_google_plus_share_show_count'] ) && $_POST['addtoany_google_plus_share_show_count'] == '1' ) ? '1' : '-1' )
			);
			$new_options['special_pinterest_pin_options'] = array(
				'show_count' => ( ( isset( $_POST['addtoany_pinterest_pin_show_count'] ) && $_POST['addtoany_pinterest_pin_show_count'] == '1' ) ? '1' : '-1' )
			);
			
		}		
		
		// Get all existing AddToAny options
		$existing_options = get_option( 'addtoany_options' );
		
		// Merge $new_options into $existing_options to retain AddToAny options from all other screens/tabs
		if ( $existing_options ) {
			$new_options = array_merge( $existing_options, $new_options );
		}
		
		update_option( 'addtoany_options', $new_options );
		
		?>
		<div class="updated fade"><p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
		<?php
		
	} else if ( isset( $_POST['Reset'] ) ) {
		// Nonce verification 
		check_admin_referer( 'add-to-any-update-options' );
		
		delete_option( 'addtoany_options' );
	}

	$options = get_option( 'addtoany_options' );
	
	function position_in_content( $options, $option_box = false ) {
		
		if ( ! isset( $options['position'] ) ) {
			$options['position'] = 'bottom';
		}
		
		$positions = array(
			'bottom' => array(
				'selected' => ( 'bottom' == $options['position'] ) ? ' selected="selected"' : '',
				'string' => __( 'bottom', 'add-to-any' )
			),
			'top' => array(
				'selected' => ( 'top' == $options['position'] ) ? ' selected="selected"' : '',
				'string' => __( 'top', 'add-to-any' )
			),
			'both' => array(
				'selected' => ( 'both' == $options['position'] ) ? ' selected="selected"' : '',
				'string' => __( 'top &amp; bottom', 'add-to-any' )
			)
		);
		
		if ( $option_box ) {
			$html = '</label>';
			$html .= '<label>'; // Label needed to prevent checkmark toggle on SELECT click 
			$html .= '<select name="A2A_SHARE_SAVE_position">';
			$html .= '<option value="bottom"' . $positions['bottom']['selected'] . '>' . $positions['bottom']['string'] . '</option>';
			$html .= '<option value="top"' . $positions['top']['selected'] . '>' . $positions['top']['string'] . '</option>';
			$html .= '<option value="both"' . $positions['both']['selected'] . '>' . $positions['both']['string'] . '</option>';
			$html .= '</select>';
			
			return $html;
		} else {
			$html = '<span class="A2A_SHARE_SAVE_position">';
			$html .= $positions[$options['position']]['string'];
			$html .= '</span>';
			
			return $html;
		}
	}
	
	?>
	
	<?php A2A_SHARE_SAVE_theme_hooks_check(); ?>
	
	<div class="wrap">
	
	<h2><?php _e( 'AddToAny Share Settings', 'add-to-any' ); ?></h2>
	
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'options-general.php?page=add-to-any.php' ); ?>" class="nav-tab<?php if ( 'default' == $current_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Standard' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'floating' ), admin_url( 'options-general.php?page=add-to-any.php' ) ) ); ?>" class="nav-tab<?php if ( 'floating' == $current_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Floating' ); ?></a>
	</h2>

	<form id="addtoany_admin_form" method="post" action="">
	
	<?php wp_nonce_field('add-to-any-update-options'); ?>

		<table class="form-table">
		
		<?php if ( 'default' == $current_screen ) : ?>
			<tr valign="top">
			<th scope="row"><?php _e("Icon Size", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input type="radio" name="A2A_SHARE_SAVE_icon_size" value="32"<?php if ( ! isset( $options['icon_size'] ) || '32' == $options['icon_size'] ) echo ' checked="checked"'; ?>> <?php _e('Large', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_icon_size" value="16"<?php if ( isset( $options['icon_size'] ) && '16' == $options['icon_size'] ) echo ' checked="checked"'; ?>> <?php _e('Small', 'add-to-any'); ?></label>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e("Standalone Buttons", "add-to-any"); ?></th>
			<td><fieldset>
				<ul id="addtoany_services_sortable" class="addtoany_admin_list addtoany_override a2a_kit_size_32">
					<li class="dummy"><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path; ?>/icons/transparent.gif" width="16" height="16" alt="" /></li>
				</ul>
				<p id="addtoany_services_info"><?php _e("Choose the services you want below. &nbsp;Click a chosen service again to remove. &nbsp;Reorder services by dragging and dropping as they appear above.", "add-to-any"); ?></p>
				<ul id="addtoany_services_selectable" class="addtoany_admin_list">
					<li id="a2a_wp_facebook_like" class="addtoany_special_service" title="Facebook Like button">
						<span><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/icons/facebook_like.png'; ?>" width="50" height="20" alt="Facebook Like" /></span>
					</li>
					<li id="a2a_wp_twitter_tweet" class="addtoany_special_service" title="Twitter Tweet button">
						<span><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/icons/twitter_tweet.png'; ?>" width="55" height="20" alt="Twitter Tweet" /></span>
					</li>
					<li id="a2a_wp_google_plusone" class="addtoany_special_service" title="Google +1 button">
						<span><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/icons/google_plusone.png'; ?>" width="32" height="20" alt="Google +1" /></span>
					</li>
					<li id="a2a_wp_google_plus_share" class="addtoany_special_service" title="Google+ Share button">
						<span><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/icons/google_plus_share.png'; ?>" width="56" height="20" alt="Google+ Share" /></span>
					</li>
					<li id="a2a_wp_pinterest_pin" class="addtoany_special_service" title="Pinterest Pin It button">
						<span><img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/icons/pinterest_pin.png'; ?>" width="40" height="20" alt="Pinterest Pin It" /></span>
					</li>
				<?php
					// Show all services
					foreach ($A2A_SHARE_SAVE_services as $service_safe_name=>$site) { 
						if (isset($site['href']))
							$custom_service = true;
						else
							$custom_service = false;
						if ( ! isset($site['icon']))
							$site['icon'] = 'default';
					?>
						<li data-addtoany-icon-name="<?php echo $site['icon']; ?>" id="a2a_wp_<?php echo $service_safe_name; ?>" title="<?php echo $site['name']; ?>">
							<span><img src="<?php echo (isset($site['icon_url'])) ? $site['icon_url'] : $A2A_SHARE_SAVE_plugin_url_path.'/icons/'.$site['icon'].'.png'; ?>" width="<?php echo (isset($site['icon_width'])) ? $site['icon_width'] : '16'; ?>" height="<?php echo (isset($site['icon_height'])) ? $site['icon_height'] : '16'; ?>" alt="" /><?php echo $site['name']; ?></span>
						</li>
				<?php
					} ?>
				</ul>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e("Universal Button", "add-to-any"); ?></th>
			<td><fieldset>
				<div class="addtoany_icon_size_large">
					<label class="addtoany_override a2a_kit_size_32">
						<input name="A2A_SHARE_SAVE_button" value="A2A_SVG_32" type="radio"<?php if ( ! isset( $options['button'] ) || 'A2A_SVG_32' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<span class="a2a_svg a2a_s_a2a" onclick="this.parentNode.firstChild.checked=true" style="margin-left:9px"></span>
					</label>
					<br>
				</div>
				
				<div class="addtoany_icon_size_small">
					<label>
						<input name="A2A_SHARE_SAVE_button" value="favicon.png|16|16" id="A2A_SHARE_SAVE_button_is_favicon_16" type="radio"<?php if ( isset( $options['button'] ) && 'favicon.png|16|16' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/favicon.png'; ?>" width="16" height="16" border="0" style="padding:9px;vertical-align:middle" alt="+ <?php _e('Share','add-to-any'); ?>" title="+ <?php _e('Share','add-to-any'); ?>" onclick="this.parentNode.firstChild.checked=true"/>
					</label>
					<input name="A2A_SHARE_SAVE_button_favicon_16_16_text" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_favicon_16').checked=true" style="vertical-align:middle;width:150px" value="<?php echo ( isset( $options['button_text'] ) && '' != trim( $options['button_text'] ) ) ? stripslashes($options['button_text']) : __('Share','add-to-any'); ?>" />
					<label style="padding-left:9px">
						<input name="A2A_SHARE_SAVE_button" value="share_16_16.png|16|16" id="A2A_SHARE_SAVE_button_is_share_icon_16" type="radio"<?php if ( isset( $options['button'] ) && 'share_16_16.png|16|16' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/share_16_16.png'; ?>" width="16" height="16" border="0" style="padding:9px;vertical-align:middle" alt="+ <?php _e('Share','add-to-any'); ?>" title="+ <?php _e('Share','add-to-any'); ?>" onclick="this.parentNode.firstChild.checked=true"/>
					</label>
					<input name="A2A_SHARE_SAVE_button_share_16_16_text" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_share_icon_16').checked=true" style="vertical-align:middle;width:150px" value="<?php echo ( isset( $options['button_text'] ) && '' != trim( $options['button_text'] ) ) ? stripslashes($options['button_text']) : __('Share','add-to-any'); ?>" />
					<br>
					<label>
						<input name="A2A_SHARE_SAVE_button" value="share_save_120_16.png|120|16" type="radio"<?php if ( isset( $options['button'] ) && 'share_save_120_16.png|120|16' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/share_save_120_16.png'; ?>" width="120" height="16" border="0" style="padding:9px;vertical-align:middle" onclick="this.parentNode.firstChild.checked=true"/>
					</label>
					<br>
					<label>
						<input name="A2A_SHARE_SAVE_button" value="share_save_171_16.png|171|16" type="radio"<?php if ( isset( $options['button'] ) && 'share_save_171_16.png|171|16' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/share_save_171_16.png'; ?>" width="171" height="16" border="0" style="padding:9px;vertical-align:middle" onclick="this.parentNode.firstChild.checked=true"/>
					</label>
					<br>
					<label>
						<input name="A2A_SHARE_SAVE_button" value="share_save_256_24.png|256|24" type="radio"<?php if ( isset( $options['button'] ) && 'share_save_256_24.png|256|24' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url_path.'/share_save_256_24.png'; ?>" width="256" height="24" border="0" style="padding:9px;vertical-align:middle" onclick="this.parentNode.firstChild.checked=true"/>
					</label>
					<br>
				</div>
				
				<label>
					<input name="A2A_SHARE_SAVE_button" value="CUSTOM" id="A2A_SHARE_SAVE_button_is_custom" type="radio"<?php if ( isset( $options['button'] ) && 'CUSTOM' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("Image URL"); ?>:</span>
				</label>
				<input name="A2A_SHARE_SAVE_button_custom" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_custom').checked=true" style="vertical-align:middle" value="<?php if ( isset( $options['button_custom'] ) ) echo $options['button_custom']; ?>" />
				<br>
				<label>
					<input name="A2A_SHARE_SAVE_button" value="TEXT" id="A2A_SHARE_SAVE_button_is_text" type="radio"<?php if ( isset( $options['button'] ) && 'TEXT' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("Text only"); ?>:</span>
				</label>
				<input name="A2A_SHARE_SAVE_button_text" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_text').checked=true" style="vertical-align:middle;width:150px" value="<?php echo ( isset( $options['button_text'] ) && trim( '' != $options['button_text'] ) ) ? stripslashes($options['button_text']) : __('Share','add-to-any'); ?>" />
				<br>
				<label>
					<input name="A2A_SHARE_SAVE_button" value="NONE" type="radio"<?php if ( isset( $options['button'] ) && 'NONE' == $options['button'] ) echo ' checked="checked"'; ?> onclick="return confirm('<?php _e('This option will disable universal sharing. Are you sure you want to disable universal sharing?', 'add-to-any' ) ?>')" style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("None"); ?></span>
				</label>
				
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Placement', 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts'] ) || $options['display_in_posts'] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php printf(__('Display at the %s of posts', 'add-to-any'), position_in_content($options, TRUE)); ?> <strong>*</strong>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_excerpts" type="checkbox"<?php 
						if ( ! isset( $options['display_in_excerpts'] ) || $options['display_in_excerpts'] != '-1' ) echo ' checked="checked"';
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of post excerpts', 'add-to-any'), position_in_content($options)); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts_on_front_page" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts_on_front_page'] ) || $options['display_in_posts_on_front_page'] != '-1' ) echo ' checked="checked"';
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts on the front page', 'add-to-any'), position_in_content($options)); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts_on_archive_pages" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts_on_archive_pages'] ) || $options['display_in_posts_on_archive_pages'] != '-1' ) echo ' checked="checked"';
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts on archive pages', 'add-to-any'), position_in_content($options)); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_feed" type="checkbox"<?php 
						if ( ! isset( $options['display_in_feed'] ) || $options['display_in_feed'] != '-1' ) echo ' checked="checked"'; 
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts in the feed', 'add-to-any'), position_in_content($options)); ?>
				</label>
				<br/>
				<label>
					<input name="A2A_SHARE_SAVE_display_in_pages" type="checkbox"<?php if ( ! isset( $options['display_in_pages'] ) || $options['display_in_pages'] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php printf(__('Display at the %s of pages', 'add-to-any'), position_in_content($options, TRUE)); ?>
				</label>
				<br/><br/>
				<div class="setting-description">
					<?php _e("See <a href=\"widgets.php\" title=\"Theme Widgets\">Widgets</a> and <a href=\"options-general.php?page=add-to-any.php&action=floating\" title=\"AddToAny Floating Share Buttons\">Floating</a> for additional placement options. For advanced placement, see <a href=\"http://wordpress.org/plugins/add-to-any/faq/\">the FAQs</a>.", "add-to-any"); ?>
				</div>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Menu Options', 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<input name="A2A_SHARE_SAVE_onclick" type="checkbox"<?php if ( isset( $options['onclick'] ) && $options['onclick'] == '1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php _e('Only show the universal share menu when the user <em>clicks</em> the universal share button', 'add-to-any'); ?>
				</label>
				<br />
				<label>
					<input name="A2A_SHARE_SAVE_show_title" type="checkbox"<?php if ( isset( $options['show_title'] ) && $options['show_title'] == '1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php _e('Show the title of the page within the universal share menu', 'add-to-any'); ?>
				</label>
				<label>
					<p><?php _e("You can use AddToAny's Menu Styler to customize the colors of your universal share menu. When you're done, be sure to paste the generated code in the <a href=\"#\" onclick=\"document.getElementById('A2A_SHARE_SAVE_additional_js_variables').focus();return false\">Additional Options</a> box below.", "add-to-any"); ?></p>
				</label>
				<p>
					<a href="http://www.addtoany.com/buttons/share_save/menu_style/wordpress" class="button-secondary" title="<?php _e("Open the AddToAny Menu Styler in a new window", "add-to-any"); ?>" target="_blank" onclick="document.getElementById('A2A_SHARE_SAVE_additional_js_variables').focus(); document.getElementById('A2A_SHARE_SAVE_menu_styler_note').style.display='';"><?php _e("Open Menu Styler", "add-to-any"); ?></a>
				</p>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Additional Options', 'add-to-any'); ?></th>
			<td><fieldset>
				<p id="A2A_SHARE_SAVE_menu_styler_note" style="display:none">
					<label for="A2A_SHARE_SAVE_additional_js_variables" class="updated">
						<strong><?php _e("Paste the code from AddToAny's Menu Styler in the box below!", 'add-to-any'); ?></strong>
					</label>
				</p>
				<label for="A2A_SHARE_SAVE_additional_js_variables">
					<p><?php _e('Below you can add special JavaScript code for AddToAny.', 'add-to-any'); ?>
					<?php _e("Advanced users should explore AddToAny's <a href=\"http://www.addtoany.com/buttons/customize/wordpress\" target=\"_blank\">additional options</a>.", "add-to-any"); ?></p>
				</label>
				<p>
					<textarea name="A2A_SHARE_SAVE_additional_js_variables" id="A2A_SHARE_SAVE_additional_js_variables" class="code" style="width: 98%; font-size: 12px;" rows="6" cols="50"><?php if ( isset( $options['additional_js_variables'] ) ) echo stripslashes( $options['additional_js_variables'] ); ?></textarea>
				</p>
				<?php if ( isset( $options['additional_js_variables'] ) && $options['additional_js_variables'] != '' ) { ?>
				<label for="A2A_SHARE_SAVE_additional_js_variables" class="setting-description"><?php _e("<strong>Note</strong>: If you're adding new code, be careful not to accidentally overwrite any previous code.</label>", 'add-to-any'); ?>
				<?php } ?>	
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Advanced Options', 'add-to-any'); ?></th>
			<td><fieldset>
				<label for="A2A_SHARE_SAVE_custom_icons">
					<input name="A2A_SHARE_SAVE_custom_icons" id="A2A_SHARE_SAVE_custom_icons" type="checkbox"<?php if ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' ) echo ' checked="checked"'; ?> value="url"/>
				<?php _e('Use custom icons. URL:', 'add-to-any'); ?>
				</label>
				<input name="A2A_SHARE_SAVE_custom_icons_url" type="text" class="code" size="50" style="vertical-align:middle" placeholder="//example.com/blog/uploads/addtoany/icons/custom/" value="<?php if ( isset( $options['custom_icons_url'] ) ) echo $options['custom_icons_url']; ?>" />
				<p class="description">
					<?php _e("Specify the URL of the directory containing your custom icons. For example, a URL of <code>//example.com/blog/uploads/addtoany/icons/custom/</code> containing <code>facebook.png</code> and <code>twitter.png</code>. Be sure that custom icon filenames match the icon filenames in <code>plugins/add-to-any/icons</code>. For AddToAny's Universal Button, select Image URL and specify the URL of your AddToAny universal share icon (<a href=\"#\" onclick=\"document.getElementsByName('A2A_SHARE_SAVE_button_custom')[0].focus();return false\">above</a>).", "add-to-any"); ?>
				</p>
				<br/>
				<label for="A2A_SHARE_SAVE_inline_css">
					<input name="A2A_SHARE_SAVE_inline_css" id="A2A_SHARE_SAVE_inline_css" type="checkbox"<?php if ( ! isset( $options['inline_css'] ) || $options['inline_css'] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
				<?php _e('Use default CSS', 'add-to-any'); ?>
				</label>
				<p class="description">
					<?php _e("Only disable AddToAny's default stylesheet if you already have the necessary CSS code applied to your AddToAny buttons.", "add-to-any"); ?>
				</p>
				<br/>
				<label for="A2A_SHARE_SAVE_cache">
					<input name="A2A_SHARE_SAVE_cache" id="A2A_SHARE_SAVE_cache" type="checkbox"<?php if ( isset( $options['cache'] ) && $options['cache'] == '1' ) echo ' checked="checked"'; ?> value="1"/>
				<?php _e('Cache AddToAny locally with daily cache updates', 'add-to-any'); ?>
				</label>
				<p class="description">
					<?php _e("Most sites should not use this option. By default, AddToAny loads asynchronously and most efficiently. Since many visitors will have AddToAny cached in their browser already, serving AddToAny locally from your site will be slower for those visitors. If local caching is enabled, be sure to set far future cache/expires headers for image files in your <code>uploads/addtoany</code> directory.", "add-to-any"); ?>
				</p>
			</fieldset></td>
			</tr>
		<?php endif; ?>
		
		</table>		
		
		<?php if ( 'floating' == $current_screen ) : ?>
		
		<p><?php _e('AddToAny &quot;floating&quot; share buttons stay in a fixed position even when the user scrolls.', "add-to-any"); ?></p>
		<p><?php _e('Large icons from your currently selected buttons are displayed in your floating bar(s). 3rd party buttons (Like, Tweet, etc.) are not displayed.', "add-to-any"); ?></p>
		
		<h3><?php _e('Vertical Buttons', "add-to-any"); ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e("Placement", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="left_docked"<?php if ( isset( $options['floating_vertical'] ) && 'left_docked' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('Left docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="right_docked"<?php if ( isset( $options['floating_vertical'] ) && 'right_docked' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('Right docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="none"<?php if ( ! isset( $options['floating_vertical'] ) || 'none' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('None', 'add-to-any'); ?></label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Responsiveness", "add-to-any"); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_floating_vertical_responsive" name="A2A_SHARE_SAVE_floating_vertical_responsive" type="checkbox"<?php 
						if ( ! isset( $options['floating_vertical_responsive'] ) || $options['floating_vertical_responsive'] != '-1' ) echo ' checked="checked"'; ?> value="1" />
					Only display when screen is larger than <input name="A2A_SHARE_SAVE_floating_vertical_responsive_max_width" type="number" step="1" value="<?php if ( isset( $options['floating_vertical_responsive_max_width'] ) ) echo $options['floating_vertical_responsive_max_width']; else echo '980'; ?>" class="small-text" /> pixels wide
				</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Position", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_vertical_position" type="number" step="1" value="<?php if ( isset( $options['floating_vertical_position'] ) ) echo $options['floating_vertical_position']; else echo '100'; ?>" class="small-text" /> pixels from top</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Offset", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_vertical_offset" type="number" step="1" value="<?php if ( isset( $options['floating_vertical_offset'] ) ) echo $options['floating_vertical_offset']; else echo '0'; ?>" class="small-text" /> pixels from left or right</label>
			</fieldset></td>
			</tr>
		</table>
			
		<h3><?php _e('Horizontal Buttons', "add-to-any"); ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e("Placement", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="left_docked"<?php if ( isset( $options['floating_horizontal'] ) && 'left_docked' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('Left docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="right_docked"<?php if ( isset( $options['floating_horizontal'] ) && 'right_docked' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('Right docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="none"<?php if ( ! isset( $options['floating_horizontal'] ) || 'none' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('None', 'add-to-any'); ?></label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Responsiveness", "add-to-any"); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_floating_horizontal_responsive" name="A2A_SHARE_SAVE_floating_horizontal_responsive" type="checkbox"<?php 
						if ( ! isset( $options['floating_horizontal_responsive'] ) || $options['floating_horizontal_responsive'] != '-1' ) echo ' checked="checked"'; ?> value="1" />
					Only display when screen is smaller than <input name="A2A_SHARE_SAVE_floating_horizontal_responsive_min_width" type="number" step="1" value="<?php if ( isset( $options['floating_horizontal_responsive_min_width'] ) ) echo $options['floating_horizontal_responsive_min_width']; else echo '981'; ?>" class="small-text" /> pixels wide
				</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Position", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_horizontal_position" type="number" step="1" value="<?php if ( isset( $options['floating_horizontal_position'] ) ) echo $options['floating_horizontal_position']; else echo '0'; ?>" class="small-text" /> pixels from left or right</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Offset", "add-to-any"); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_horizontal_offset" type="number" step="1" value="<?php if ( isset( $options['floating_horizontal_offset'] ) ) echo $options['floating_horizontal_offset']; else echo '0'; ?>" class="small-text" /> pixels from bottom</label>
			</fieldset></td>
			</tr>
		</table>
		
		<?php endif; ?>
		
		</table>
		
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'add-to-any' ) ?>" />
			<input id="A2A_SHARE_SAVE_reset_options" type="submit" name="Reset" onclick="return confirm('<?php _e('Are you sure you want to delete all AddToAny options?', 'add-to-any' ) ?>')" value="<?php _e('Reset', 'add-to-any' ) ?>" />
		</p>
	
	</form>
	
	<h2><?php _e('Like this plugin?','add-to-any'); ?></h2>
	<p><?php _e('<a href="http://wordpress.org/extend/plugins/add-to-any/">Give it a good rating</a> on WordPress.org.','add-to-any'); ?></p>
	<p><?php _e('<a href="http://www.addtoany.com/share_save?linkname=WordPress%20Share%20%2F%20Bookmark%20Plugin%20by%20AddToAny.com&amp;linkurl=http%3A%2F%2Fwordpress.org%2Fextend%2Fplugins%2Fadd-to-any%2F">Share it</a> with your friends.','add-to-any'); ?> <a href="https://www.facebook.com/AddToAny" target="_blank">Facebook</a> / <a href="https://twitter.com/AddToAny" target="_blank">Twitter</a></p>
	
	<h2><?php _e('Need support?','add-to-any'); ?></h2>
	<p><?php _e('See the <a href="http://wordpress.org/extend/plugins/add-to-any/faq/">FAQs</a>.','add-to-any'); ?></p>
	<p><?php _e('Search the <a href="http://wordpress.org/tags/add-to-any">support forums</a>.','add-to-any'); ?></p>
	</div>
	
	<script type="text/javascript" src="http<?php if ( is_ssl() ) echo 's'; ?>://static.addtoany.com/menu/page.js"></script>
	<script type="text/javascript">if ( a2a && a2a.svg_css ) a2a.svg_css();</script>

<?php

}

// Admin page header
function A2A_SHARE_SAVE_admin_head() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'add-to-any.php' ) {
		
		$options = get_option( 'addtoany_options' );
		
	?>
	<script type="text/javascript"><!--
	jQuery(document).ready(function(){
		
		var show_appropriate_universal_buttons = function() {
			
			// Note the currently checkmarked radio button
			jQuery('input[name="A2A_SHARE_SAVE_button"]:visible').removeClass('addtoany_last_universal_selected').filter(':checked').addClass('addtoany_last_universal_selected');
			
			var select_proper_radio = function() {
				// Select the last-selected visible radio
				jQuery('input[name="A2A_SHARE_SAVE_button"].addtoany_last_universal_selected:radio:visible').attr('checked', true);
				
				// Otherwise select the first visible radio
				if ( jQuery('input[name="A2A_SHARE_SAVE_button"]:visible:checked').length < 1 )
					jQuery('input[name="A2A_SHARE_SAVE_button"]:radio:visible:first').attr('checked', true);
			};
			
			if ( jQuery('input[name="A2A_SHARE_SAVE_icon_size"]:checked').val() == '32' ) {
				// Hide small universal buttons
				jQuery('.addtoany_icon_size_small').hide('fast');
				// Show large universal button
				jQuery('.addtoany_icon_size_large').show('fast', select_proper_radio);
				
				// Switch to large standalone icons
				jQuery('#addtoany_services_sortable li').not('.dummy, .addtoany_special_service, #addtoany_show_services').html(function() {
					return jQuery(this).data('a2a_32_icon_html');
				});
				
				// Adjust the Add/Remove Services button
				jQuery('#addtoany_show_services').addClass('addtoany_line_height_32');
			}
			else {
				// Hide small universal buttons
				jQuery('.addtoany_icon_size_large').hide('fast');
				// Show large universal button
				jQuery('.addtoany_icon_size_small').show('fast', select_proper_radio);
				
				// Switch to small standalone icons
				jQuery('#addtoany_services_sortable li').not('.dummy, .addtoany_special_service, #addtoany_show_services').html(function() {
					return jQuery(this).data('a2a_16_icon_html');
				});
				
				// Adjust the Add/Remove Services button
				jQuery('#addtoany_show_services').removeClass('addtoany_line_height_32');
			}
		};
		
		show_appropriate_universal_buttons();
		
		// Display buttons/icons of the selected icon size
		jQuery('input[name="A2A_SHARE_SAVE_icon_size"]').bind('change', function(e){
			show_appropriate_universal_buttons();
		});
		
		// Toggle child options of 'Display in posts'
		jQuery('#A2A_SHARE_SAVE_display_in_posts').bind('change click', function(e){
			if (jQuery(this).is(':checked'))
				jQuery('.A2A_SHARE_SAVE_child_of_display_in_posts').attr('checked', true).attr('disabled', false);
			else 
				jQuery('.A2A_SHARE_SAVE_child_of_display_in_posts').attr('checked', false).attr('disabled', true);
		});
		
		// Update button position labels/values universally in Placement section 
		jQuery('select[name="A2A_SHARE_SAVE_position"]').bind('change click', function(e){
			var $this = jQuery(this);
			jQuery('select[name="A2A_SHARE_SAVE_position"]').not($this).val($this.val());
			
			jQuery('.A2A_SHARE_SAVE_position').html($this.find('option:selected').html());
		});
	
		var to_input = function(this_sortable){
			// Clear any previous hidden inputs for storing chosen services
			// and special service options
			jQuery('input.addtoany_hidden_options').remove();
			
			var services_array = jQuery(this_sortable).sortable('toArray'),
				services_size = services_array.length;
			if(services_size<1) return;
			
			for(var i=0, service_name, show_count_value, fb_verb_value; i < services_size; i++){
				if(services_array[i]!='') { // Exclude dummy icon
					jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="A2A_SHARE_SAVE_active_services[]" type="hidden" value="'+services_array[i]+'"/>');
					
					// Special service options?
					service_name = services_array[i].substr(7);
					if (service_name == 'facebook_like' || service_name == 'twitter_tweet' || service_name == 'google_plusone' || service_name == 'google_plus_share' || service_name == 'pinterest_pin') {
						if (service_name == 'twitter_tweet' || service_name == 'google_plusone' || service_name == 'google_plus_share' || service_name == 'pinterest_pin') {
							show_count_value = (jQuery('#' + services_array[i] + '_show_count').is(':checked')) ? '1' : '-1' ;
							jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="addtoany_' + service_name + '_show_count" type="hidden" value="' + show_count_value + '"/>');
						}
						
						if (service_name == 'facebook_like') {
							fb_verb_value = (jQuery('#' + services_array[i] + '_verb').val() == 'recommend') ? 'recommend' : 'like';
							jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="addtoany_' + service_name + '_verb" type="hidden" value="' + fb_verb_value + '"/>');
						}
					}
				}
			}
		};
	
		jQuery('#addtoany_services_sortable').sortable({
			forcePlaceholderSize: true,
			items: 'li:not(#addtoany_show_services, .dummy)',
			placeholder: 'ui-sortable-placeholder',
			opacity: .6,
			tolerance: 'pointer',
			update: function(){to_input(this)}
		});
		
		// Service click = move to sortable list
		var moveToSortableList = function(){
			var configurable_html = '',
				this_service = jQuery(this),
				this_service_name = this_service.attr('id').substr(7),
				this_service_is_special = this_service.hasClass('addtoany_special_service'),
				checked = '',
				special_options_html = '';
			
			if (jQuery('#addtoany_services_sortable li').not('.dummy').length == 0)
				jQuery('#addtoany_services_sortable').find('.dummy').hide();
				
			if (this_service_is_special) {
				if (this_service_name == 'facebook_like') {
					if (service_options[this_service_name] && service_options[this_service_name].verb)
						checked = ' selected="selected"';
					special_options_html = '<select id="' + this_service.attr('id') + '_verb" name="' + this_service.attr('id') + '_verb">'
						+ '<option value="like">Like</option>'
						+ '<option' + checked + ' value="recommend">Recommend</option>'
						+ '</select>';
				} else {
					// twitter_tweet & google_plusone & google_plus_share & pinterest_pin
					if (service_options[this_service_name] && service_options[this_service_name].show_count) {
						checked = ' checked="checked"';
					}
					special_options_html = '<label><input' + checked + ' id="' + this_service.attr('id') + '_show_count" name="' + this_service.attr('id') + '_show_count" type="checkbox" value="1"> Show count</label>';
				}
				
				configurable_html = '<span class="down_arrow"></span><br style="clear:both"/><div class="special_options">' + special_options_html + '</div>';
			}
			
			var icon_size = jQuery('input:radio[name=A2A_SHARE_SAVE_icon_size]:checked').val();
				new_service = this_service.toggleClass('addtoany_selected')
					.unbind('click', moveToSortableList)
					.bind('click', moveToSelectableList)
					.clone();
			
			new_service.data('a2a_16_icon_html', this_service.find('img').clone().attr('alt', this_service.attr('title')).wrap('<p>').parent().html() + configurable_html);
			
			if (this_service_is_special)
				// If special service, set the same HTML as used for '16px icon size'
				new_service.data( 'a2a_32_icon_html', new_service.data('a2a_16_icon_html') );
			else
				// Set HTML for 32px icon size
				new_service.data( 'a2a_32_icon_html', '<span class="a2a_svg a2a_s__default a2a_s_' + this_service.attr('data-addtoany-icon-name') + '"></span>' );
				
			new_service.html( new_service.data('a2a_' + icon_size + '_icon_html') )
				.click(function(){
					jQuery(this).not('.addtoany_special_service_options_selected').find('.special_options').slideDown('fast').parent().addClass('addtoany_special_service_options_selected');
				})
				.hide()
				.insertBefore('#addtoany_services_sortable .dummy')
				.fadeIn('fast');
			
			this_service.attr( 'id', 'old_'+this_service.attr('id') );
		};
		
		// Service click again = move back to selectable list
		var moveToSelectableList = function(){
			jQuery(this).toggleClass('addtoany_selected')
			.unbind('click', moveToSelectableList)
			.bind('click', moveToSortableList);
	
			jQuery( '#'+jQuery(this).attr('id').substr(4).replace(/\./, '\\.') )
			.hide('fast', function(){
				jQuery(this).remove();
			});
			
			
			if( jQuery('#addtoany_services_sortable li').not('.dummy').length==1 )
				jQuery('#addtoany_services_sortable').find('.dummy').show();
			
			jQuery(this).attr('id', jQuery(this).attr('id').substr(4));
		};
		
		// Service click = move to sortable list
		jQuery('#addtoany_services_selectable li').bind('click', moveToSortableList);
		
		// Form submit = get sortable list
		jQuery('#addtoany_admin_form').submit(function(){to_input('#addtoany_services_sortable')});
		
		// Auto-select active services
		<?php
		$admin_services_saved = isset( $_POST['A2A_SHARE_SAVE_active_services'] ) && isset( $_POST['Submit'] );
		
		if ( $admin_services_saved ) {
			$active_services = $_POST['A2A_SHARE_SAVE_active_services'];
		} elseif ( ! $admin_services_saved && isset( $options['active_services'] ) ) {
			$active_services = $options['active_services'];
		} else {
			// Use default services if options have not been set yet (and no services were just saved in the form)
			$active_services = array( 'facebook', 'twitter', 'google_plus' );
		}
		
		$active_services_last = end($active_services);
		if($admin_services_saved)
			$active_services_last = substr($active_services_last, 7); // Remove a2a_wp_
		$active_services_quoted = '';
		foreach ($active_services as $service) {
			if($admin_services_saved)
				$service = substr($service, 7); // Remove a2a_wp_
			$active_services_quoted .= '"' . esc_js( $service ) . '"';
			if ( $service != $active_services_last )
				$active_services_quoted .= ',';
		}
		?>
		var services = [<?php echo $active_services_quoted; ?>],
			service_options = {};
		
		<?php		
		// Special service options
		if ( isset( $_POST['addtoany_facebook_like_verb'] ) && $_POST['addtoany_facebook_like_verb'] == 'recommend'
			|| ! isset( $_POST['addtoany_facebook_like_verb'] ) 
			&& isset( $options['special_facebook_like_options'] ) && $options['special_facebook_like_options']['verb'] == 'recommend' ) {
			?>service_options.facebook_like = {verb: 'recommend'};<?php
		}
		if ( isset( $_POST['addtoany_twitter_tweet_show_count'] ) && $_POST['addtoany_twitter_tweet_show_count'] == '1'
			|| ! isset( $_POST['addtoany_twitter_tweet_show_count'] )
			&& isset( $options['special_twitter_tweet_options'] ) && $options['special_twitter_tweet_options']['show_count'] == '1' ) {
			?>service_options.twitter_tweet = {show_count: 1};<?php
		}
		if ( isset( $_POST['addtoany_google_plusone_show_count'] ) && $_POST['addtoany_google_plusone_show_count'] == '1'
			|| ! isset( $_POST['addtoany_google_plusone_show_count'] )
			&& isset( $options['special_google_plusone_options'] ) && $options['special_google_plusone_options']['show_count'] == '1' ) {
			?>service_options.google_plusone = {show_count: 1};<?php
		}
		if ( isset( $_POST['addtoany_google_plus_share_show_count'] ) && $_POST['addtoany_google_plus_share_show_count'] == '1'
			|| ! isset( $_POST['addtoany_google_plus_share_show_count'] )
			&& isset( $options['special_google_plus_share_options'] ) && $options['special_google_plus_share_options']['show_count'] == '1' ) {
			?>service_options.google_plus_share = {show_count: 1};<?php
		}
		if ( isset( $_POST['addtoany_pinterest_pin_show_count'] ) && $_POST['addtoany_pinterest_pin_show_count'] == '1'
			|| ! isset( $_POST['addtoany_pinterest_pin_show_count'] )
			&& isset( $options['special_pinterest_pin_options'] ) && $options['special_pinterest_pin_options']['show_count'] == '1' ) {
			?>service_options.pinterest_pin = {show_count: 1};<?php
		}
		?>
		
		jQuery.each(services, function(i, val){
			jQuery('#a2a_wp_'+val).click();
		});
		
		// Add/Remove Services button
		jQuery('#addtoany_services_sortable .dummy:first').after('<li id="addtoany_show_services"><?php _e('Add/Remove Services', 'add-to-any'); ?> &#187;</li>');
		jQuery('#addtoany_show_services').click(function(e){
			jQuery('#addtoany_services_selectable, #addtoany_services_info').slideDown('fast');
			jQuery(this).fadeOut('fast');
		});
		
		// Adjust the Add/Remove Services button for large or small icons
		if ( jQuery('input:radio[name=A2A_SHARE_SAVE_icon_size]:checked').val() == '32' )
			jQuery('#addtoany_show_services').addClass('addtoany_line_height_32');
		
		// TBD
		jQuery('#addtoany_show_css_code').click(function(e){
			jQuery('#addtoany_css_code').slideDown('fast');
			jQuery(this).fadeOut('fast');
		});
	});
	--></script>

	<style type="text/css">
	.ui-sortable-placeholder{background-color:transparent;border:1px dashed #AAA !important;}
	.addtoany_admin_list{list-style:none;padding:0;margin:0;}
	.addtoany_admin_list li{-webkit-border-radius:9px;-moz-border-radius:9px;border-radius:9px;}
	
	#addtoany_services_selectable{clear:left;display:none;}
	#addtoany_services_selectable li{cursor:pointer;float:left;width:150px;font-size:11px;margin:0;padding:6px;border:1px solid transparent;_border-color:#FAFAFA/*IE6*/;overflow:hidden;}
	<?php // white-space:nowrap could go above, but then webkit does not wrap floats if parent has no width set; wrapping in <span> instead (below) ?>
	#addtoany_services_selectable li span{white-space:nowrap;}
	#addtoany_services_selectable li:hover, #addtoany_services_selectable li.addtoany_selected{border:1px solid #AAA;background-color:#FFF;}
	#addtoany_services_selectable li.addtoany_selected:hover{border-color:#F00;}
	#addtoany_services_selectable li:active{border:1px solid #000;}
	#addtoany_services_selectable img{margin:0 4px;width:16px;height:16px;border:0;vertical-align:middle;}
	#addtoany_services_selectable .addtoany_special_service{padding:3px 6px;}
	#addtoany_services_selectable .addtoany_special_service img{width:auto;height:20px;}
	
	#addtoany_services_sortable li, #addtoany_services_sortable li.dummy:hover{cursor:move;float:left;padding:9px;border:1px solid transparent;_border-color:#FAFAFA/*IE6*/;}
	#addtoany_services_sortable li:hover{border:1px solid #AAA;background-color:#FFF;}
	#addtoany_services_sortable li.dummy, #addtoany_services_sortable li.dummy:hover{cursor:auto;background-color:transparent;}
	#addtoany_services_sortable img{width:16px;height:16px;border:0;vertical-align:middle;}
	#addtoany_services_sortable .addtoany_special_service img{width:auto;height:20px;float:left;}
	#addtoany_services_sortable .addtoany_special_service span.down_arrow{background:url(<?php echo admin_url( '/images/arrows.png' ); ?>) no-repeat 5px 9px;float:right;height:29px;;margin:-6px 0 -6px 4px;width:26px;}
	#addtoany_services_sortable .addtoany_special_service div.special_options{display:none;font-size:11px;margin-top:9px;}
	#addtoany_services_sortable .addtoany_special_service_options_selected{border:1px solid #AAA;background-color:#FFF;}
	#addtoany_services_sortable .addtoany_special_service_options_selected span.down_arrow{display:none;}
	
	li#addtoany_show_services.addtoany_line_height_32{line-height:32px}
	li#addtoany_show_services{border:1px solid #DFDFDF;background-color:#FFF;cursor:pointer;margin-left:9px;}
	li#addtoany_show_services:hover{border:1px solid #AAA;}
	#addtoany_services_info{clear:left;display:none;margin:10px;}
	
	.a2a_kit_size_32.addtoany_override .a2a_svg { 
		border-radius: 4px;
		display:inline-block;
		height: 32px;
		vertical-align:middle;
		width: 32px;
	}
	
	#addtoany_template_button_code, #addtoany_css_code{display:none;}
	
	#A2A_SHARE_SAVE_reset_options{color:red;margin-left: 15px;}
	</style>
<?php

	}
}

add_filter( 'admin_head', 'A2A_SHARE_SAVE_admin_head' );



function A2A_SHARE_SAVE_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );
}
