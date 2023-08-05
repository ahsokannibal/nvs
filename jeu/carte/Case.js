
class Case{
    couleur;
    couleur_brouillard;
    constructor(options={}){
        Object.assign(this, options);
        this.setCouleur();
    }

    setTooltipContent(){
       $(canvas).attr('title', this.x + " - " + this.y).css('font-weight', 'bold');;
    }

    draw(canvas, ctx){
        let me = this;
        //this.cleanTile(ctx);

        this.setCouleur();
        if(batiments_checkbox.checked && this.batiment != undefined){
            //on utilise l'image
            if(this.batiment.nom == 'Fort' || this.batiment.nom == 'Fortin' || this.batiment.nom == 'Gare' || this.batiment.nom == 'Hopital' || this.batiment.nom == 'Pont'|| this.batiment.nom == 'Train' || this.batiment.nom == 'Pénitencier' || this.batiment.nom == 'Point stratégique'){
                
                
                if(this.batiment.nom == 'Point stratégique'){
                    
                    if(this.batiment.camp == 1){
                        this.couleur = couleur_bat_clan1;
                    }else if(this.batiment.camp == 2){
                        this.couleur = couleur_bat_clan2;
                    }else {
                        this.couleur = noir;
                    }
                    ctx.strokeStyle = this.couleur;
                    ctx.lineWidth = pixel_size/2;
                    ctx.strokeRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
                }
                this.drawImageIfLoaded(canvas, '../../images_perso/'+this.batiment.image);
               
            }else{

                if(scale===maxScale){
                    this.drawImageIfLoaded(canvas, '../../fond_carte/'+this.f, function(canvas, ctx, me){me.drawImageIfLoaded(canvas, '../../images_perso/'+me.batiment.image);});
                }else{
                    //on utilise une couleur
                    if(this.batiment.camp == 1){
                        this.couleur = couleur_bat_clan1;
                    }else if(this.batiment.camp == 2){
                        this.couleur = couleur_bat_clan2;
                    }else {
                        this.couleur = couleur_bat_neutre;
                    }
                    this.drawFondCase(ctx);
                }
            }
            
            if(this.joueur != undefined && compagnie_checkbox.checked){
                if (this.joueur.some(e => e.compagnie != undefined)) {
                    /* this.joueur contains the element we're looking for */
                    ctx.strokeStyle = blanc;
                    ctx.lineWidth = pixel_size/2;
                    ctx.strokeRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
                }
            }
            if(this.joueur != undefined && bataillon_checkbox.checked){
                if (this.joueur.some(e => e.bataillon != undefined)) {
                    /* this.joueur contains the element we're looking for */
                    ctx.strokeStyle = 'orange';
                    ctx.lineWidth = pixel_size/2;
                    ctx.strokeRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
                }
            }
        }else if(this.joueur != undefined && !Array.isArray(this.joueur) && joueurs_checkbox.checked){
            if(this.joueur.camp == 1){
                this.couleur = couleur_perso_clan1;
            }else if(this.joueur.camp == 2){
                this.couleur = couleur_perso_clan2;
            }else {
                this.couleur = couleur_perso_defaut;
            }
            if(compagnie_checkbox.checked && this.joueur.compagnie != undefined){
                
                ctx.strokeStyle = blanc;
                ctx.lineWidth = pixel_size/2;
                ctx.strokeRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
            }
            if(bataillon_checkbox.checked && this.joueur.bataillon != undefined){
                ctx.strokeStyle = 'orange';
                ctx.lineWidth = pixel_size/2;
                ctx.strokeRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
            }

            if(scale === maxScale){
                this.drawImageIfLoaded(canvas, '../../images_perso/'+this.joueur.image, this.drawMatricule);
               
                
                
            }else{
                ctx.fillStyle = this.couleur;
                ctx.lineWidth = pixel_size/2;
                ctx.fillRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
            }
            
            
        
            /*var img = new Image(pixel_size, pixel_size); //  Constructeur HTML5
            img.src = '../../images_perso/'+this.joueur.image;
            img.onload = function(){
                ctx.drawImage(img, me.getX(canvas), me.getY(canvas), pixel_size, pixel_size);
            };*/
            
            /*var img = new Image(pixel_size, pixel_size); //  Constructeur HTML5
            img.src = '../../images_perso/'+this.joueur.image;
            let x=this.x;
            let y=this.y;
            img.onload = function(){
                ctx.drawImage(img, this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
            };*/
            
        }else if(this.pnj != undefined){
            if(scale === maxScale){
                this.drawFondCase(ctx);
                this.drawImageIfLoaded(canvas, '../../fond_carte/'+this.f, function(canvas, ctx, me){me.drawImageIfLoaded(canvas, '../../images/pnj/'+me.pnj.image);});
                
            }else{
                this.couleur = noir;
                this.drawFondCase(ctx);
            }
            
        }else if(this.brouillard != undefined && this.brouillard.valeur == 1 && brouillard_checkbox.checked){
            if(scale === maxScale){
                this.drawImageIfLoaded(canvas, '../../fond_carte/'+this.f, this.drawBrouillardOver);
            }else{
                if(topographie.checked){
                    ctx.fillStyle = this.couleur_brouillard;
                }else{
                    ctx.fillStyle = couleur_brouillard_plaine;
                }
                ctx.fillRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
            }

        }else if(topographie_checkbox.checked){
            if(scale === maxScale){
                this.drawImageIfLoaded(canvas, '../../fond_carte/'+this.f);
                
            }else{
                this.drawFondCase(ctx);
            }
            
        }/*else if(contraintes_batiments_checkbox.checked){
            
        }*/else{
            this.couleur = gris_brouillard;
            this.drawFondCase(ctx);
        }

    }

