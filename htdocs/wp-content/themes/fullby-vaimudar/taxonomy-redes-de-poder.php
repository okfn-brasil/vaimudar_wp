<?php 
	get_header(); 
	global $wp;
	
	$open_capital = array(
		'andrade-gutierrez-sa',
		'construtora-oas-ltda',
		'eike-fuhrken-batista',
		'mendes-junior-engenharia-sa',
		'odebrecht-s-a',
		'queiroz-galvao-s-a',
		'soares-penido-obras-construcoes-e-investimentos-ltda'
	);

//	Informações sobre a categoria
	$current_url 	= add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	$current_url 	= explode('=',$current_url);
	$category_name 	= $current_url[count($current_url)-1];

	$term 				= get_term_by('slug', $category_name, 'redes-de-poder');
	$term_name 			= $term->name;
	$term_description 	= $term->description; //contem a info do cnpj
	$term_id			= $term->id;
	
	$description	= explode(',', $term_description);
	$term_cnpj 		= explode(':', $description[0]);
	$type			= explode(':', $description[1]);
	
	preg_match('/(^\d{2})(\d{3})(\d{3})(\d{4})(\d{2}$)/', trim($term_cnpj[1]), $matches);
	if( $matches ) {
		$term_cnpj = $matches[1].'.'.$matches[2].'.'.$matches[3].'/'.$matches[4].'-'.$matches[5];
	} else {
		preg_match('/(^\d{3})(\d{3})(\d{3})(\d{2}$)/',trim($term_cnpj[1]), $matches);
		if($matches) {
			$term_cnpj = $matches[1].'.'.$matches[2].'.'.$matches[3].'-'.$matches[4];
		} else {
			$term_cnpj = 'Não informado';
		}
	}

	if( in_array($category_name, $open_capital) ) {
		$url_proprietarios = 'http://proprietariosdobrasil.org.br/proprietarios/'.$category_name;
	} else {
		$url_proprietarios = false;
	}
	
?>
<div class="redes-header" style="background-image: url('<?php bloginfo('stylesheet_directory'); ?>/img/obras.jpg');">
	<div class="title row">
		<div class="col-md-10 col-md-offset-1 text">
			<?php echo $term_name; ?>
		</div>
	</div>
</div>
<div class="col-md-10 col-md-offset-1 redes-wrapper">
	<div class="row">		
	<p class="grid-cat"><a href="" title="Redes de poder" rel="category tag">Redes de poder</a></p>
	<br />
	<div class="col-md-9 redes-left">
		<div class="redes-content">
			<?php //	Informações sobre a rede ?>
			<div class="redes-description">
				<p>
					<?php 
					
					if( isset($type[1]) ) {
						echo '<strong>Tipo: </strong>'; echo $type[1]; echo '<br />';
					}
					?>
					<strong>CNPJ ou CPF: </strong><?php echo $term_cnpj; ?><br />
				</p>
				<?php /**<p>
					Odebrecht S.A lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore 
					magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure 
					dolor in reprehenderit in voluptate velit esse cillum dolore. Pellentesque tincidunt luctus felis at varius. Fusce ac vestibulum purus, 
					id feugiat nunc. Morbi quis venenatis sem, eget pretium dolor. Maecenas consectetur scelerisque ullamcorper. Sed sed sapien ipsum. Quisque sed 
					laoreet erat. Nullam vulputate blandit mi, eu interdum diam sagittis id. Quisque eget dolor sit amet magna aliquet tristique porta vitae dui. Aliquam 
					convallis orci neque, sit amet posuere dui tempor ut. Vivamus augue risus, ornare a volutpat ac, feugiat ut velit. 
				</p>
				*/ ?>
				<?php if ($url_proprietarios) : ?>
				<div class="row silver-box">
				<div class="col-md-6">
						<strong><?php echo $term_name;?></strong><br />
						Veja mais informações clicanco no botão ao lado.
					</div>
					<div class="col-md-6">
						<a href=<?php echo $url_proprietarios;?> class="btn btn-info btn-black col-md-8" target="_blank">Proprietários do Brasil &nbsp;&nbsp;&nbsp;<span class="highlight">></span></a>
					</div>
				</div>
				<?php endif;?>
				
			</div>
			<br /><br />
		
