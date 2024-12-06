<?php

/*

Plugin Name: Custom Class Menu

Description: Min kontomeny för att lägga till hantering för säljaranvändare för klassinloggning.

Version: 1.0

Author: Mahesh Sharma

*/



define( 'CP_PLUGIN_URL', plugin_dir_url(__FILE__) );

define( 'CP_PLUGIN_PATH', plugin_dir_path(__FILE__) );



// Action hooks and filters

add_action( 'wp_enqueue_scripts', 'class_enqueue_datatable_script' );

add_filter( 'woocommerce_account_menu_items', 'class_manage_child', 40 );

add_action( 'init', 'zz_add_frontend_endpoints_class' );

add_action( 'woocommerce_account_hantera-säljaren_endpoint', 'class_my_account_manage_child' );

add_action( 'woocommerce_account_lägg-till-redigera-säljaren_endpoint', 'class_my_account_add_edit_child' );

add_action( 'wp_ajax_class_get_child_listing', 'class_get_child_listing' );

add_action( 'wp_ajax_nopriv_class_get_child_listing', 'class_get_child_listing' );

add_action( 'wp_ajax_class_remove_child_users', 'class_remove_child_users' );

add_action( 'wp_ajax_nopriv_class_remove_child_users', 'class_remove_child_users' );

add_action( 'wp_ajax_check_user_email', 'check_user_email' );

add_action( 'wp_ajax_nopriv_check_user_email', 'check_user_email' );

add_action( 'wp_ajax_check_user_name_exists', 'check_user_name_exists' );

add_action( 'wp_ajax_nopriv_check_user_name_exists', 'check_user_name_exists' );

add_action( 'wp_ajax_reset_cookies', 'reset_cookies' );

add_action( 'wp_ajax_nopriv_reset_cookies', 'reset_cookies' );





/**

 * @purpose: Enqueue class custom script for class login

 * 

 */

function class_enqueue_datatable_script() 

