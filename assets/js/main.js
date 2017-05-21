(function($){
	$(document).ready(function(){

		console.log("Initiating wpmodal frontend scripts.");

		// Generic modal option variable comes from a context provided by wp_localize_script
		if(context.generic_modal) {

			console.log("Generic modal mode.");

			$('[data-toggle="modal"]').click(function(){
				console.log("Clicked a data tooggle modal!");
				var modal_id = $( this ).data('target');
				console.log("Modal id is: " + modal_id);
				$(modal_id).modal({
  					fadeDuration: 100
				});

			});

		}


	});

})(jQuery);