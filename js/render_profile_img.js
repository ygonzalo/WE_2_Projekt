//render canvas mit bunten vierecken
function renderProfileImg(){
	
	var templates =	document.getElementsByClassName("profile_img");
	
	for (var i=0; i<templates.length; i++)
	{
		var can = templates[i].getElementsByTagName('canvas')[0];
		var ctx = can.getContext("2d");
		var input = templates[i].getElementsByTagName('input')[0].value;
		var input2 = input.split('-');
						
		ctx.canvas.width  = 320;
		ctx.canvas.height = 400;
		ctx.clearRect(0,0,320,400);
	
		if(typeof input2[4] == 'undefined' && input2[4] == null){
			color1 = getRandomColor(0);
			color2 = getRandomColor(0);
			color3 = getRandomColor(0);
			color4 = getRandomColor(0)
			color5 = getRandomColor(0);
		}else{
			color1 = getRandomColor(input2[0]);
			color2 = getRandomColor(input2[1]);
			color3 = getRandomColor(input2[2]);
			color4 = getRandomColor(input2[3]);
			color5 = getRandomColor(input2[4]);
		}
		
		//erzeugen vierecke
		ctx.fillStyle = color1;
		ctx.fillRect(0,0,100,300);
		ctx.fillStyle = color2;
		ctx.fillRect(0,300,220,100);
		ctx.fillStyle = color3;
		ctx.fillRect(100,0,220,100);
		ctx.fillStyle = color4;
		ctx.fillRect(220,100,100,300);
		ctx.fillStyle = color5;
		ctx.fillRect(100,100,120,200);				
		
	}
	
}

	
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

document.onload = renderProfileImg();