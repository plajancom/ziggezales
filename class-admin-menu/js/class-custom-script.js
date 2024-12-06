jQuery(document).ready(function($) {

	$('label#email_id_error').hide();

	// Get URL parameters
	var urlParams  = new URLSearchParams(window.location.search);
	var user_id    = urlParams.get('user_id');
	var userRegex  = /^[a-z0-9\-_]+$/;
	var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

	$.validator.addMethod("usernameRegex", function(value, element) {
		return this.optional(element) || userRegex.test(value);
	}, "Användarnamnet får endast bestå av små bokstäver, bindestreck och understreck.");

	$.validator.addMethod("emailRegex", function(value, element) {
		return this.optional(element) || emailRegex.test(value);
	}, "Vänligen ange en giltig e-postadress.");

	$.validator.addMethod("noSpace", function (value, element) {
		return value.trim() !== ""; 
	}, "Vänligen ange rätt namn.");
	
	if($('#child-class-listing').length) 
	{
		$('#child-class-listing').dataTable({
			processing: true,
			serverSide: true,
			searching: true,
			"ajax": {
				"url": class_custom_ajax_object.ajax_url,
				"type": "POST",
				"data": {
					action: 'class_get_child_listing'
				}
			},
			"order": [
				[ 0, "desc" ]
			],
			'columnDefs': [{
					'targets': [4], 'orderable': false,
				},
				{ 
					'targets': [0, 1], 'visible': false, 
				}
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

	// // Rules for form validation
	var validationRules = {
		username: {
			required: true,
		},
		email: {
			required: true,
			emailRegex: true,
		},
		first_name: {
			required: true,
			noSpace: true,
		},
		role: {
			required: true,
		},
		password: {
			required: true,
		},
		confirm_password: {
			required: true,
			equalTo: "#password",
		}
	};

	// Error message for fields 
	var validationMessages = {
		username: {
			required: "Vänligen ange användarnamn.",
		},
		email: {
			required: "Vänligen ange en e-postadress.",
		},
		first_name: {
			required: "Vänligen ange förnamn."
		},
		role: {
			required: "Välj roll."
		},
		password: {
			required: "Ange lösenord Tack."
		},
		confirm_password: {
			required: "Vänligen ange bekräfta lösenord.",
			equalTo: "Lösenordet matchar inte.",
		}
	};

	if (user_id) 
	{
		validationRules.password = {};
		delete validationMessages.password;

		validationRules.confirm_password = {
			equalTo: "#password",
		};
	}

	// Validate child form
	$('#class-add-edit-child').validate({
		rules: validationRules,
		messages: validationMessages,
	});

	$('input#email').on('blur', function() {
		$('label#email_id_error').hide();
		$('label#email_id_error').text('');

		var email = $(this).val();
		$('input#username').val(email);

		if (email.match(emailRegex)) {
			$.ajax({
				url: class_custom_ajax_object.ajax_url,
				type: "post",
				data: {
					user_email: email,
					action: 'check_user_email',
					user_id: user_id,
				},
				success: function( response )
				{
					response = $.parseJSON(response);

					if( response == "true" )
					{
						$('button#submit-class').prop('disabled', false);
						return true;
					}
					else
					{
						$('button#submit-class').prop('disabled', true);
						$('label#email_id_error').show();
						$('label#email_id_error').text(response);
					}
				}
			})
		}
	})

	$(document).on('click', 'a.remove-child-class', function() {
		var user_id = $(this).attr('data-user_id');
		var role    = $(this).attr('data-role');

		jQuery('#class-child-delete-confirmation-modal').modal('show');
		$('#class-child-delete-confirmation-modal').find('a').attr('href', 'javascript:void(0);');
		$('#class-child-delete-confirmation-modal').find('a').attr('data-user_id', user_id);
		$('#class-child-delete-confirmation-modal').find('a').attr('data-role', role);
	})

	$(document).on('click', '#class-child-delete-confirmation-modal button', function() {
		jQuery('#class-child-delete-confirmation-modal').modal('hide');
	})

	$(document).on('click', 'div#class-child-delete-confirmation-modal .modal-footer a', function() {
		var user_id = $(this).attr('data-user_id');
		var role    = $(this).attr('data-role');

		$.ajax({
			url: class_custom_ajax_object.ajax_url,
			type: "post",
			data: {
				action: 'class_remove_child_users',
				user_id: user_id,
				role: role,
			},
			success: function( response ) {
				location.reload();
			}
		})
	})

	$(document).on('click', 'a#cancel-class', function() {
		var link = $(this).attr('href');

		$.ajax({
			url: class_custom_ajax_object.ajax_url,
			type: "post",
			data: {
				action: 'reset_cookies',
			},
			success: function( response )
			{
				window.location.href = link;
			}
		})

		return false
	})
});

if( jQuery('#copyRegisterButton').length )
{
	document.getElementById("copyRegisterButton").addEventListener("click", function(event) {
		event.preventDefault();
		
		var linkToCopy = document.getElementById("registerLink");
		var linkValue = linkToCopy.getAttribute("href");
		var tempInput = document.createElement("input");
		document.body.appendChild(tempInput);
		tempInput.value = linkValue;
		tempInput.select();

		document.execCommand("copy");
		document.body.removeChild(tempInput);

		alert("Länken har kopierats till urklipp");
	});
}

