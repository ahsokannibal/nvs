
Date.prototype.yyyymmdd = function() {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
            ].join('-');
};

const pixel_size = adjustPixelSizeOnScreenSize();
const pixel_distance = 1;
const map_size = 201;
const maxScale = 13;
const images = [];

// couleurs perso_carte brouillard
const gris_brouillard 				= 'rgb(80, 80, 80)'; // noir
const blanc 				        = 'rgb(255, 255, 255)'; // blanc
const noir 							= 'rgb(0, 0, 0)'; // noir
const grey 							= 'rgb(125, 125, 125)'; // gris
const brouillard_general			= noir;
const couleur_vert 					= 'rgb(10, 254, 10)'; // vert bien voyant
const couleur_perso_clan1 			= 'rgb(10, 10, 254)'; // bleu bien voyant
const couleur_perso_clan2 			= 'rgb(254, 10, 10)'; // rouge bien voyant
const couleur_perso_defaut          = 'rgb(130, 20, 130)';// mauve 
const couleur_bat_clan1 			= 'rgb(75, 75, 254)'; // bleu batiments
const couleur_bat_clan2 			= 'rgb(254, 75, 75)'; // rouge batiments
const couleur_bat_neutre			= 'rgb(130, 130, 130)'; // gris batiments
const couleur_rail					= 'rgb(200, 200, 200)'; // gris rails
const couleur_brouillard_plaine		= 'rgb(208, 192, 122)'; // Chamois
const couleur_brouillard_eau		= 'rgb(187, 174, 152)'; // Gr�ge
const couleur_brouillard_marecage   = 'rgb(175, 176, 118)';
const couleur_brouillard_montagne	= 'rgb(47, 27, 12)'; // Cachou
const couleur_brouillard_colinne	= 'rgb(133, 109, 77)'; // Bistre
const couleur_brouillard_desert		= 'rgb(225, 206, 154)'; // Vanille
const couleur_brouillard_foret		= 'rgb(97, 77, 26)'; // Vanille
const couleur_brouillard_image	    = 'rgba(208, 192, 122, 0.5)'; // translucide

// couleurs hors brouillard
const couleur_plaine 	            = 'rgb(129, 156, 84)'; // vert clair
const couleur_colline 	            = 'rgb(96, 110, 70)'; // 
const couleur_montagne 	            = 'rgb(134, 118, 89)'; // marron foncé
const couleur_desert 	            = 'rgb(215, 197, 101)'; // jaune foncé (penchant vers le marron)
const couleur_neige 		        = 'rgb(232, 248, 248)'; // blanc
const couleur_marecage 	            = 'rgb(169, 177, 166)'; // gris
const couleur_foret 		        = 'rgb(60, 86, 33)'; // vert foncé
const couleur_eau 		            = 'rgb(92, 191, 207)'; // bleu clair
const couleur_eau_p 		        = 'rgb(39, 141, 227)'; // bleu foncé


const topographie_checkbox = document.getElementById('topographie');
topographie_checkbox.addEventListener('change', (event)=>{
    drawMap(currentMap);
});
const brouillard_checkbox = document.getElementById('brouillard');
brouillard_checkbox.addEventListener('change', (event)=>{
    mapTiles.forEach(tile =>{
        if(tile.brouillard!=undefined){
            tile.draw(canvas, ctx);
        }
    });
});
const joueurs_checkbox = document.getElementById('joueurs');
joueurs_checkbox.addEventListener('change', (event)=>{
    mapTiles.forEach(tile =>{
        if(tile.joueur!=undefined){
            tile.draw(canvas, ctx);
        }
    });
});
const batiments_checkbox = document.getElementById('batiments');
batiments_checkbox.addEventListener('change', (event)=>{
    mapTiles.forEach(tile =>{
        if(tile.batiment!=undefined){
            tile.draw(canvas, ctx);
        }
    });
});/*
const contraintes_batiments_checkbox = document.getElementById('contraintes_batiments');
contraintes_batiments_checkbox.addEventListener('change', (event)=>{
    mapTiles.forEach(tile =>{
        tile.draw(canvas, ctx);
    });
});*/
const bataillon_checkbox = document.getElementById('bataillon');
bataillon_checkbox.addEventListener('change', (event)=>{
    drawMap(currentMap);
});
const compagnie_checkbox = document.getElementById('compagnie');
compagnie_checkbox.addEventListener('change', (event)=>{    
    drawMap(currentMap);
});