{

	$random_version = rand(1, 99999);



	wp_enqueue_script('class-custom-script', CP_PLUGIN_URL . 'js/class-custom-script.js', array('jquery'), $random_version, true);

	wp_localize_script('class-custom-script', 'class_custom_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('custom-ajax-nonce')));

}



/**

 * @purpose: Add custom menu for class login to manage seller under class

 * @param  : $menu_links

 * @return : (array) $menu_links - with updated menus list

 * 

 */

function class_manage_child($menu_links)

{

	$roles        = wp_get_current_user()->roles;

	$logout_index = array_search('customer-logout', array_keys($menu_links));



	if( in_array('wc_product_vendors_admin_vendor', $roles) == true )

	{

		if ($logout_index !== false) {

			$menu_links = array_slice($menu_links, 0, $logout_index, true)

				+ array('hantera-säljaren' => 'Hantera säljaren')

				+ array_slice($menu_links, $logout_index, NULL, true);

		} 

		else

			$menu_links['hantera-säljaren'] = 'Hantera säljaren';

	}



	return $menu_links;

}



/**

 * @purpose: Register custom endpoints for my-account page

 * 

 */

function zz_add_frontend_endpoints_class() 

{	

	add_rewrite_endpoint( 'hantera-säljaren', EP_PAGES );

	add_rewrite_endpoint( 'lägg-till-redigera-säljaren', EP_PAGES );

}



/**

 * @purpose: View page for manage seller. Display list of all seller under class login

 * 

 */

function class_my_account_manage_child()

{

	if( isset($_COOKIE['users_added']) && $_COOKIE['users_added'] == '1' )

	{ ?>



		<script type="text/javascript">

			jQuery('.woocommerce-notices-wrapper').append('<div class="woocommerce-message" role="alert">Säljaren skapades framgångsrikt.</div>');

		</script>



	<?php }



	if( isset($_COOKIE['users_updated']) && $_COOKIE['users_updated'] == '1' )

	{ ?>



		<script type="text/javascript">

			jQuery('.woocommerce-notices-wrapper').append('<div class="woocommerce-message" role="alert">Säljaren har uppdaterats.</div>');

		</script>



	<?php }



	if( isset($_COOKIE['user_deleted']) && $_COOKIE['user_deleted'] == '1' )

	{ ?>



		<script type="text/javascript">

			jQuery('.woocommerce-notices-wrapper').append('<div class="woocommerce-message" role="alert">Säljaren har raderats.</div>');

		</script>



	<?php }



	$register_seller_url = get_permalink('2988') . '?param=' . base64_encode(get_current_user_id());



	$output = '<div class="wrap" style="position: relative;">



		<div class="row">

			<div class="col-md-8">

				<h3> Hantera säljaren </h3>

			</div>

			<div class="col-md-4">

				<div class="add-class">

					<a target="_blank" id="registerLink" href="' . $register_seller_url . '" class="button-primary">Registrera säljare &nbsp;&nbsp;<span style="z-index: 9; position: relative;" class="copy-link" id="copyRegisterButton"><i style="font-size: 24px;" class="fa-solid fa-copy"></i></span> </a> &emsp;

					<a href="' . wc_get_account_endpoint_url( 'lägg-till-redigera-säljaren' ) . '" class="button-primary">Lägg till ny säljare</a>

				</div>

			</div>

		</div>



		<div class="manage-child-listing">

			<table class="table table-bordered" id="child-class-listing">

				<thead>

					<tr>

						<th>Användar ID</th>

						<th>Användarnamn</th>

						<th>Namn</th>

						<th>E-postadress</th>

						<th class="no-sort">Handlingar</th>

					</tr>

				</thead>

				<tbody>

				</tbody>

			</table>

		</div>

	</div>



	<div class="modal" tabindex="-1" role="dialog" id="class-child-delete-confirmation-modal">

		<div class="modal-dialog" role="document">

			<div class="modal-content">

				<div class="modal-header">

					<h5 class="modal-title">Ta bort säljaren</h5>

				</div>

				<div class="modal-body">

					<p>Är du säker på att ta bort den här säljaren?</p>

				</div>

				<div class="modal-footer">

					<a href="#" class="btn btn-primary">Ta bort användare</a>

					<button type="button" class="btn btn-secondary" data-dismiss="modal">Stänga</button>

				</div>

			</div>

		</div>

	</div>';



	echo $output;

}



/**

 * @purpose: AJAX call to get list of all seller under class id

 * @return : (JSON) with list of all seller data and count

 * 

 */

function class_get_child_listing()

{

	global $wpdb;

	$data = [];

	$count = 0;



	$draw   = !empty($_POST['draw']) ? $_POST['draw'] : 0;

	$start  = !empty($_POST['start']) ? $_POST['start'] : 0;

	$length = !empty($_POST['length']) ? $_POST['length'] : 10;

	$search = !empty($_POST['search']) ? $_POST['search'] : [];

	$order  = !empty($_POST['order']) ? $_POST['order'] : [];



	$searchText  = !empty($search) ? $search['value'] : '';

	$orderColumn = !empty($order) ? $order[0]['column'] : 0;

	$orderDir    = !empty($order) ? $order[0]['dir'] : 'desc';

	$class_id    = get_current_user_id();



	reset_cookies_custom_plugin();



	switch ($orderColumn) 

	{

		case '1': $orderColumn = 'user_login';

			break;

		case '2': $orderColumn = 'display_name';

			break;

		case '3': $orderColumn = 'user_email';

			break;

		default: $orderColumn = 'ID';

	}



	$sql = "SELECT DISTINCT u.ID, u.user_login, u.user_email, u.display_name

		FROM wp_users u

		INNER JOIN wp_usermeta m ON u.ID = m.user_id

		WHERE m.meta_key = 'wp_capabilities' 

			AND m.meta_value LIKE '%children%'

			AND u.ID IN (

				SELECT user_id

				FROM wp_usermeta

				WHERE meta_key = 'assigned_class' 

				AND meta_value = " . $class_id . "

			)";



	if( !empty($searchText) )

	{

		$sql .= " AND (

			u.user_login LIKE '%" . $searchText . "%' OR 

			u.user_email LIKE '%" . $searchText . "%' OR 

			u.display_name LIKE '%" . $searchText . "%'

		)";

	}



	$count = count($wpdb->get_results($sql));



	$sql .= " ORDER BY " . $orderColumn . " " . $orderDir . "

		LIMIT " . $length . " OFFSET " . $start;



	$users = $wpdb->get_results($sql);



	if (!empty($users)) 

	{

		foreach ($users as $key => $user) 

		{

			$action_url = wc_get_account_endpoint_url( 'lägg-till-redigera-säljaren' );

			$action     = '<a href="' . $action_url . '?user_id=' . $user->ID . '"><i class="zg-icon-user-pen-icon"></i></a> &nbsp;&nbsp; <a class="remove-child-class" href="javascript:void(0);" data-user_id="' . $user->ID . '" data-role="child"><i class="zg-icon-delete-user-icon"></i></a>';





			$data[$key] = [

				$user->ID,

				$user->user_login,

				$user->display_name,

				$user->user_email,

				$action,

			];

		}

	}

	

	$response = array(

		"draw"            => intval($draw),

		"recordsTotal"    => $count,

		"recordsFiltered" => $count,

		"data"            => $data,

	);



	wp_send_json($response);

}