<?php
	
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
	   ),
	    'meta_query' => 
	   		array(
				array(
                	'key' => 'tipo_obra',
                    'value' => 'ESTÁDIOS',
                    )
			),
	);
	
	$obras = get_posts($args);
	if($obras) {
?>
	<div class="obras-list">
	<h3 class="sing-tit">Obras</h3><br />
	<div class="row">
		<div class="col-md-2 text-center">
			<img src="<?php bloginfo('stylesheet_directory')?>/img/icon_stadium.png" class="img-responsive">
		</div>
		<div class="col-md-10">
	<?php
		echo '		<ul>';
		foreach($obras as $obra) {
			echo '			<li class="item">';
			echo '				<a href="' . get_permalink($obra->ID) . '">'.$obra->post_title.'</a>'; //titulo
			echo '			</li>';
		}
		echo '		</ul>';
		echo '	</div>';
		echo '</div>';
		echo '</div>';
	}
?>

<?php 
	$args = array(
	   'post_type' => 'obras',
	   'tax_query' => array(
	      array(
	        'taxonomy' => 'redes-de-poder',
	        'field' => 'slug',
	        'terms' => array($category_name),
	        'operator' => 'IN'
	      )
	   ),
		'meta_query' => 
			array(
				array(
				'key' => 'tipo_obra',
				'value' => 'MOBILIDADE URBANA',
			)
		),
	);
	$obras = get_posts($args);
	if($obras) {
?>
	<div class="row">
		<div class="col-md-2 text-center">
			<img src="<?php bloginfo('stylesheet_directory')?>/img/icon_transport.png" class="img-responsive">
		</div>
		<div class="col-md-10">
	<?php
		echo '		<ul>';
		foreach($obras as $obra) {
			echo '			<li class="item">';
			echo '				<a href="' . get_permalink($obra->ID) . '">'.$obra->post_title.'</a>'; //titulo
			echo '			</li>';
		}
		echo '		</ul>';
		echo '	</div>';
		echo '</div>';
		echo '<br />';
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
	if($analises) {
		foreach($analises as $analise) {
			echo '<li>';
			echo '<div class="date">'.mysql2date('d/m/Y', $analise->post_date).'</div>'; //data
			echo '<div class="title"><a href="'.get_permalink($analise->ID).'">'.$analise->post_title.'</a></div>'; //titulo
			echo '<div class="resume">'.$analise->post_excerpt.'</div>'; //texto resumido
			echo '<div class="hr"><hr></div>';
			echo '</li>';
		}
	} else {
		echo '<li>';
		echo '<div class="resume">Nenhuma análise disponível</div>'; //texto resumido
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
	
	echo '<div class="item-list item">';
	echo '<h3 class="sing-tit">Empresas</h3>';
	$cnpj = array();
	if($rede_de_poder) {
		echo '<ul>';
		foreach($rede_de_poder as $rede) {
			echo '<li>'.$rede->post_title.'</li>'; //titulo
			$cnpj[] = get_metadata('post', $rede->ID, 'cnpj', 1).'<br />'; //pega o cnpj de cada empresa, não precisa aparecer
		}
	} else {
		echo '<li><div class="resume">Nenhuma empresa</div></li>'; //titulo
	}
	echo '</ul>';
	echo '</div>';
//	Gráficos
	echo '<div class="chart item">';
	echo '<h3 class="sing-tit">Distribuição por partido e ano</h3>';
	echo '<img src="/wp-content/uploads/2014/06/Grafico.jpg" class="text-center">';
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
	if($noticias) {
		echo '<div class="articles-list item news">';
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
	} 
?>	
		</div>
	</div>
	</div>
</div>
	
<?php get_footer(); ?>	