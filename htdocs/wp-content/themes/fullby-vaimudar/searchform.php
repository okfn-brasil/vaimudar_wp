<form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
	<div class="row">
		<div class="col-xs-12">
			<label class="screen-reader-text" for="s">Buscar por:</label>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-11 no-gap-right">
			<input type="text" value="" name="s" id="s" />
		</div>
		<div class="col-xs-1 no-gap-left">
			<button type="submit" id="searchsubmit" /><i class="fa fa-search fa-2x"></i></button>
		</div>
	</div>
</form>