/**

 * @purpose: Add/Edit seller form and on submit create seller user in wordpress and link to class 

 * 

 */

function class_my_account_add_edit_child()

{

	$child_details = [];



	$class_id  = get_current_user_id();

	$school_id = get_user_meta($class_id, 'assigned_school', true);

	$user_id   = !empty($_GET['user_id']) ? $_GET['user_id'] : 0;



	$class_details = get_userdata($class_id);

	$slug = str_replace(' ', '-', $class_details->user_login);



	$args = array(

		'hide_empty' => false,

		'slug'       => strtolower($slug),

	);



	$vendor_terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );

	$list = get_field('barndetaljer', WC_PRODUCT_VENDORS_TAXONOMY . '_' . $vendor_terms[0]->term_id);



	if( $user_id )

		$child_details = get_userdata($user_id);



	if(!empty($_POST))

	{

		$username   = !empty($_POST['username']) ? $_POST['username'] : '';

		$email      = !empty($_POST['email']) ? $_POST['email'] : '';

		$first_name = !empty($_POST['first_name']) ? $_POST['first_name'] : '';

		$password   = !empty($_POST['password']) ? $_POST['password'] : '';

		$old_slug   = !empty($_POST['old_slug']) ? $_POST['old_slug'] : '';

		$role       = 'children';





		if( $user_id )

		{

			$args = apply_filters( 'wcpv_admin_register_vendor_args', array(

				'ID'            => $user_id,

				'user_email'    => $email,

				'user_nicename' => strtolower($first_name),

				'display_name'  => $first_name,

				'role'          => $role,

				'first_name'    => $first_name,

			) );



			if( $password )

				$args['user_pass'] = $password;



			wp_update_user($args);



			update_user_meta($user_id, 'assigned_school', sanitize_text_field($school_id));

			update_user_meta($user_id, 'assigned_class', sanitize_text_field($class_id));



			if( !empty($list) )

			{

				foreach ($list as $key => $value) 

				{

					if($value['slug'] === $old_slug)

					{

						$list[$key]['name']         = $first_name;

						$list[$key]['slug']         = $username;

						$list[$key]['barns_e-post'] = $email;



						if( $password )

							$list[$key]['password'] = $password;

					}

				}

			}



			update_field('barndetaljer', $list, WC_PRODUCT_VENDORS_TAXONOMY . '_' . $vendor_terms[0]->term_id);

		}

		else

		{

			$args = apply_filters( 'wcpv_admin_register_vendor_args', array(

				'user_login'    => $username,

				'user_email'    => $email,

				'user_pass'     => $password,

				'user_nicename' => strtolower($first_name),

				'display_name'  => $first_name,

				'role'          => $role,

				'first_name'    => $first_name,

			) );



			$user_id = wp_insert_user( $args );



			if( $user_id > 0 )

			{

				update_user_meta($user_id, 'assigned_school', sanitize_text_field($school_id));

				update_user_meta($user_id, 'assigned_class', sanitize_text_field($class_id));



				$list[] = [

					'slug'         => $username,

					'barns_e-post' => $email,

					'name'         => $first_name,

					'password'     => $password,

				];



				update_field('barndetaljer', $list, WC_PRODUCT_VENDORS_TAXONOMY . '_' . $vendor_terms[0]->term_id);

			}



			$wc = new WC_Emails();

			$wc->customer_new_account($user_id, array('user_pass' => $password));

		}



		$redirect_url = wc_get_account_endpoint_url( 'hantera-säljaren' ); ?>



		<script type="text/javascript">

			window.location.href = "<?php echo $redirect_url; ?>";

		</script>



		<?php 

	}



	$output = '<form action="" method="post" class="class-add-edit-child" name="class-add-edit-child" id="class-add-edit-child">



		<h3> ' . ( $user_id <= 0 ? 'Lägg till säljare' : 'Redigera säljare' ) . ' </h3>



		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">

			

			<label for="first_name">Visningsnamn&nbsp;<span class="required">*</span></label>

			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="first_name" autocomplete="off" value="'. ( !empty($child_details) ? $child_details->display_name : '' ) .'">



			<label style="display: none;" for="username">Användarnamn&nbsp;<span class="required">*</span></label>

			<input ' . ( $user_id > 0 ? 'readonly' : '' ) . ' type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="new-password" value="'. ( !empty($child_details) ? $child_details->user_login : '' ) .'">

		</p>

		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">

			<label for="email">E-postadress&nbsp;<span class="required">*</span></label>

			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="email" autocomplete="off" value="'. ( !empty($child_details) ? $child_details->user_email : '' ) .'">

			<label id="email_id_error" style="color: red; font-size: 14px; margin-top: 5px; text-transform: inherit !important; letter-spacing: 0 !important;"></label>

		</p>

		<div class="clear"></div>

		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">

			<label for="password">Lösenord&nbsp;<span class="required">*</span></label>

			<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="password" autocomplete="new-password">

		</p>

		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">

			<label for="confirm_password">Bekräfta lösenord&nbsp;<span class="required">*</span></label>

			<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="confirm_password" id="confirm_password">

		</p>

		<div class="clear"></div>

		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">

			<label for="role">Roll&nbsp;<span class="required">*</span></label>

			<input readonly type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="role" id="role" autocomplete="off" value="Säljare">

		</p>

		<div class="clear"></div>

		<p>

			<button id="submit-class" type="submit" class="woocommerce-Button button button-primary" name="submit" value="Spara ändringar">' . ( $user_id > 0 ? 'Uppdatera säljaren' : 'Lägg till säljare' ) . '</button> &emsp; <a href="' . wc_get_account_endpoint_url( 'hantera-säljaren' ) . '" id="cancel-class" class="woocommerce-Button button button-primary" name="submit"> Annullera </a>

		</p>

	</form>';



	echo $output;

}



