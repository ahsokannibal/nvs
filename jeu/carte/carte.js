		
function addMouseChecker(imgId, inputId, valueToShow) {
    
    imgId 	= document.getElementById(imgId);
    inputId = document.getElementById(inputId);
        
    if (imgId.addEventListener) {
        imgId.addEventListener('mousemove', function(e){checkMousePos(imgId, inputId, valueToShow, e);}, false);
    } else if (imgId.attachEvent) {
        imgId.attachEvent('onclick', function(e){checkMousePos(imgId, inputId, valueToShow, e);});
    }
}

function checkMousePos(imgId, inputId, valueToShow, e) {
    
    var ih=imgId.naturalHeight;
    
    var pos = [];
    
    pos['x'] 	= Math.floor((e.pageX - imgId.offsetLeft) / 3);
    pos['y'] 	= Math.floor((ih - (e.pageY - imgId.offsetTop)) / 3);
    pos['xy'] 	= pos['x'] +','+ pos['y'];
    
    inputId.value = pos[valueToShow];
}