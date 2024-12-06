jQuery(document).ready(function($) {
	var school_commission, sc_commission, sch_commission, child_commission, class_commission, admin_commission, admin_class_commission;

	// Datatable for showing school commission for school login
	if($('#table-school-commission').length) 
	{
		school_commission = $('#table-school-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_school_commission_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
				},
				"dataSrc": function(json) {
					var totalOrder      = json.total_order_total;
					var totalCommission = json.total_commission;

					$('span.total-sale').find('span.total').text(totalOrder);
					$('span.total-commission').find('span.commission').text(totalCommission);

					return json.data;
				}
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

	// Datatable for showing class related commission for school login
	if($('#table-class-commission').length) 
	{
		sc_commission = $('#table-class-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_sc_commission_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
				},
				"dataSrc": function(json) {
					var totalOrder      = json.total_order_total;
					var totalCommission = json.total_commission;

					$('span.total-sale').find('span.total').text(totalOrder);
					$('span.total-commission').find('span.commission').text(totalCommission);

					return json.data;
				}
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
			}
		});
	}

	// Get URL parameters
	var urlParams = new URLSearchParams(window.location.search);
	var class_id  = urlParams.get('class');
	var school_id = urlParams.get('school_id');
	var page      = urlParams.get('page');

	// Datatable for showing child related commission for school login
	if($('#table-child-commission').length) 
	{
		sch_commission = $('#table-child-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_schild_commission_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.class_id   = class_id;
				},
				"dataSrc": function(json) {
					var totalOrder      = json.total_order_total;
					var totalCommission = json.total_commission;

					$('span.total-sale').find('span.total').text(totalOrder);
					$('span.total-commission').find('span.commission').text(totalCommission);

					var total_school_order      = json.total_school_order;
					var total_school_commission = json.total_school_commission;
					var total_website_order      = json.total_website_order;
					var total_website_commission = json.total_website_commission;

					$('.total-school-sale').find('abbr').text(total_school_order);
					$('.total-school-commission').find('abbr').text(total_school_commission);
					$('.total-website-sale').find('abbr').text(total_website_order);
					$('.total-website-commission').find('abbr').text(total_website_commission);

					return json.data;
				}
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
			}
		});
	}
	
	// Datatable for showing child commission for child login
	if($('#table-ch-commission').length) 
	{
		child_commission = $('#table-ch-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_child_commission_detail';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
				}
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
			"select": false
		});
	}

	// Datatable for showing class commission for class login
	if($('#table-class-commission-cs').length) 
	{
		class_commission = $('#table-class-commission-cs').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_class_commission_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
				}
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
			}
		});
	}

	// Datatable to show commission detail recevied for school and website
	if($('#table-admin-commission').length) 
	{
		admin_commission = $('#table-admin-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_commission_details';
					d.start_date = $('input#start-date').val();
					d.end_date   = $('input#end-date').val();
					d.type       = $('select#commission-type').find('option:selected').val();
					d.school_id  = $('select.school-commission').find('option:selected').val();
				},
				"dataSrc": function(json) {
					var total_school_order      = json.total_school_order;
					var total_school_commission = json.total_school_commission;
					var total_website_order      = json.total_website_order;
					var total_website_commission = json.total_website_commission;

					$('.total-school-sale').find('abbr').text(total_school_order);
					$('.total-school-commission').find('abbr').text(total_school_commission);
					$('.total-website-sale').find('abbr').text(total_website_order);
					$('.total-website-commission').find('abbr').text(total_website_commission);

					return json.data;
				}
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

	// Datatable to show class commission detail for admin
	if($('#table-admin-class-commission').length) 
	{
		admin_class_commission = $('#table-admin-class-commission').dataTable({
			processing: true,
			serverSide: true,
			searching: false,
			"ordering": false,
			"ajax": {
				"url": commission_ajax_object.ajax_url,
				"type": "POST",
				"data": function(d) {
					d.action     = 'get_sc_commission_details';
					d.start_date = $('input#start-date').val();
					d.school_id  = school_id;
					d.page       = page;
				},
				"dataSrc": function(json) {
					var total_school_order      = json.total_school_order;
					var total_school_commission = json.total_school_commission;
					var total_website_order      = json.total_website_order;
					var total_website_commission = json.total_website_commission;

					$('.total-school-sale').find('abbr').text(total_school_order);
					$('.total-school-commission').find('abbr').text(total_school_commission);
					$('.total-website-sale').find('abbr').text(total_website_order);
					$('.total-website-commission').find('abbr').text(total_website_commission);

					return json.data;
				}
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

	// Bootstrap datepicker
	$(document).on('click', '#sort-date-range', function() {
		if( school_commission )
			school_commission.fnDraw();

		if( sc_commission )
			sc_commission.fnDraw();

		if( sch_commission )
			sch_commission.fnDraw();

		if( child_commission )
			child_commission.fnDraw();

		if( class_commission )
			class_commission.fnDraw();

		if( admin_commission )
			admin_commission.fnDraw();

		if( admin_class_commission )
			admin_class_commission.fnDraw();
	})

	// On change of commission type hide and show school dropdown
	$(document).on('change', '#commission-type', function() {
		var type = $(this).find('option:selected').val();
		
		if( type == 'school' )
			$('.school-commission').show();
		else
			$('.school-commission').hide();
	})

	// On change of start date add validation for end date
	if($('input#start-date').length) 
	{
		$("input#start-date").datepicker("destroy");
		$("input#end-date").datepicker("destroy");
		
		$('input#start-date').datepicker({
			autoclose: true,
			dateFormat: 'yy-mm-dd',
			language: 'sv'
		}).on('changeDate', function(e) {
			var selectedStartDate = e.date;
			var minEndDate = new Date(selectedStartDate);
			minEndDate.setDate(minEndDate.getDate() + 1);

			$( "input#end-date" ).datepicker('setStartDate', minEndDate);
		});

		$( "input#end-date" ).datepicker({
			autoclose: true,
			dateFormat: 'yy-mm-dd',
			language: 'sv'
		}).on('changeDate', function (e) {
			var selectedDate = e.date;
			$("input#start-date").datepicker('setEndDate', selectedDate);
		});
	}

});

