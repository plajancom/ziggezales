jQuery(document).ready(function($) {
	var pick_orders;

	// Datatable for showing school commission for school login
	if($('#table-pick-order').length) {
		pick_orders = $('#table-pick-order').DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			ordering: false,
			pageLength: 50,
			ajax: {
				url: pick_order_ajax_object.ajax_url,
				type: "POST",
				data: function(d) {
					d.action = 'get_pick_order_details';
					d.start_date = $('input#start-date').val();
					d.end_date = $('input#end-date').val();
					d.school_id = $('select.pick-school-dropdown').find('option:selected').val();
					d.class_id = $('select.pick-class-dropdown').find('option:selected').val();
					d.seller_id = $('select.pick-seller-dropdown').find('option:selected').val();
				},
			},
			columnDefs : [
				{ 'visible': false, 'targets': [5, 6] }
			],
			language: {
				emptyTable: "Inga data tillgängliga i tabellen",
				lengthMenu: "Visa _MENU_ rader",
				info: "Visar _START_ till _END_ av _TOTAL_ resultat",
				infoEmpty: "Visar 0 till 0 av 0 resultat",
				infoFiltered: "(filtrerade från _MAX_ totalt antal resultat)",
				paginate: {
					first: "Första",
					previous: "Föregående",
					next: "Nästa",
					last: "Sista"
				},
				search: "Sök:"
			},
		});
	}

	// filter pick order records on start/end date and school
	$(document).on('click', 'input#filter-pick-order', function() {
		var seller = $('.pick-seller-dropdown').find(":selected").val();
		if(seller != '') {
			// $('.no-column').show();
		}
		if( pick_orders ) {
        	pick_orders.draw();
			if(seller != '') {
				pick_orders.column( 5 ).visible( true );
				pick_orders.column( 6 ).visible( true );
			} else {
				pick_orders.column( 5 ).visible( false );
				pick_orders.column( 6 ).visible( false );
			}
		}
	});

    $(document).on('change', '.pick-school-dropdown', function() {
        var schoolID = $(this).val();
        if(schoolID == '') {
            $('.pick-class-dropdown').empty();
            $('.pick-class-dropdown').html('<option value="">Välj alternativ</option>');
            $('.pick-seller-dropdown').empty();
            $('.pick-seller-dropdown').html('<option value="">Välj Säljare</option>');
        }
        $.ajax({
            url: pick_order_ajax_object.ajax_url,
            type: "GET",
            data: {action: 'get_pick_school_class', school_id: schoolID},
            success: function(response) {
                $('.pick-class-dropdown').html(response);
            }
        });
    });
	$(document).on('change', '.pick-class-dropdown', function() {
        var classID = $(this).val();
        if(classID == '') {
            $('.pick-seller-dropdown').empty();
            $('.pick-seller-dropdown').html('<option value="">Välj Säljare</option>');
        }
        $.ajax({
            url: pick_order_ajax_object.ajax_url,
            type: "GET",
            data: {action: 'get_pick_class_seller', class_id: classID},
            success: function(response) {
                $('.pick-seller-dropdown').html(response);
            }
        });
    });

	// $('#export-pick-order').on('click', function() {
	// 	d.start_date = $('input#start-date').val();
	// 	d.end_date = $('input#end-date').val();
	// 	d.school_id = $('select.pick-school-dropdown').find('option:selected').val();
	// 	d.class_id = $('select.pick-class-dropdown').find('option:selected').val();
	// 	d.seller_id = $('select.pick-seller-dropdown').find('option:selected').val();
	// });
});