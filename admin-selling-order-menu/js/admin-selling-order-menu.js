jQuery(document).ready(function($) {
	var selling_orders;

	// Datatable for showing school commission for school login
	if($('#table-selling-order').length) 
	{
		selling_orders = $('#table-selling-order').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			pageLength: 50,
			"ajax": {
				"url": selling_order_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_selling_order_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.school_id  = $('select.school-dropdown').find('option:selected').val();
				},
			},
			"language": {
				"emptyTable": "Inga data tillgängliga i tabellen",
				"lengthMenu": "Visa _MENU_ rader",
				"info": "Visar _START_ till _END_ av _TOTAL_ resultat",
				"infoEmpty": "Visar 0 till 0 av 0 resultat",
				"infoFiltered": "(filtrerade från _MAX_ totalt antal resultat)",
				"paginate": {
					"first": "Första",
					"previous": "Föregående",
					"next": "Nästa",
					"last": "Sista"
				},
				"search": "Sök:"
			},
		});
	}

	// filter selling order records on start/end date and school
	$(document).on('click', 'input#filter-selling-order', function() {
		if( selling_orders )
			selling_orders.fnDraw();
	})
});;