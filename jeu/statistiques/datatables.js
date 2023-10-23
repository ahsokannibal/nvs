$(document).ready(function () {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
 
    $('#armesData').DataTable( {
		'rowCallback': function(row, data, index){
			if(data['camp']== "2"){
				$(row).find('td:eq(4)').css('color', 'red');
			}else if (data['camp']== "1"){
				$(row).find('td:eq(4)').css('color', 'blue');
			}else{
                $(row).find('td:eq(4)').css('color', 'black');
            }
		},
        "ajax": {
            "url" : "functions_statistiques.php",
            "type":"POST",
            "data":{
				"type" :"arme",
				"function":"listAll"
            },
            "dataSrc": ""
		},
		"responsive":"true",
		"order": [[ 0, "desc" ]],
		"columns": [
            { "data": "arme" },
            { "data": "attaques" },
            { "data": "precision" },
            { "data": "degats" },
            { "data": "camp" },
        ],
        "columnDefs": [ {
            "targets": 4,
            "data": "camp",
            "render": function (data, type, full, meta){
                if(data==1){
                    return 'Nord';
                }else if (data==2){
                    return 'Sud';
                }
                return 'Autre';
            }
        }]
        
    });

    $('#playersData').DataTable( {
		'rowCallback': function(row, data, index){
			if(data['camp']== "2"){
				$(row).find('td:eq(4)').css('color', 'red');
			}else if (data['camp']== "1"){
				$(row).find('td:eq(4)').css('color', 'blue');
			}else{
                $(row).find('td:eq(4)').css('color', 'black');
            }
		},
        "ajax": {
            "url" : "functions_statistiques.php",
            "type":"POST",
            "data":{
				"type" :"player",
				"function":"listAll",
				"params":"..."
            },
            "dataSrc": ""
		},
		"responsive":"true",
		"order": [[ 0, "desc" ]],
		"columns": [
            { "data": "matricule" },
            { "data": "nom" },
            { "data": "type" },
            { "data": "grade" },
            { "data": "camp" },
            { "data": "bataillon" },
            { "data": "xp" },
            { "data": "compagnie" }
        ],
        "columnDefs": [ {
            "targets": 0,
            "data": "matricule",
            "render": function (data, type, full, meta){
                return '<a target="_blank" href="https://nord-vs-sud.fr/jeu/evenement.php?infoid='+data+'">'+ data + '</a>';
            
            }
            
        },{
            "targets": 4,
            "data": "camp",
            "render": function (data, type, full, meta){
                if(data==1){
                    return 'Nord';
                }else if (data==2){
                    return 'Sud';
                }
                return 'Autre';
            }
        }]
        
    });

    $('#compagniesData').DataTable( {
		'rowCallback': function(row, data, index){
			if(data['camp']== "2"){
				$(row).find('td:eq(2)').css('color', 'red');
			}else if (data['camp']== "1"){
				$(row).find('td:eq(2)').css('color', 'blue');
			}else{
                $(row).find('td:eq(2)').css('color', 'black');
            }
		},
        "ajax": {
            "url" : "functions_statistiques.php",
            "type":"POST",
            "data":{
				"type" :"compagnie",
				"function":"listAll",
				"params":'{"activeFor":6}'
            },
            "dataSrc": ""
		},
		"responsive":"true",
		"order": [[ 0, "desc" ]],
		"columns": [
            { "data": "nom" },
            { "data": "membres" },
            { "data": "camp" }
        ],
        "columnDefs": [ {
            "targets": 2,
            "data": "camp",
            "render": function (data, type, full, meta){
                if(data==1){
                    return 'Nord';
                }else if (data==2){
                    return 'Sud';
                }
                return 'Autre';
            }
        }]
        
    });
});