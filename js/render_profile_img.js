//render canvas mit bunten vierecken
	
function getRandomColor(i){

	zzahl = Math.floor(i);
	
	if(zzahl == 0){
		var zzahl = Math.floor(Math.random() * 4)+1;
	}			
	
	switch(zzahl) {
		case 1:
			color= "rgba(149,211,28,1)";
			break;
		case 2:
			color= "rgba(239,231,16,1)";
			break;
		case 3:
			color= "rgba(234,53,143,1)";
			break;
		case 4:
			color= "rgba(105,22,233,1)";
			break;
	}
	return color;
}
