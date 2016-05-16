app.service("PwdScore", [
	function () {

		this.ratePassword = function(password){

			var result = zxcvbn(password);
			sdiv = document.getElementById("scoreDiv");
			span= document.getElementById("scoreValue");
			var score=result.score;

			switch(score){

				case 0: sdiv.style.width = '20%';
					sdiv.style.backgroundColor = 'rgb(255, 51, 51)';
					span.innerHTML="Schlecht";
					break;
				case 1: sdiv.style.width = '40%';
					sdiv.style.backgroundColor = 'rgb(255, 153, 102)';
					span.innerHTML="Mäßig";
					break;
				case 2: sdiv.style.width = '60%';
					sdiv.style.backgroundColor = 'rgb(255, 221, 153)';
					span.innerHTML="Okay";
					break;
				case 3: sdiv.style.width = '80%';
					sdiv.style.backgroundColor = 'rgb(255, 255, 0)';
					span.innerHTML="Gut";
					break;
				case 4: sdiv.style.width = '100%';
					sdiv.style.backgroundColor = 'rgb(153, 255, 51)';
					span.innerHTML="Sehr gut";
					break;
				default: sdiv.style.width = '20%';
					sdiv.style.backgroundColor = 'rgb(255, 51, 51)';
					span.innerHTML="Schlecht";

			}
		}

	}]);
