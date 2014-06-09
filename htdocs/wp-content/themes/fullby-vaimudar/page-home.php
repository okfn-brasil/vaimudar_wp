<?php get_header(); ?>			
		
	<div class="col-md-9 single">
	<?php 
		$analises = new WP_Query();
		$analises->query('post_type=analises');
	?>
		<div class="col-md-9 single-in">
		
			<?php if ($analises->have_posts()) :?><?php while($analises->have_posts()) : $analises->the_post(); ?> 

				<?php if ( has_post_thumbnail() ) { ?>

                    <?php the_post_thumbnail('single', array('class' => 'sing-cop')); ?>

                <?php } else { ?>
                
                	<div class="row spacer-sing"></div>	
                
                 <?php }  ?>
				
				<div class="sing-tit-cont">
					
					<h3 class="sing-tit"><?php the_title(); ?></h3>
				
				</div>

				<div class="sing-cont">
					
					<div class="sing-spacer">
					
						<?php the_content('Leggi...');?>

					</div>

				</div>	
				 					
			<?php endwhile; ?>
	        <?php else : ?>

	                <p>Nenhuma análise disponível.</p>
	         
	        <?php endif; ?> 
	        
		</div>	
		 
		<div class="col-md-3">
		
			<div class="sec-sidebar">

				<?php get_sidebar( 'secondary' ); ?>	
										
		    </div>
		   
		 </div>

	</div>			

	<div class="col-md-3 sidebar">

		<?php get_sidebar( 'primary' ); ?>	
		    
	</div>
		
<?php get_footer(); ?>