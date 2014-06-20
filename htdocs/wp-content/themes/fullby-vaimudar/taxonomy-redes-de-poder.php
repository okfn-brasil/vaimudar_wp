<?php 
	get_header(); 
	global $wp;
	global $wpdb;
	
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

	if( in_array($category_name, $open_capital) ) {
		$url_proprietarios = 'http://proprietariosdobrasil.org.br/proprietarios/'.$category_name;
	} else {
		$url_proprietarios = false;
	}
	
//	gráficos
	$total_year = $wpdb->get_results(
		"select 
			ano,
			sum(valor) as max_year 
		from 
			repasse_partido
		where 
			grupo like ('".$term_name."%') 
		and 
			ano >= 2002
		group by
			ano
		order by 
			ano"
	);
	
	$total_party = $wpdb->get_results(
		"select 
			partido,
			sum(valor) as max_party 
		from 
			repasse_partido
		where 
			grupo like ('".$term_name."%') 
		and 
			ano >= 2002
		group by
			partido
		order by
			max_party"
	);
	
	$rows = $wpdb->get_results(
		"select 
			partido,
			ano,
			valor
		from 
			repasse_partido 
		where 
			grupo like ('".$term_name."%') 
		and 
			ano >= 2002
		order by
			partido"
	);
	
	if( $rows ) {
		$partido = $rows[0]->partido;
		
		foreach ( $rows as $row ) {
			if( $row->partido != $partido ) {
				$partido = $row->partido;
			}
	
			$doacoes[$partido][] = array(
				'ano' 		=> $row->ano,
				'valor' 	=> $row->valor,
			);
			
		}
		 
		$doacoes[] = array(
			'total_ano' 	=> $total_year,
			'total_partido' => $total_party
		);
		
		echo '<div id="chart_data" value="'.trim(htmlentities(json_encode($doacoes))).'"></div>';
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
					<strong>Sobre </strong><br />
				</p>
				<p>
					<?php echo $term_description;?> 
				</p>
				
				<?php if ($url_proprietarios) : ?>
				<div class="row silver-box">
				<div class="col-md-6">
						<strong><?php echo $term_name;?></strong><br />
						Veja mais informações clicanco no botão ao lado.
					</div>
					<div class="col-md-6">
						<a href=<?php echo $url_proprietarios;?> class="btn btn-info btn-black col-md-8" target="_blank">Proprietários do Brasil &nbsp;&nbsp;&nbsp;<span class="highlight"></span></a>
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
		<div class="obras-icon">
			<img src="<?php bloginfo('stylesheet_directory')?>/img/icon_stadium.png" class="img-responsive">
		</div>
		<div class="obras-title">
			<div class="div0"></div>
			<div class="div1"></div>
			<div class="div1 content">Estádios</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
	<?php
		echo '		<ul>';
		foreach($obras as $obra) {
			echo '			<li><div class="item">';
			echo '				<div class="local">';
			echo '					<a href="' . get_permalink($obra->ID) . '">'.$obra->post_title.'</a>'; //titulo
			echo '				</div>';
			echo '				<div class="ramification">';
			echo '					<img src="http://vaimudar.org/wp-content/uploads/2014/06/ramification.png">';
			echo '				</div>';
			echo '				<div class="info">';
			echo '					<div class="custo">';
			echo '						<strong>Custo: </strong>' . 'R$XX,XX';
			echo '					</div>';
			echo '					<div class="progresso">';
			echo '						<strong>Progresso: </strong>' . '<span class="concluido">Obra Concluída</span>';
			echo '					</div>';
			echo '					<div class="financiamento">';
			echo '						<strong>Financiamento Federal: </strong>' . 'R$XX,XX';
			echo '					</div>';
			echo '				</div>';
			echo '			</div></li>';
		}
		echo '		</ul>';

		echo '<div class="obras-title">';
		echo '<div class="div0"></div>';
		echo '<div class="div1"></div>';
		echo '<div class="div1 content">Total em obras</div>';
		echo '</div>';
		echo '<div class="obras-total">';
		echo 'R$ 5,6 bilhões';
		echo '</div>';

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
	
	//	Gráficos
	echo '<div class="chart-list">';
	echo '<h3 class="sing-tit">Doações</h3>';
	echo '<div class="chart item">';
	echo '<h3 class="sing-tit">Distribuição por partido e ano</h3>';
	echo '<div id="chart" style="width:800px;height:500px"></div>';
	echo '<div id="miniature">';
    echo '<ul id="overviewLegend">';
    echo '</ul> </div>';
	echo '</div>';
	
	echo '</ul>';
	echo '</div>';
	
	echo '<div class="chart item">';
	echo '<h3 class="sing-tit">Distribuição total por ano</h3>';
	echo '<div id="chart_year" style="width:600px;height:300px"></div>';
	echo '</div>';
	
	echo '</ul>';
	echo '</div>';
	
	echo '<div class="chart item">';
	echo '<h3 class="sing-tit">Distribuição total por partido</h3>';
	echo '<div id="chart_party" style="width:600px;height:300px"></div>';
	echo '</div>';
	
	echo '</ul>';
	echo '</div>';
	echo '</div>';

//	Gráficos
//	echo '<div class="chart-list">';
//	echo '<h3 class="sing-tit">Doações</h3>';
//	echo '<div class="chart item">';
//	echo '<h3 class="sing-tit highlight">Distribuições por partido e ano</h3>';
//	echo '<img src="/wp-content/uploads/2014/06/Grafico1.jpg" class="text-center">';
//	echo '</div>';
//	echo '<div class="chart item">';
//	echo '<h3 class="sing-tit highlight">Distribuição total das empresas por ano</h3>';
//	echo '<img src="/wp-content/uploads/2014/06/Grafico2.jpg" class="text-center">';
//	echo '</div>';
//	echo '<div class="chart item">';
//	echo '<h3 class="sing-tit highlight">Total de doações por partido</h3>';
//	echo '<img src="/wp-content/uploads/2014/06/Grafico3.jpg" class="text-center">';
//	echo '</div>';
//	echo '</div>';
	
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
			$link = get_post_meta($noticia->ID, 'link');
			
			echo '<li>';
			echo '<div class="title"><a href='.$link[0].' target=_blank>'.$noticia->post_title.'</a></div>'; //titulo
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
