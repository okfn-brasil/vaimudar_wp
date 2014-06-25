<?php
add_filter('option_fullby_analytics',       'stripslashes');
add_action('init', 'register_cpt_empresas', 0);
add_action('init', 'register_cpt_analises', 0);
add_action('init', 'register_cpt_noticias', 0);
add_action('init', 'register_cpt_obras', 0); 

add_action( 'init', 'create_power_network_hierarchical_taxonomy', 1 );
add_action('wp_print_scripts', 'chart_addJS');

function create_power_network_hierarchical_taxonomy() {
// Add new taxonomy, make it hierarchical like categories

//first do the translations part for GUI
  $labels = array(
    'name' 				=> _x( 'Redes de poder', 'taxonomy general name' ),
    'singular_name' 	=> _x( 'Rede de poder', 'taxonomy singular name' ),
    'search_items' 		=>  __( 'Procurar nas redes' ),
    'all_items' 		=> __( 'Todas as redes' ),
    'parent_item' 		=> __( 'Rede controladora' ),
    'parent_item_colon' => __( 'Rede controladora:' ),
    'edit_item' 		=> __( 'Editar Rede de poder' ),
    'update_item' 		=> __( 'Atualizar Rede de poder' ),
    'add_new_item' 		=> __( 'Add nova rede' ),
    'new_item_name' 	=> __( 'Nova rede de poder name' ),
    'menu_name' 		=> __( 'Redes de poder' ),
  );

// Now register the taxonomy
  register_taxonomy('redes-de-poder',array('empresas', 'noticias', 'analises', 'obras'), array(
    'hierarchical' 		=> true,
    'labels' 			=> $labels,
    'show_ui' 			=> true,
    'show_admin_column' => true,
    'query_var' 		=> true,
    'rewrite' 			=> array( 'slug' => 'redes-de-poder' ),
  ));

}

function register_cpt_empresas() {
	register_post_type('empresas', array(
		'label' 				=> 'empresas',
		'description' 			=> '',
		'public' 				=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> true,
		'capability_type' 		=> 'post',
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'rewrite' 				=> array('slug' => 'empresas', 'with_front' => true),
		'query_var' 			=> true,
		'supports' 				=> array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
		'labels' 				=> array (
		  'name' 				=> 'empresas',
		  'singular_name' 		=> 'Empresa',
		  'menu_name' 			=> 'Empresas',
		  'add_new' 			=> 'Adicionar empresas',
	  	  'add_new_item' 		=> 'Adicionar nova empresa',
		  'edit' 				=> 'Editar',
		  'edit_item' 			=> 'Editar empresas',
		  'new_item' 			=> 'Adicionar empresa',
		  'view' 				=> 'Visualizar empresas',
		  'view_item' 			=> 'Visualizar empresa',
		  'search_items' 		=> 'Procurar empresas',
		  'not_found' 			=> 'Nenhuma empresa encontrada',
		  'not_found_in_trash' 	=> 'Nenhuma empresa encontrada na lixeira',
		  'parent' 				=> 'Parent',
		)
	)); 
}

function register_cpt_analises() {
	register_post_type('analises', array(
		'label' 				=> 'análises',
		'description' 			=> '',
		'public' 				=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> true,
		'capability_type' 		=> 'post',
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'rewrite' 				=> array('slug' => 'analises', 'with_front' => true),
		'query_var' 			=> true,
		'supports' 				=> array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
		'labels' 				=> array (
		  'name' 				=> 'analises',
		  'singular_name' 		=> 'Análise',
		  'menu_name' 			=> 'Análises',
		  'add_new' 			=> 'Adicionar análises',
	  	  'add_new_item' 		=> 'Adicionar nova análise',
		  'edit' 				=> 'Editar',
		  'edit_item' 			=> 'Editar análise',
		  'new_item' 			=> 'Nova análise',
		  'view' 				=> 'Visualizar análises',
		  'view_item' 			=> 'Visualizar análise',
		  'search_items' 		=> 'Procurar análises',
		  'not_found' 			=> 'Nenhuma análise encontrada',
		  'not_found_in_trash' 	=> 'Nenhuma análise encontrada na lixeira',
		  'parent' 				=> 'Parent',
		)
	)); 
}

