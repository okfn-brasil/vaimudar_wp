<?php get_header(); ?>			
		
	<div class="col-md-9 single">
	
		<div class="col-md-9 single-in">
		 <img width="800" height="494" src="http://poder.vaimudar.org/wp-content/uploads/2014/06/800px-Os_Candangos-800x494.jpg" class="sing-cop wp-post-image" alt="800px-Os_Candangos">
		
		<?php $terms = get_terms('redes-de-poder') ;?>
		
			<?php if (have_posts()) :?><?php while(have_posts()) : the_post(); ?> 

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
					
						<?php the_content();?>

					</div>

				</div>	
				 					
			<?php endwhile; ?>
			<div class="sing-cont">
					<div class="sing-spacer">
			<?php 
			
				echo '<ul>';
				foreach($terms as $term) {
					echo '<li>';
					echo '<div class="title"><a href="/redes-de-poder/'.$term->slug.'">'.$term->name.'</a></div>'; //titulo
					echo '<div class="resume">'.$term->post_excerpt.'</div>'; //texto resumido
					echo '<div class="hr"><hr></div>';
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
				echo '</div>';
			
			
	        else : ?>

	                <p>Desculpe, nenhum post no momento.</p>
	         
	        <?php endif; ?> 
	        
		</div>
		
		<div class="pagination">
		
			<?php
			global $wp_query;
			
			$big = 999999999; // need an unlikely integer
			
			echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages
			) );
			?>
			
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