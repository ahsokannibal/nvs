$( document ).ready(function() {
    console.log( "DOM chargé" );
	
	$('#reload_captcha').click(function(){
		$("#captcha").attr("src", "captcha.php?"+(new Date()).getTime());
	})
});