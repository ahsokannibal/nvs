


var dataPieChart, dataBarChart, dataGradeBarChart, pgPieChart;
function setChart(){
	var chPie = document.getElementById("playersPieChart");
	if (chPie) {
	  new Chart(chPie, {
	    type: 'pie',
	    data: {
	      labels: ['Nord', 'Sud'],
	      datasets: [
	        {
	          backgroundColor: ['blue','red'],
	          borderWidth: 0,
	          data: dataPieChart.map(e=>e.compte)
	        }
	      ]
	    },
	    plugins: [{
	      beforeDraw: function(chart) {
	        var width = chart.chart.width,
	            height = chart.chart.height,
	            ctx = chart.chart.ctx;
	        ctx.restore();
	        var fontSize = (height / 70).toFixed(2);
	        ctx.font = fontSize + "em sans-serif";
	        ctx.textBaseline = "middle";
	        //here write difference percentage
	       // console.log((chart.config.data.datasets[0].data[0] / chart.config.data.datasets[0].data[1])*100 - 100)
	        var calcul = (chart.config.data.datasets[0].data[0] / chart.config.data.datasets[0].data[1])*100 - 100;
	        var text = calcul.toFixed(2) + "%",
	            textX = Math.round((width - ctx.measureText(text).width) / 2),
	            textY = height / 2;
	        ctx.fillText(text, textX, textY);
	        ctx.save();
	      }
	    }],
	    options: {
	    	layout:{padding:0}, 
	    	legend:{display:false}, 
	    	cutoutPercentage: 80/*,
			title: {
				display: true,
				text: "Disparité de chefs actifs par camp"
			}*/
	    }
	  });
	}
}




function getBarChartData(labels, dataNord, dataSud){
	return {
		labels: labels,
		datasets: [{
			label: 'Nord',
			backgroundColor: 'blue',
			borderColor: 'blue',
			borderWidth: 1,
			data: dataNord
		}, {
			label: 'Sud',
			backgroundColor: 'red',
			borderColor: 'red',
			borderWidth: 1,
			data: dataSud
		}]

	};
}

var listTypes = ['Chef', 'Infanterie', 'Cavalerie lourde', 'Soigneur', 'Artillerie', 'Toutou', 'Cavalerie légère'];


function createGrouillotBarChartDatas(labels, dataNord, dataSud){
	listTypes.forEach(function(type){
		var countNord;
		var foundNord = dataBarChart.find(function(element, index){
			if(element.camp=='1' && element.type==type){
				return true;
			}
		});
		if(foundNord != undefined){
			countNord = foundNord.compte;
		}else{
			countNord=0;
		}
		
		var countSud;
		var foundSud = dataBarChart.find(function(element, index){
			if(element.camp=='2' && element.type==type){
				return true;
			}
		});
		
		if(foundSud != undefined){
			countSud = foundSud.compte;
		}else{
			countSud=0;
		}
		
		if(countNord != 0 || countSud != 0){
			labels.push(type);
			dataNord.push(countNord);
			dataSud.push(countSud);
		}
		
		
	});
	
}

function setGrouillotChart(){
	var labels = [];
	var dataNord = [];
	var dataSud = [];

	createGrouillotBarChartDatas(labels, dataNord, dataSud);

	var ctx = document.getElementById('playersGrouillot').getContext('2d');
	window.myBar = new Chart(ctx, {
		type: 'bar',
		data: getBarChartData(labels, dataNord, dataSud),
		options: {
			responsive: true,
			legend: {
				display: false,
			}/*,
			title: {
				display: true,
				text: "Disparité par camp"
			}*/
		}
	});
}

