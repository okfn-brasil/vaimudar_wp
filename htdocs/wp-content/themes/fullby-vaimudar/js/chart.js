jQuery(document).ready(function(){
	$data 	= jQuery.parseJSON(jQuery('#chart_data').attr('value'));
	//console.log($data);
	
	
	var options = {
	    series: {
	        lines: { show: true },
	        points: { show: true }
	    }
	};
	
	$chart_data = new Array();
	for( key in $data ) {
		$final_data = new Array();
		
		for( $i=0; $i < $data[key].length; $i++ ) {
			$final_data.push( [ $data[key][$i]['ano'], $data[key][$i]['valor'] ] );
		}
		
		$chart_data.push( { label: key, data: $final_data } );
	}
	
	console.log($chart_data);
	
	var plot = jQuery.plot('#chart', $chart_data, options);
	
//	var plot = jQuery.plot('#chart', [ { label: "Foo", data: [ [2002, 1], [17, -14], [30, 5] ] },
//	                                   { label: "Bar", data: [ [11, 13], [19, 11], [30, -7] ] }
//	], options);
});