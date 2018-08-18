<?php 
function getSmallest($Arr){
	$max = max($Arr);
	$min = min($Arr);
	$smallest = $min;
	for ($i=0; $i < $max ; $i++) { 
		if(!in_array($i, $Arr)){
			     $is_smallest = 0;
            foreach ($Arr as $defaults) {
            	if($i > $default){
                      //not the smallest though
            	}else{
            		$is_smallest = $i;
            		break;
            	}
            }
		}
	}
}


// getSmallest([1,2,3,4,3,2,1]);

?>