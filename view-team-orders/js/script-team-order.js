jQuery(document).ready(function($) {

	var school_child_order, class_child_order, seller_orders;

	// Datatable for child order listing
	if($('#child-orders-list').length) 
	{
		school_child_order = $('#child-orders-list').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ajax": {
				"url": team_order_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_order_list_child';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.status     = $('select#status-filter').find('option:selected').val();
				}
			},
			"order": [
				[ 0, "desc" ]
			],
			'columnDefs': [{
					'targets': [0, 1, 2, 3, 4, 5, 6], 'orderable': false,
				},
			],
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
			}
		});
	}

	if($('#class-child-orders-list').length) 
	{
		class_child_order = $('#class-child-orders-list').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ajax": {
				"url": class_custom_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_class_seller_order_list';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.status     = $('select#status-filter').find('option:selected').val();
				}
			},
			"order": [
				[ 0, "desc" ]
			],
			'columnDefs': [{
					'targets': [0, 1, 2, 3, 4, 5, 6], 'orderable': false,
				},
			],
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
			}
		});
	}

	if($('#seller-orders-list').length) 
	{
		seller_orders = $('#seller-orders-list').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ajax": {
				"url": class_custom_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_seller_order_list';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.status     = $('select#status-filter').find('option:selected').val();
				}
			},
			"order": [
				[ 0, "desc" ]
			],
			'columnDefs': [{
					'targets': [0, 1, 2, 3, 4, 5], 'orderable': false,
				},
			],
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
			}
		});
	}

	$(document).on('click', '#sort-date-range', function() {
		if( school_child_order )
			school_child_order.fnDraw();

		if( class_child_order )
			class_child_order.fnDraw();

		if( seller_orders )
			seller_orders.fnDraw();
	})

})

