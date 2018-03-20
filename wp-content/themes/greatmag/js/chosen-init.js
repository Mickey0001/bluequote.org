
jQuery(function($){
	$(document).on('panelsopen', function(e) {
		$(".chosen-dropdown-5").chosen({
			disable_search_threshold: 10,
			max_selected_options: 5,
		});
		$(".chosen-dropdown-3").chosen({
			disable_search_threshold: 10,
			max_selected_options: 3,
		});
		$(".chosen-dropdown-1").chosen({
			disable_search_threshold: 10,
			max_selected_options: 1,
		});	

		$(".chosen-dropdown").chosen({
			disable_search_threshold: 10,
		});

		$('.chosen-dropdown').chosenSortable();

	});	
});