function register_cpt_noticias() {
	register_post_type('noticias', array(
		'label' 				=> 'notícias',
		'description' 			=> '',
		'public' 				=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> true,
		'capability_type' 		=> 'post',
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'rewrite' 				=> array('slug' => 'noticias', 'with_front' => true),
		'query_var' 			=> true,
		'supports' 				=> array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
		'labels' 				=> array (
		  'name' 				=> 'noticias',
		  'singular_name' 		=> 'Notícia',
		  'menu_name' 			=> 'Notícias',
		  'add_new' 			=> 'Adicionar notícias',
	  	  'add_new_item' 		=> 'Adicionar nova notícia',
		  'edit' 				=> 'Editar',
		  'edit_item' 			=> 'Editar notícia',
		  'new_item' 			=> 'Nova notícia',
		  'view' 				=> 'Visualizar notícias',
		  'view_item' 			=> 'Visualizar notícia',
		  'search_items' 		=> 'Procurar notícias',
		  'not_found' 			=> 'Nenhuma notícia encontrada',
		  'not_found_in_trash' 	=> 'Nenhuma notícia encontrada na lixeira',
		  'parent' 				=> 'Parent',
		)
	)); 
}

function register_cpt_obras() {
	register_post_type('obras', array(
		'label' 				=> 'obras',
		'description' 			=> '',
		'public' 				=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> true,
		'capability_type' 		=> 'post',
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'rewrite' 				=> array('slug' => 'obras', 'with_front' => true),
		'query_var' 			=> true,
		'supports' 				=> array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
		'labels' 				=> array (
		  'name' 				=> 'obras',
		  'singular_name' 		=> 'Obra',
		  'menu_name' 			=> 'Obras',
		  'add_new' 			=> 'Adicionar obra',
	  	  'add_new_item' 		=> 'Adicionar nova obra',
		  'edit' 				=> 'Editar',
		  'edit_item' 			=> 'Editar obra',
		  'new_item' 			=> 'Nova obra',
		  'view' 				=> 'Visualizar obras',
		  'view_item' 			=> 'Visualizar obra',
		  'search_items' 		=> 'Procurar obras',
		  'not_found' 			=> 'Nenhuma obra encontrada',
		  'not_found_in_trash' 	=> 'Nenhuma obra encontrada na lixeira',
		  'parent' 				=> 'Parent',
		)
	)); 
}


if( is_page('Home') && !is_admin() ) {
	 add_action("wp_enqueue_scripts", "my_jquery_enqueue", 11);
}

function my_jquery_enqueue() {
   wp_deregister_script('jquery');
   wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . 
        "://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js", false, null);
   wp_enqueue_script('jquery');
}

function wpb_adding_scripts() {
	global $wp;
	
	$current_url 	= add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	$current_url 	= explode('/',$current_url);
	
	$url = get_template_directory_uri();
	
	if(in_array('redes-de-poder', $current_url)) {
		wp_register_script('chart', $url.'-vaimudar/js/chart.js', array('jquery'),'0.1', true);
		wp_enqueue_script('chart');
		
		wp_register_script('flot', $url.'-vaimudar/js/flot/jquery.flot.js', array('jquery'),'1.1', true);
		wp_enqueue_script('flot');
				
	} elseif(is_page('Home')) {
	
		wp_register_script('migrate', 'http://code.jquery.com/jquery-migrate-1.2.1.js', array('jquery'),'1.2.1', true);
		wp_enqueue_script('migrate');
		
		wp_register_style('bubble-style', $url.'-vaimudar/js/bubble-map/build/bubbletree.css');
		wp_enqueue_style('bubble-style');
	
		wp_register_script('history', $url.'-vaimudar/js/bubble-map/lib/jquery.history.js', array('jquery'),'1.0', true);
		wp_enqueue_script('history');
		
		wp_register_script('tooltip', $url.'-vaimudar/js/bubble-map/lib/jquery.tooltip.min.js', array('jquery'),'1.0', true);
		wp_enqueue_script('tooltip');
		
		wp_register_script('raphael', $url.'-vaimudar/js/bubble-map/lib/raphael.js', array('jquery'),'1.0', true);
		wp_enqueue_script('raphael');
		
		wp_register_script('Tween', $url.'-vaimudar/js/bubble-map/lib/Tween.js', array('jquery'),'1.0', true);
		wp_enqueue_script('Tween');
		
		wp_register_script('vis4', $url.'-vaimudar/js/bubble-map/lib/vis4.js', array('jquery'),'1.0', true);
		wp_enqueue_script('vis4');
		
		wp_register_script('bubbletree-home', $url.'-vaimudar/js/bubble-map/lib/bubbletree.home.js', array('jquery'),'1.0', false);
		wp_enqueue_script('bubbletree-home');
		
		wp_register_script('bubbletree', $url.'-vaimudar/js/bubble-map/build/bubbletree.js', array('jquery'),'1.0', false);
		wp_enqueue_script('bubbletree');
		
		wp_register_script('cofog', $url.'-vaimudar/js/bubble-map/styles/cofog.js', array('jquery'),'1.0', false);
		wp_enqueue_script('cofog');
	
	}
}

add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' );

?>
