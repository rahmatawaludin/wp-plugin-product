jQuery(document).ready(function($) {
	/* Dynamic select and link to post */
	/* use .on to select dynamic added id begin with 'selectcat' */
	$(document).on('change', "[id^=selectcat]", function() {	
		var cat_id = $(this).children(':selected').attr('value');
		// console.log('selected form : '+this.id);
		var form_id = this.id;

		/* Ajax call to server by selected value id */
		var data = {
			action: 'product_get_category',
			cat_id: cat_id
		};		

		jQuery.post(categoryAjax.ajaxurl, data, function(response) {
			$('#'+form_id).nextAll("[id^=selectcat]").remove(); // remove all select after this 
			$('#'+form_id).nextAll("[id=content_product]").remove(); // remove all link of content after this 
			$('#product_container').replaceWith('<div id="product_container"></id>'); // delete content from #product_container
			$(response).insertAfter($('#'+form_id));
		});
	});

	/* Display content by ajax */
	$(document).on('click', ".link_product", function() {	
		var product_id = $(this).attr('value');
		var data = {
			action: 'product_get_content',
			product_id: product_id
		};	
		jQuery.post(categoryAjax.ajaxurl, data, function(response) {
			$('#product_container').replaceWith('<div id="product_container">'+response+'</id>');
		});
	});
});