/**

 * @purpose: Unlink seller account from class login and remove it from site

 * 

 */

function class_remove_child_users()

{

	$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : 0;

	$role    = !empty($_POST['role']) ? $_POST['role'] : '';



	setcookie('user_deleted', '1', time() + 86400, '/');



	if( $role == 'child' )

	{

		$assigned_school = get_user_meta($user_id, 'assigned_school', true);

		$assigned_class  = get_user_meta($user_id, 'assigned_class', true); 



		$class_details = get_userdata($assigned_class);

		$child_details = get_userdata($user_id);

		$slug          = str_replace(' ', '-', $class_details->user_login);



		$args = array(

			'hide_empty' => false,

			'slug'       => strtolower($slug),

		);



		$vendor_terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );

		$vendor_id = $vendor_terms[0]->term_id;



		$list = get_field('barndetaljer', WC_PRODUCT_VENDORS_TAXONOMY . '_' . $vendor_id);



		if( !empty($list) )

		{

			foreach ($list as $key => $value) 

			{

				if($value['slug'] === $child_details->user_login)

					unset($list[$key]);

			}

		}



		update_field('barndetaljer', $list, WC_PRODUCT_VENDORS_TAXONOMY . '_' . $vendor_id);

		wp_delete_user($user_id);

	}



	wp_die();

}



/**

 * @purpose: AJAX call to validate duplicate username exists while add/edit of seller details 

 * 

 */

function check_user_name_exists()

{

	global $wpdb;



	$user_id           = !empty($_POST['user_id']) ? $_POST['user_id'] : 0;

	$username_to_check = !empty($_POST['username']) ? $_POST['username'] : '';



	$check_user_id = username_exists($username_to_check);



	if( !empty($check_user_id) )

	{

		if( $user_id > 0 && $check_user_id == $user_id )

			echo json_encode('true');

		else if( $user_id > 0 && $check_user_id != $user_id )

			echo json_encode('Användarnamn existerar redan.');

		else if( $user_id == 0 && $check_user_id > 0 )

			echo json_encode('Användarnamn existerar redan.');

	}

	else

		echo json_encode('true');



	die();

}



/**

 * @purpose: AJAX call to validate duplicate email exists while add/edit of seller details 

 * 

 */

function check_user_email()

{

	global $wpdb;



	$user_id        = !empty($_POST['user_id']) ? $_POST['user_id'] : 0;

	$email_to_check = !empty($_POST['user_email']) ? $_POST['user_email'] : '';



	$check_user_id = email_exists($email_to_check);



	if( !empty($check_user_id) )

	{

		if( $user_id > 0 && $check_user_id == $user_id )

			echo json_encode('true');

		else if( $user_id > 0 && $check_user_id != $user_id )

			echo json_encode('Emailadressen finns redan.');

		else if( $user_id == 0 && $check_user_id > 0 )

			echo json_encode('Emailadressen finns redan.');

	}

	else

		echo json_encode('true');



	if( $user_id )

		setcookie('users_updated', '1', time() + 86400, '/');

	else

		setcookie('users_added', '1', time() + 86400, '/');



	die();

}



/**

 * @purpose: AJAX call to reset cookies on cancel button

 * 

 */

function reset_cookies()

{

	reset_cookies_custom_plugin();

	wp_die();

}