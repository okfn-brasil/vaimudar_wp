<div class="col-md-12 footer">
	<div class="row">
		<div class="col-md-3 logo-footer">
			<img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-footer.png" title="<?php bloginfo('name'); ?>" alt="<?php bloginfo('name'); ?>">
		</div>
		<div class="col-md-9 text-right">
			<ul class="footer-logos">
				<li class="label">Apoio:</li>
				<li class="image divider"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-avina.png"></li>
				<li class="label divider">Realização:</li>
				<li class="divider"></li>
				<li class="image"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-okfn-footer.png"></li>
			</ul>
		</div>
	</div>
</div>
<div class="col-md-12 footer copyright text-center">
	Um projeto da <a href="http://br.okfn.org/" target="_blank">Open Knowledge Brasil</a> 2014 - Todo conteúdo desse site é licenciado sob uma Licença <a href="http://creativecommons.org/licenses/by/3.0/br/" target="_blank">Creative Commons Atribuição 3.0</a>
</div>
	
	

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="http://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/bootstrap.min.js"></script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/isotope.js"></script>
   
<script>
(function ($) {
	var $container = $('.grid'),
		colWidth = function () {
			var w = $container.width(), 
				columnNum = 1,
				columnWidth = 0;
			if (w > 1200) {
				columnNum  = 4;
			} else if (w > 900) {
				columnNum  = 3;
			} else if (w > 600) {
				columnNum  = 2;
			} else if (w > 300) {
				columnNum  = 1;
			}
			columnWidth = Math.floor(w/columnNum);
			$container.find('.item').each(function() {
				var $item = $(this),
					multiplier_w = $item.attr('class').match(/item-w(\d)/),
					multiplier_h = $item.attr('class').match(/item-h(\d)/),
					width = multiplier_w ? columnWidth*multiplier_w[1]-10 : columnWidth-10,
					height = multiplier_h ? columnWidth*multiplier_h[1]*0.5-40 : columnWidth*0.5-40;
				$item.css({
					width: width,
					//height: height
				});
			});
			return columnWidth;
		},
		isotope = function () {
			$container.imagesLoaded( function(){
				$container.isotope({
					resizable: false,
					itemSelector: '.item',
					masonry: {
						columnWidth: colWidth(),
						gutterWidth: 20
					}
				});
			});
		};
		
	isotope();
	
	$(window).smartresize(isotope);
	
	//image fade
	$('.item img').hide().one("load",function(){
    	$(this).fadeIn(500);
    }).each(function(){
    	if(this.complete) $(this).trigger("load");
    });
    
    //tab sidebar
    $('#myTab a').click(function (e) {
	  e.preventDefault()
	  $(this).tab('show')
	})

	
}(jQuery));


</script>
	
	<?php wp_footer();?>
    
  </body>
</html>

    	