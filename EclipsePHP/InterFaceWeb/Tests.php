<?php 

	$id_rubrique = rand(0,15);
	switch($id_rubrique) {
		case (($id_rubrique==5) or ($id_rubrique==11) or ($id_rubrique==17) or ($id_rubrique==40)) :
			$color='#ff9400';
			
			break;

		case (($id_rubrique==6) or ($id_rubrique==12) or ($id_rubrique==18) or ($id_rubrique==41)) :
			$color='#abd825';
			
			break;
		case (($id_rubrique==7) or ($id_rubrique==13) or ($id_rubrique==35) or ($id_rubrique==42)) :
			$color='#c16bb2';
			
			break;

		case (($id_rubrique==8) or ($id_rubrique==14) or ($id_rubrique==36) or ($id_rubrique==43)) :
			$color='#62b6a7';
			
			break;

		case (($id_rubrique==9) or ($id_rubrique==15) or ($id_rubrique==37) or ($id_rubrique==44)) :
			$color='#f93e4f';
			
			break;

		case (($id_rubrique==10) or ($id_rubrique==16) or ($id_rubrique==38) or ($id_rubrique==45)) :
			$color='#CC0000';
		
			break; }


?>