const canvas = document.getElementById('map');
const ctx = canvas.getContext('2d');
const evCache = [];
var prevDiff = -1;



var mapTiles;
var histoMaps = new Map();
var currentMap;

var currentTile;

var translatePos = {
    x: 0,
    y: 0
};

var scale = 1.0;
var scaleWheelMultiplier = 0.4;
var scalePinchMultiplier = 0.1;
var startDragOffset = {};
var mouseDown = false;
var originx = 0;
var originy = 0;

//datepicker en français
;(function($){
    $.fn.datepicker.dates['fr'] = {
    days: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
    daysShort: ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
    daysMin: ["d", "l", "ma", "me", "j", "v", "s"],
    months: ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
    monthsShort: ["janv.", "févr.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
    today: "Aujourd'hui",
    monthsTitle: "Mois",
    clear: "Effacer",
    weekStart: 1,
    format: "dd/mm/yyyy"
    };
}(jQuery));

$( document ).ready(function(){
    
    get_map();
    canvas.addEventListener('mousemove', function(e){
        let tile = getTilePointerPos(canvas, e);
        
        //je mets à jour le tooltip si la case existe
        tile != undefined ? tile.setTooltipContent():'';
    }, false);
    /*canvas.addEventListener("touchmove", function (e) {
        var touch = e.touches[0];
        var mouseEvent = new MouseEvent("mousemove", {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
      }, false);*/

    let startDate='20/09/2022';
    $(canvas).hover(function(){
        $(this).css('cursor','pointer').css('font-weight', 'bold');//.attr('title', 'This is a hover text.');
        //todo improve tooltip http://jsfiddle.net/mannemvamsi/X8MD7/
    }, function() {
        $(this).css('cursor','auto');
    });
     $('#datepicker').datepicker({
        language: 'fr',
        autoclose: true,
        todayHighlight: true,
        todayBtn: true,
        startDate: startDate,
        endDate: new Date()
    }).on("changeDate", function(e){
        const today = new Date();
        today.setHours(0,0,0,0);
        if(e.date == undefined || e.date.valueOf() === today.valueOf()){
            $('#carousel-control-next').hide();
            $('#carousel-control-prev').hide();
           // $('#carousel-caption').hide();
            drawMap(currentMap);
        }else{
            get_historique_map($('#datepicker').datepicker('getDate').yyyymmdd());
            $('#carousel-control-next').show();
            if($('#datepicker').datepicker('getDate').toLocaleDateString('fr-FR') != startDate){
                $('#carousel-control-prev').show();
            }else{
                $('#carousel-control-prev').hide();
            }
           // $('#carouselTitle').text("Map du " + $('#datepicker').datepicker('getDate').toLocaleDateString('fr-FR')).show();
        }
    });
    
    $('#carousel-control-next').on("click", function(e){
        let date = $('#datepicker').datepicker('getDate');
        date.setTime(date.getTime() + (1000*60*60*24));
        $('#datepicker').datepicker("setDate", date);
    });
    $('#carousel-control-prev').on("click", function(e){
        let date = $('#datepicker').datepicker('getDate');
        date.setTime(date.getTime() - (1000*60*60*24));
        $('#datepicker').datepicker("setDate", date);
    });
    $('#carousel-control-next').hide();
    $('#carousel-control-prev').hide();
   // $('#carousel-caption').hide();

    
    // add event listeners to handle screen drag
    canvas.addEventListener("pointerdown", handleStart);
    canvas.addEventListener("pointerup", handleEnd);
    canvas.addEventListener("pointerout", handleEnd);
    canvas.addEventListener("pointerleave", handleEnd);
    canvas.addEventListener("pointercancel", handleEnd);
    canvas.addEventListener("pointermove", handleMove);

    canvas.addEventListener("wheel", handleWheel);

});

function handleWheel(event){
    /*event.preventDefault();

scale += event.deltaY * -0.01;

// Restrict scale
scale = Math.min(Math.max(.125, scale), 4);*/

    event.preventDefault();
    
    // Normalize mouse wheel movement to +1 or -1 to avoid unusual jumps.
    const wheel = event.deltaY < 0 ? 1 : -1;
    zoomedtile = getTilePointerPos(canvas, event);
    
    
    // Compute zoom factor.
    const zoom = Math.exp(wheel * scaleWheelMultiplier);
    
    // Update scale and others.
    scale *= zoom;

    //On ne descend pas sous un certains seuil de dezoom (pour eviter des problemes de calcul de case survolée)
    scale = (scale < 1) ? 1 : scale;
    scale = (scale > maxScale) ? maxScale : scale;

    zoomMap(zoomedtile);
    
    
}

function zoomMap(zoomedtile){


    //const zoom =  Math.min(Math.max(.125, wheel), 4);
    let zommedTileRapportX = zoomedtile.x / map_size;
    let zommedTileRapportY = (map_size - zoomedtile.y) / map_size;

    //let newTransX =  (translatePos.x - map_size) * difX * scale  *  zommedTileRapport ;
    let newTransX = -(canvas.width * scale - canvas.width)  * zommedTileRapportX;
    let newTransY = -(canvas.height * scale - canvas.height)  * zommedTileRapportY;
        

    //console.log(newTransX, newTransY, canvas.offsetWidth, canvas.width, zommedTileRapportX, scale);
    adjustedTranslatePos(newTransX, newTransY);
    
    drawMap(currentMap);
    
}



function handleStart(e){
    evCache.push(e);

    if(evCache.length === 2){
        zoomedtile = getTilePointerPos(canvas, {offsetX:(evCache[0].offsetX + evCache[1].offsetX)/2, offsetY: (evCache[0].offsetY + evCache[1].offsetY)/2});
      //  console.log(evCache[0].offsetX, evCache[1].offsetX)
    }else if (evCache.length === 1 ){
        
        startDragOffset.x = e.clientX - translatePos.x;
        startDragOffset.y = e.clientY - translatePos.y;
    }

   /* startDragOffset.x = e.changedTouches[0].clientX - translatePos.x;
    startDragOffset.y = e.changedTouches[0].clientY - translatePos.y;*/
}
function handleMove(e){

    //zoomedtile = getTilePointerPos(canvas, evCache[0]);
    // Find this event in the cache and update its record with this event
    const index = evCache.findIndex((cachedEv) => cachedEv.pointerId === e.pointerId);
    evCache[index] = e;
    // If two pointers are down, check for pinch gestures
    if (evCache.length === 2) {
    // Calculate the distance between the two pointers
        const curDiffX = Math.abs(evCache[0].clientX - evCache[1].clientX);
        const curdiffY = Math.abs(evCache[0].clientY - evCache[1].clientY)
        let curDiff = (curDiffX > curdiffY) ? curDiffX : curdiffY;

        if (prevDiff > 0) {
            if(curDiff == prevDiff){
                return
            }
            const zoomOrDezoom = (curDiff > prevDiff) ? 1 : -1;
            
            
            
            // Compute zoom factor.
            const zoom = Math.exp(zoomOrDezoom * scalePinchMultiplier);
            
            // Update scale and others.
            scale *= zoom;
        
            //On ne descend pas sous un certains seuil de dezoom (pour eviter des problemes de calcul de case survolée)
            scale = (scale < 1) ? 1 : scale;
            scale = (scale > maxScale) ? maxScale : scale;
            
            zoomMap(zoomedtile);
        }

        // Cache the distance for the next move event
        prevDiff = curDiff;
    }else if (evCache.length === 1) {
        let newTransX = e.clientX - startDragOffset.x;
        let newTransY = e.clientY - startDragOffset.y;
        
        adjustedTranslatePos(newTransX, newTransY);
        drawMap(currentMap);
        evCache.splice(index, 1, e);  // swap in the new touch record
    }else if (evCache.length === 0) {
        if(currentTile != undefined){
            currentTile.draw(canvas, ctx);
        }
        currentTile = getTilePointerPos(canvas, e);
        if(currentTile != undefined){
            currentTile.drawMouseOver(canvas, ctx);
        }
    }
}

function handleEnd(e){
    removeEvent(e);
    if(evCache.length === 0){
        startDragOffset.x = 0;
        startDragOffset.y = 0;
    }
    // If the number of pointers down is less than two then reset diff tracker
    if (evCache.length < 2) {
        prevDiff = -1;
    }
    if(currentTile != undefined){
        currentTile.draw(canvas, ctx);
    }
}


function drawMap(mapTiles){
	var i = 0;
    let startX = Math.floor(Math.abs(translatePos.x) / ((pixel_size + pixel_distance)*scale)) - 2;
    // let lengthX = translatePos.x / ((pixel_size + pixel_distance)*scale) + map_size  + 4;
    let lengthX = (map_size / scale)+3;
    let endY = Math.floor( translatePos.y / ((pixel_size + pixel_distance)*scale)) + map_size + 2;
    let startY = endY - map_size / scale - 4;

    //nettoyage de la map
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    canvas.width = map_size * pixel_size + (map_size - 2) * pixel_distance + pixel_size / 2 + pixel_distance;
    canvas.height = map_size * pixel_size + (map_size - 2) * pixel_distance + pixel_size;

    if(translatePos.x <= 0 && translatePos.y <= 0 && (canvas.width * scale + translatePos.x) >= canvas.width && (canvas.height * scale + translatePos.y) >= canvas.height){
        ctx.translate(translatePos.x, translatePos.y);
    }
    
    ctx.scale(scale, scale);

    drawBackground();

    mapTiles.forEach(function(value, key, map){
        let tile = value;
        //let tile = mapTiles.get(key);
        if(tile.x > startX && tile.x < (startX+lengthX) && tile.y > startY && tile.y < endY){
            tile.draw(canvas, ctx);
			i=i+1;
        }
        
    });
	console.log("Drawn "+i + " cases")
}

function drawBackground(){
    //map en gris_brouillard
    ctx.fillStyle = gris_brouillard;
    ctx.fillRect((0), (0), canvas.width, canvas.height);
}
/*
function drawStar(ctx, centerX,centerY,arms,innerRadius,outerRadius,startAngle,fillStyle,strokeStyle,lineWidth) {
    startAngle = startAngle * Math.PI / 180  || 0;
    var step = Math.PI / arms,
        angle = startAngle
        ,hyp,x,y;
    ctx.strokeStyle = strokeStyle;
    ctx.fillStyle = fillStyle;
    ctx.lineWidth = lineWidth;
    ctx.beginPath();
    for (var i =0,len= 2 * arms; i <len; i++) {
      hyp = i & 1 ? innerRadius : outerRadius;
      x = centerX + Math.cos(angle) * hyp;
      y = centerY +Math.sin(angle) * hyp;
      angle+=step;
      ctx.lineTo(x, y);
    }
    ctx.closePath();
    fillStyle && ctx.fill();
    strokeStyle && ctx.stroke();
  }
*/
function get_map(){
    $.ajax({
        method: "GET",
        url: "functions_carte.php",
        data:{
            "function":"get_map"
        },
        success: function(data){
             
            //affichage
            let tiles = [];
            Object.keys(data).forEach(function(k){

                let tile = new Case(data[k]);
                tiles.push(tile);


                tile.draw(canvas, ctx);
                
            });
            mapTiles = toMap(tiles, toKey);
            currentMap = mapTiles;
            drawMap(currentMap);
        },
        error: function(error_data){
            console.log("Endpoint request error");
            console.log(error_data)
        }
    });
}

function get_historique_map(historique_date){

    if(histoMaps.has(historique_date)){
        drawMap(histoMaps.get(historique_date));
    }else{
        $.ajax({
            method: "POST",
            url: "functions_carte.php",
            data:{
                "function":"get_historique",
                "date":historique_date
            },
            success:function(data){
                 
                //affichage
                let tiles = [];
                Object.keys(data).forEach(function(k){

                    let tile = new Case(data[k]);
                    tiles.push(tile);


                    tile.draw(canvas, ctx);
                    
                });
                let histoMapTiles = toMap(tiles, toKey);

                histoMaps.set(historique_date, histoMapTiles);
                currentMap = histoMapTiles;
                drawMap(currentMap);
            },
            error:function(error_data){
                console.log("Endpoint request error");
                console.log(error_data)
            }
        });
    }
    
}




function adjustPixelSizeOnScreenSize(){
    let width = window.innerWidth;
    if(width<800){
        return 5;
    }else if(width<1100){
        return 5;
    }else if(width<1600){
        return 5;
    }
    return 5;   
}

//Fonctions qui permettent de transformer en map la liste de cases pour une recherche plus rapide
function toKey(tile){
    return `${tile.x}-${tile.y}`;
}

function toMap(list, toKey){
    const keyValuePairs = list.map(item => [toKey(item), item]);
    return new Map(keyValuePairs);
}

function removeEvent(e) {
    // Remove this event from the target's cache
    const index = evCache.findIndex((cachedEv) => cachedEv.pointerId === e.pointerId);
    evCache.splice(index, 1);
}

function adjustedTranslatePos(newTranslatePosX, newTranslatePosY){
    
    if(newTranslatePosX > 0){
        newTranslatePosX = 0;
    }

    if(newTranslatePosY > 0){
        newTranslatePosY = 0;
    }
    if((canvas.width * scale + newTranslatePosX) < canvas.width){
        newTranslatePosX =  canvas.width - canvas.width * scale;
    }
    if((canvas.height * scale + newTranslatePosY) < canvas.height){
        newTranslatePosY =  canvas.height - canvas.height * scale;
    }
    if(canvas.width == canvas.width * scale){
        newTranslatePosX = 0;
    }
    if(canvas.height == canvas.height * scale){
        newTranslatePosY = 0;
    }
    
    translatePos.x = newTranslatePosX;
    translatePos.y = newTranslatePosY;
}

function getTilePointerPos(canvas,  e) {
    
    let difX = canvas.offsetWidth / canvas.width;
    let difY = canvas.offsetHeight / canvas.height;

    var x = (e.offsetX/ difX) - translatePos.x;
    var y = (e.offsetY/ difY) - translatePos.y;

   // console.log(x, x/((pixel_size + pixel_distance)*scale), difX,  e.offsetX, scale, translatePos.x, (pixel_size + pixel_distance), canvas.offsetWidth, canvas.width);
    var pos = [];
    pos['x'] 	= Math.floor(x/((pixel_size + pixel_distance)*scale));
    pos['y'] 	= Math.floor((canvas.height*scale-y)/((pixel_size + pixel_distance)*scale));
    pos['xy'] 	= pos['x'] +'-'+ pos['y'];
    
    var tile = currentMap.get(pos['xy']);
    
    return tile;
}

