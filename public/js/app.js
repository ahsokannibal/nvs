document.addEventListener('DOMContentLoaded', function() {
    console.log( "DOM chargé" );
	
	$('#reload_captcha').click(function(){
		$("#captcha").attr("src", "captcha.php?"+(new Date()).getTime());
	})

	//aperçu d'une image sélectionnée dans un formulaire
	var imgInput = document.getElementById('imgUpload');
	var imgPreview150 = document.getElementById('imgPreview150');
	var imgPreview40 = document.getElementById('imgPreview40');
	var imgPreviewName = document.getElementById('imgPreviewName');

	imgInput.addEventListener('change', function(event) {
		clearImgPreview();

		var file = event.target.files[0];
		if (file) {
			imgPreviewName.textContent = file.name;
			var reader = new FileReader();

			reader.onload = function(e) {
				var img150 = document.createElement('img');
				img150.src = e.target.result;
				img150.style.maxWidth = '150px';
				img150.style.maxHeight = '150px';
				imgPreview150.appendChild(img150);
				
				var img40 = document.createElement('img');
				img40.src = e.target.result;
				img40.style.maxWidth = '40px';
				img40.style.maxHeight = '40px';
				imgPreview40.appendChild(img40);
			}

			reader.readAsDataURL(file);
		}
	});

	function clearImgPreview() {
		imgPreview150.innerHTML = '';
		imgPreview40.innerHTML = '';
	}
});
