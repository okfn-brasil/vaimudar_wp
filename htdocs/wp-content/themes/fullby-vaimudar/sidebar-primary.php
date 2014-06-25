	
	<div class="tab-header">
		<span class="greenlight">#</span>Discussões
	</div>
	
	<div class="tab-spacer">

	</div>
	<?php 
		$args = array(
				    'orderby'          => 'name',
				    'order'            => 'ASC',
				    'limit'            => -1,
				    'category_name'    => 'dos',
				    'hide_invisible'   => 1,
				    'show_updated'     => 0,
				    'echo'             => 1,
				    'categorize'       => 0,
				    'title_li'         => __('Participe nas discussões das leis'),
//				    'title_before'     => '<h2>',
//				    'title_after'      => '</h2>',
				    'category_orderby' => 'name',
				    'category_order'   => 'ASC',
//				    'class'            => 'linkcat',
				    'category_before'  => '<li id=%id class=%class style="list-style-type: none;">',
				    'category_after'   => '</li>' 
				);
				 
		wp_list_bookmarks($args);
	?>
	
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Primary Sidebar') ) : ?>
	 
	<?php endif; ?>