jQuery(document).ready(function($) {
	// console.log($("[id^=selectcat]").length); // check jumlah ID yang terselect

	$(document).on('change', "[id^=selectcat]", function() {	
		var cat_id = $(this).children(':selected').attr('value');
		console.log('selected form : '+this.id);
		var form_id = this.id;

		/* Ajax call to server by selected value id */
		var data = {
			action: 'product_get_category',
			cat_id: cat_id
		};		

		jQuery.post(categoryAjax.ajaxurl, data, function(response) {
			// console.log(response);
			$('#'+form_id).nextAll("[id^=selectcat]").remove();
			$('#'+form_id).nextAll("[id=content_product]").remove();
			$(''+response+'').insertAfter($('#'+form_id));
		});
	});
});