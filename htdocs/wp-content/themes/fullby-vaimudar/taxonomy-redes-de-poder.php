<?php 
	get_header(); 
	global $wp;

?>
<div class="redes-header" style="background-image: url('<?php bloginfo('stylesheet_directory'); ?>/img/obras.jpg');">
	<div class="title row">
		<div class="col-md-10 col-md-offset-1 text">
			Odebrecht Transport S.A.
		</div>
	</div>
</div>
<div class="col-md-10 col-md-offset-1 redes-wrapper">
	<div class="row">
	<div class="col-md-9">
		<div class="redes-content">
		
		<p class="grid-cat"><a href="" title="Redes de poder" rel="category tag">Redes de poder</a></p>
		
<?php
	//	Informações sobre a categoria
	$current_url 	= add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	$current_url 	= explode('=',$current_url);
	$category_name 	= $current_url[count($current_url)-1];	
	
	$term 				= get_term_by('slug', $category_name, 'redes-de-poder');
	$term_name 			= $term->name;
	$term_description 	= $term->description; //contem a info do cnpj
	$term_id			= $term->id;

	
//	Pega todos os posts do tipo obras da categoria
	$args = array(
	   'post_type' => 'obras',
	   'tax_query' => array(
	      array(
	         'taxonomy' => 'redes-de-poder',
	         'field' => 'slug',
	         'terms' => array($category_name),
	         'operator' => 'IN'
	      )
	   )
	);
	echo '<h3 class="sing-tit">Obras</h3><br />';
	$obras = get_posts($args);
	foreach($obras as $obra) {
		echo mysql2date('d/m/Y', $obra->post_date).'<br />'; //data
		echo $obra->post_title.'<br />'; //titulo
		echo $obra->post_excerpt.'<br />'; //texto resumido
		echo get_permalink($obra->ID).'<br />'; //link
		get_the_post_thumbnail( $obra->ID, '32x32' );
	}
	
//	Pega todos os posts do tipo analises da categoria
	$args = array(
	   'post_type' => 'analises',
	   'tax_query' => array(
	      array(
	         'taxonomy' => 'redes-de-poder',
	         'field' => 'slug',
	         'terms' => array($category_name),
	         'operator' => 'IN'
	      )
	   )
	);
	$analises = get_posts($args);

	echo '<div class="articles-list">';
	echo '<h3 class="sing-tit">Análises</h3>';
	echo '<ul>';
	foreach($analises as $analise) {
		echo '<li>';
		echo '<div class="date">'.mysql2date('d/m/Y', $analise->post_date).'</div>'; //data
		echo '<div class="title"><a href="'.get_permalink($analise->ID).'">'.$analise->post_title.'</a></div>'; //titulo
		echo '<div class="resume">'.$analise->post_excerpt.'</div>'; //texto resumido
		echo '<div class="hr"><hr></div>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</div>';
?>
		</div>
	</div>					
	<div class="col-md-3 redes-sidebar">
		<div class="sec-sidebar redes-content">
<?php
//	Pega todos os posts do tipo empresa da categoria
	$args = array(
	   'post_type' => 'empresas',
	   'tax_query' => array(
	      array(
	         'taxonomy' => 'redes-de-poder',
	         'field' => 'slug',
	         'terms' => array($category_name),
	         'operator' => 'IN'
	      )
	   )
	);
	$rede_de_poder = get_posts($args);
	
	echo '<div class="item-list">';
	echo '<h3 class="sing-tit">Empresas</h3>';
	$cnpj = array();
	echo '<ul>';
	foreach($rede_de_poder as $rede) {
		echo '<li>'.$rede->post_title.'</li>'; //titulo
		$cnpj[] = get_metadata('post', $rede->ID, 'cnpj', 1).'<br />'; //pega o cnpj de cada empresa, não precisa aparecer
	}
	echo '</ul>';
	echo '</div>';

//	Pega todos os posts do tipo noticias da categoria
	$args = array(
	   'post_type' => 'noticias',
	   'tax_query' => array(
	      array(
	         'taxonomy' => 'redes-de-poder',
	         'field' => 'slug',
	         'terms' => array($category_name),
	         'operator' => 'IN'
	      )
	   )
	);
	$noticias = get_posts($args);
	
	echo '<div class="articles-list news">';
	echo '<h3 class="sing-tit">Notícias</h3>';
	echo '<ul>';
	foreach($noticias as $noticia) {
		echo '<li>';
		echo '<div class="date">'.mysql2date('d/m/Y', $noticia->post_date).'</div>'; //data
		echo '<div class="title"><a href="'.get_permalink($noticia->ID).'">'.$noticia->post_title.'</a></div>'; //titulo
		echo '<div class="resume">'.$noticia->post_excerpt.'</div>'; //texto resumido
		echo '<div class="hr"><hr></div>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</div>';
	
?>	
		</div>
	</div>
	</div>
</div>
	
<?php get_footer(); ?>	