function setPgPieChart(){
	var chPie = document.getElementById("pgPieChart");
	if (chPie) {
	  new Chart(chPie, {
	    type: 'pie',
	    data: {
	      labels: ['Nord', 'Sud'],
	      datasets: [
	        {
	          backgroundColor: ['blue','red'],
	          borderWidth: 0,
	          data: pgPieChart.map(e=>e.compte)
	        }
	      ]
	    },
	    plugins: [{
	      beforeDraw: function(chart) {
	        var width = chart.chart.width,
	            height = chart.chart.height,
	            ctx = chart.chart.ctx;
	        ctx.restore();
	        var fontSize = (height / 70).toFixed(2);
	        ctx.font = fontSize + "em sans-serif";
	        ctx.textBaseline = "middle";
	        //here write difference percentage
	       // console.log((chart.config.data.datasets[0].data[0] / chart.config.data.datasets[0].data[1])*100 - 100)
	        var calcul = (chart.config.data.datasets[0].data[0] / chart.config.data.datasets[0].data[1])*100 - 100;
	        var text = calcul.toFixed(2) + "%",
	            textX = Math.round((width - ctx.measureText(text).width) / 2),
	            textY = height / 2 ;
	        ctx.fillText(text, textX, textY);
	        ctx.save();
	      }
	    }],
	    options: {
	    	layout:{padding:0}, 
	    	legend:{display:false}, 
	    	cutoutPercentage: 80,
			/*title: {
				display: true,
				text: "Disparité points de grouillots"
			}*/
	    }
	  });
	}
}

function createGadeBarChartDatas(labels, dataNord, dataSud){
	dataGradeBarChart.forEach(function(data){
		var already = labels.find(function(element, index){
			if(element == data.grade){
				return true;
			}
		});
		if(already){
			return;
		}
		var countNord;
		var foundNord = dataGradeBarChart.find(function(element, index){
			if(element.camp=='1' && element.grade===data.grade){
				return true;
			}
		});

		if(foundNord != undefined){
			countNord = foundNord.compte;
		}else{
			countNord=0;
		}
		
		var countSud;
		var foundSud = dataGradeBarChart.find(function(element, index){
			if(element.camp=='2' && element.grade===data.grade){
				return true;
			}
		});
		
		if(foundSud != undefined){
			countSud = foundSud.compte;
		}else{
			countSud=0;
		}
		if(countNord != 0 || countSud != 0){
			labels.push(data.grade);
			dataNord.push(countNord);
			dataSud.push(countSud);
		}
		
		
	});
	
}

function setGradesChart(){
	var labels = [];
	var dataNord = [];
	var dataSud = [];

	createGadeBarChartDatas(labels, dataNord, dataSud);

	var ctx = document.getElementById('gradesChart').getContext('2d');
	window.myBar = new Chart(ctx, {
		type: 'bar',
		data: getBarChartData(labels, dataNord, dataSud),
		options: {
			responsive: true,
			legend: {
				display: false,
			}/*,
			title: {
				display: true,
				text: "Disparité des grades par camp"
			}*/
		}
	});
}

$.ajax({
    method: "POST",
    url: "functions_statistiques.php",
    data:{
		"type" :"player",
		"function":"playersSideCharts",
		"params":'{"activeFor":6}'
    },
    success: function(data){
    	dataPieChart = data;
        setChart();
        //setChart2(data);
    },
    error: function(error_data){
        console.log("Endpoint GET request error");
        // console.log(error_data)
    }
});

$.ajax({
    method: "POST",
    url: "functions_statistiques.php",
    data:{
		"type" :"player",
		"function":"playersGrouillotsCharts",
		"params":'{"activeFor":6}'
    },
    success: function(data){
    	dataBarChart = data;
        setGrouillotChart();
    },
    error: function(error_data){
        console.log("Endpoint GET request error");
        // console.log(error_data)
    }
});

$.ajax({
    method: "POST",
    url: "functions_statistiques.php",
    data:{
		"type" :"player",
		"function":"pgPieChart",
		"params":'{"activeFor":6}'
    },
    success: function(data){
    	pgPieChart = data;
        setPgPieChart();
        //setChart2(data);
    },
    error: function(error_data){
        console.log("Endpoint GET request error");
        // console.log(error_data)
    }
});

$.ajax({
    method: "POST",
    url: "functions_statistiques.php",
    data:{
		"type" :"player",
		"function":"playersGradeCharts",
		"params":'{"activeFor":6}'
    },
    success: function(data){
    	dataGradeBarChart = data;
        setGradesChart();
    },
    error: function(error_data){
        console.log("Endpoint GET request error");
        // console.log(error_data)
    }
});

$(window).resize(function(){
	setChart();
	setGrouillotChart();
});