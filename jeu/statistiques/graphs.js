var dataPieChart, dataBarChart, dataGradeBarChart, pgPieChart, xpBarChart, dataXpGradeBarChart;


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
	        var calcul = (chart.config.data.datasets[0].data[0] - chart.config.data.datasets[0].data[1]) * 100 / (chart.config.data.datasets[0].data[0] + chart.config.data.datasets[0].data[1]);
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

var listTypes = ['Chef', 'Infanterie', 'Cavalerie lourde', 'Soigneur', 'Artillerie', 'Gatling', 'Toutou', 'Cavalerie légère'];


function createGrouillotBarChartDatas(dataBarChart, labels, dataNord, dataSud){
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

	createGrouillotBarChartDatas(dataBarChart, labels, dataNord, dataSud);

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
	        var calcul = (parseInt(chart.config.data.datasets[0].data[0]) - parseInt(chart.config.data.datasets[0].data[1])) * 100 / (parseInt(chart.config.data.datasets[0].data[0]) + parseInt(chart.config.data.datasets[0].data[1]));
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

function setXpBarChart(){
	var labels = [];
	var dataNord = [];
	var dataSud = [];

	createGrouillotBarChartDatas(xpBarChart, labels, dataNord, dataSud);

	var chPie = document.getElementById("xpBarChart");
	if (chPie) {
	  new Chart(chPie, {
	    type: 'bar',
	    data: getBarChartData(labels, dataNord, dataSud),
	    options: {
	    	layout:{padding:0}, 
	    	legend:{display:false}, 
	    	cutoutPercentage: 80
	    }
	  });
	}
}

function setCompaPieChart(data, chartId){
	let chPie = document.getElementById(chartId);
	let compasNames = [], compasColors = ['#FF6633', '#FFB399', '#FF33FF', '#FFFF99', '#00B3E6', 
	'#E6B333', '#3366E6', '#999966', '#99FF99', '#B34D4D',
	'#80B300', '#809900', '#E6B3B3', '#6680B3', '#66991A', 
	'#FF99E6', '#CCFF1A', '#FF1A66', '#E6331A', '#33FFCC',
	'#66994D', '#B366CC', '#4D8000', '#B33300', '#CC80CC', 
	'#66664D', '#991AFF', '#E666FF', '#4DB3FF', '#1AB399',
	'#E666B3', '#33991A', '#CC9999', '#B3B31A', '#00E680', 
	'#4D8066', '#809980', '#E6FF80', '#1AFF33', '#999933',
	'#FF3380', '#CCCC00', '#66E64D', '#4D80CC', '#9900B3', 
	'#E64D66', '#4DB380', '#FF4D4D', '#99E6E6', '#6666FF'], playerCount=[];
	data.forEach(element => createCompaPieChartDatas(element, compasNames, playerCount));
	if (chPie) {
	  new Chart(chPie, {
	    type: 'pie',
	    data: {
			labels: compasNames,
			datasets: [{
				backgroundColor: compasColors,
				borderWidth: 0,
				data: playerCount
			  }
			]
		},
	    options: {
	    	layout:{padding:0}, 
	    	legend:{
	    		display:(window.innerWidth < 1024) ? false : true,
	    		position:"bottom"
	    	}
	    }
	  });
	}
}

function createCompaPieChartDatas(element, compasNames, playerCount){
	compasNames.push(element.nom + " : " + element.membres);
	playerCount.push(parseInt(element.membres,10));
}



function createGadeBarChartDatas(dataBarChart, labels, dataNord, dataSud){
	dataBarChart.forEach(function(data){
		var already = labels.find(function(element, index){
			if(element == data.grade){
				return true;
			}
		});
		if(already){
			return;
		}
		var countNord;
		var foundNord = dataBarChart.find(function(element, index){
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
		var foundSud = dataBarChart.find(function(element, index){
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

function setGradesChart(dataGradeBarChart){
	var labels = [];
	var dataNord = [];
	var dataSud = [];

	createGadeBarChartDatas(dataGradeBarChart, labels, dataNord, dataSud);

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

function setXpGradesChart(dataXpGradeBarChart){
	var labels = [];
	var dataNord = [];
	var dataSud = [];

	createGadeBarChartDatas(dataXpGradeBarChart, labels, dataNord, dataSud);

	var ctx = document.getElementById('xpGradesChart').getContext('2d');
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
        setGradesChart(dataGradeBarChart);
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
		"type" :"compagnie",
		"function":"listAllByCamp",
		"params":'{"activeFor":6,"camp":1}'
    },
    success: function(data){
        setCompaPieChart(data, "nordCompaPieChart");
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
		"type" :"compagnie",
		"function":"listAllByCamp",
		"params":'{"activeFor":6,"camp":2}'
    },
    success: function(data){
        setCompaPieChart(data, "sudCompaPieChart");
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
		"function":"xpBarChart",
		"params":'{"activeFor":6}'
    },
    success: function(data){
        xpBarChart = data;
		console.log(data)
        setXpBarChart();
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
		"function":"xpByGradeChart",
		"params":'{"activeFor":6}'
    },
    success: function(data){
		dataXpGradeBarChart = data;
        setXpGradesChart(dataXpGradeBarChart);
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