    drawMatricule(canvas, ctx, me){
        ctx.font = "2px Arial";
        ctx.fillStyle= noir;
        ctx.textAlign = "center";
        ctx.fillText(me.joueur.id, me.getX(canvas) + pixel_size / 2, me.getY(canvas) + pixel_size - 0.5);
    }

    drawBrouillardOver(canvas, ctx, me){
        ctx.fillStyle = couleur_brouillard_image;
        ctx.fillRect(me.getX(canvas), me.getY(canvas), pixel_size, pixel_size);
    }

    drawImageIfLoaded(canvas, src, onImageDrawned = null){
        let me = this;
        var img = new Image(pixel_size, pixel_size); //  Constructeur HTML5
        img.src = src;
        let result = this.saveImageToCache(img);
        if(result == 0 || !result.isLoaded){
            img.onload = function(){
                img.isLoaded = true;
                ctx.drawImage(img, me.getX(canvas), me.getY(canvas), pixel_size, pixel_size);
                if(onImageDrawned != null){
                    onImageDrawned(canvas, ctx, me);
                }
            }
        }else{
            ctx.drawImage(result, me.getX(canvas), me.getY(canvas), pixel_size, pixel_size);
            if(onImageDrawned != null){
                onImageDrawned(canvas, ctx, me);
            }
        }
    }

    saveImageToCache(img){
        
        let result = images.find(x => x.src == img.src);
        if(result == undefined){
            images.push(img);
            return 0;
        }else{
            return result;
        }
    }

    drawFondCase(ctx){
        ctx.fillStyle = this.couleur;
        ctx.fillRect(this.getX(canvas), this.getY(canvas), pixel_size, pixel_size);
    }

    cleanTile(ctx){
        ctx.clearRect(this.getX(canvas)+0.5, this.getY(canvas)+0.5, pixel_size-1, pixel_size-1);
        ctx.fillStyle = gris_brouillard;
        ctx.fillRect(this.getX(canvas), this.getY(canvas), pixel_size+pixel_distance, pixel_size+pixel_distance);
    }

    drawMouseOver(canvas, ctx){
        ctx.fillStyle = blanc;
        //la surcouche ne couvre pas entièrement la case pour éviter un effet "ghost" sur les cases survolées
        ctx.fillRect(this.getX(canvas)+0.4, this.getY(canvas)+0.4, pixel_size-0.8, pixel_size-0.8);
    }

    getX(canvas){
        return (this.x*(pixel_size + pixel_distance)+pixel_distance);
    }

    getY(canvas){
        return (canvas.width-this.y*(pixel_size + pixel_distance)-pixel_size);
    }

    setCouleur(){
        if (this.f == '3.gif') {
			// Montagne
            this.couleur             = couleur_montagne;
            this.couleur_brouillard  = couleur_brouillard_montagne;
		}
		else if (this.f == '2.gif') {
			// Colinne
            this.couleur             = couleur_colline;
			this.couleur_brouillard  = couleur_brouillard_colinne;
		}
		else if (this.f == '4.gif') {
			// Desert
            this.couleur             = couleur_desert;
			this.couleur_brouillard  = couleur_brouillard_desert;
		}
		else if (this.f == '6.gif') {
			// marécage
            this.couleur             = couleur_marecage;
			this.couleur_brouillard  = couleur_brouillard_marecage;
		}
		else if (this.f == '7.gif') {
			// Foret
            this.couleur             = couleur_foret;
			this.couleur_brouillard  = couleur_brouillard_foret;
		}
        else if (this.f == 'b5b.png' || this.f == 'b5r.png' || this.f == 'b5g.png') {
			// pont
			this.couleur             = couleur_bat_neutre;
            this.couleur_brouillard  = couleur_brouillard_eau;
		}
		else if (this.f == '8.gif') {
			// eau 
			this.couleur             = couleur_eau;
            this.couleur_brouillard  = couleur_brouillard_eau;
		}else if(this.f == '9.gif'){
            this.couleur             = couleur_eau_p;
            this.couleur_brouillard  = couleur_brouillard_eau;
        }else if(this.f.includes('rail')){
            this.couleur             = couleur_rail;
            this.couleur_brouillard  = couleur_brouillard_plaine;
        }else {
			// plaine et autres
			this.couleur             = couleur_plaine;
            this.couleur_brouillard  = couleur_brouillard_plaine;
		}
    }
}
