<?php 
	get_header(); 
	global $wp;

//	Informações sobre a categoria
	$current_url 	= add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	$current_url 	= explode('=',$current_url);
	$category_name 	= $current_url[count($current_url)-1];	
	
	$term 				= get_term_by('slug', $category_name, 'redes-de-poder');
	$term_name 			= $term->name;
	$term_description 	= $term->description; //contem a info do cnpj
	$term_id			= $term->id;

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
	
	echo '<h3>Empresas</h3><br />';
	$cnpj = array();
	foreach($rede_de_poder as $rede) {
		echo $rede->post_title.'<br />'; //titulo
		$cnpj[] = get_metadata('post', $rede->ID, 'cnpj', 1).'<br />'; //pega o cnpj de cada empresa, não precisa aparecer
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

	echo '<h3>Análises</h3><br />';
	foreach($analises as $analise) {
		echo $analise->post_date.'<br />'; //data
		echo $analise->post_title.'<br />'; //titulo
		echo $analise->post_excerpt.'<br />'; //texto resumido
		echo get_permalink($analise->ID).'<br />'; //link
	}
	
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
	
	echo '<h3>Notícias</h3><br />';
	foreach($noticias as $noticia) {
		echo $noticia->post_date.'<br />'; //data
		echo $noticia->post_title.'<br />'; //titulo
		echo $noticia->post_excerpt.'<br />'; //texto resumido
		echo get_permalink($noticia->ID).'<br />'; //link
	}
	
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
	echo '<h3>Obras</h3><br />';
	$obras = get_posts($args);
	foreach($obras as $obra) {
		echo $obra->post_date.'<br />'; //data
		echo $obra->post_title.'<br />'; //titulo
		echo $obra->post_excerpt.'<br />'; //texto resumido
		echo get_permalink($obra->ID).'<br />'; //link
		get_the_post_thumbnail( $obra->ID, '32x32' );
	}
?>	

<!--Daqui pra frente é a página normal do tema - default -->
	<div class="col-md-9 cont-grid">					
						<h2 class="grid-tit"><a href="<?php //the_permalink(); ?>"><?php //the_title(); ?></a></h2>
						
						<p class="meta"> <i class="fa fa-clock-o"></i> <?php //the_time('j M , Y') ?> &nbsp;
<?php /**		
		<?php if ( is_search() ) { ?>

			<p class="result">Result for: <strong><i><?php echo $s ?></i></strong></p>
		
		<?php }  ?>

		<div class="grid">
					
			<?php if (have_posts()) :?><?php while(have_posts()) : the_post(); ?> 

				<div class="item">
				
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
						<p class="grid-cat"><?php the_category(','); the_taxonomies(','); ?></p> 
						
						<h2 class="grid-tit"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						
						<p class="meta"> <i class="fa fa-clock-o"></i> <?php the_time('j M , Y') ?> &nbsp;
						
							<?php 
							$video = get_post_meta($post->ID, 'fullby_video', true );
							
							if($video != '') { ?>
						 			
						 		<i class="fa fa-video-camera"></i> Video
						 			
						 	<?php } else if (strpos($post->post_content,'[gallery') !== false) { ?>
						 			
						 		<i class="fa fa-th"></i> Gallery
						
								<?php } else {?>
						
								<?php } ?>
								
						</p>
						 
						<?php $video = get_post_meta($post->ID, 'fullby_video', true );
						
						if($video != '') {?>
						
						
					    	<a href="<?php the_permalink(); ?>" class="link-video">
								<img src="http://img.youtube.com/vi/<?php echo $video ?>/hqdefault.jpg" class="grid-cop"/>
								<i class="fa fa-play-circle fa-4x"></i> 
							</a>
						
						<?php 				                 
						
							} else if ( has_post_thumbnail() ) { ?>
						
						   <a href="<?php the_permalink(); ?>">
						        <?php the_post_thumbnail('medium', array('class' => 'grid-cop')); ?>
						   </a>
						
						<?php } ?>
						
						<div class="grid-text">
						
							<?php the_excerpt();?>
							
						</div>
						
						<p>
							<?php $post_tags = wp_get_post_tags($post->ID); if(!empty($post_tags)) {?>
								<span class="tag-post"> <i class="fa fa-tag"></i> <?php the_tags('', ', ', ''); ?> </span>
							<?php } ?>
						</p>
						
					</div>
					
				</div>	

			<?php endwhile; ?>
	        <?php else : ?>

	                <p>Sorry, no posts matched your criteria.</p>

	        <?php endif; ?> 

		</div>	
**/?>
			
		</div>
			
	</div>
	
	<div class="col-md-3 sidebar">

		<?php get_sidebar( 'primary' ); ?>		
		    
	</div>
	
<?php get_footer(); ?>	