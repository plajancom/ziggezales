<?php
/*
Plugin Name: Visa teamorder
Description: Visa teamwise order på myaccount för skola, klass och säljarinloggning
Version: 1.0
Author: Mahesh Sharma
*/

define('VO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VO_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Action hooks and filters
add_action( 'wp_enqueue_scripts', 'enqueue_team_order_scripts' );
add_filter( 'woocommerce_account_menu_items', 'team_order_menu_links', 40 );
add_action( 'init', 'team_order_add_endpoints' );
add_action( 'woocommerce_account_lagordning_endpoint', 'my_account_team_orders_listing' );
add_action( 'woocommerce_account_view-team-orders_endpoint', 'my_account_view_team_orders' );

add_action( 'wp_ajax_get_order_list_child', 'get_order_list_child' );
add_action( 'wp_ajax_nopriv_get_order_list_child', 'get_order_list_child' );
add_action( 'wp_ajax_get_class_seller_order_list', 'get_class_seller_order_list' );
add_action( 'wp_ajax_nopriv_get_class_seller_order_list', 'get_class_seller_order_list' );
add_action( 'wp_ajax_get_seller_order_list', 'get_seller_order_list' );
add_action( 'wp_ajax_nopriv_get_seller_order_list', 'get_seller_order_list' );


/**
 * @purpose: Enqueue custom scripts and styles for plugin
 * 
 */
function enqueue_team_order_scripts()
{
	$random_version = rand(1, 99999);

	wp_enqueue_script('team-order-custom-script', VO_PLUGIN_URL . 'js/script-team-order.js', array('jquery'), $random_version, true);
	wp_localize_script('team-order-custom-script', 'team_order_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('custom-ajax-nonce')));
}

/**
 * @purpose: Display team order menu for school, class and seller login before logout
 * @param  : $menu_links (my-account page menu links)
 * @return : (array) $menu_links (Updated links for my-account page)
 * 
 */
function team_order_menu_links($menu_links)
{
	$roles        = wp_get_current_user()->roles;
	$logout_index = array_search('customer-logout', array_keys($menu_links));

	if( in_array('wc_product_vendors_manager_vendor', $roles) == true || in_array('wc_product_vendors_admin_vendor', $roles) == true || in_array('children', $roles) == true ) 
	{
		if ($logout_index !== false) {
			$menu_links = array_slice($menu_links, 0, $logout_index, true)
				+ array('lagordning'   => 'Lagorder')
				+ array_slice($menu_links, $logout_index, NULL, true);
		} 
		else
			$menu_links['lagordning']   = 'Lagorder';
	}

	return $menu_links;
}

/**
 * @purpose: Rewrite endpoints URL
 * 
 */
function team_order_add_endpoints()
{
	add_rewrite_endpoint( 'lagordning', EP_PAGES );
	add_rewrite_endpoint( 'view-team-orders', EP_PAGES );
}

/**
 * @purpose: Display function to show listing of order for seller depending on school, class and seller login 
 * 
 */
function my_account_team_orders_listing()
{
	$output         = '';
	$roles          = wp_get_current_user()->roles;
	$order_statuses = wc_get_order_statuses(); 
	
	if( isset($_COOKIE['order_status']) && $_COOKIE['order_status'] == '1' )
	{ ?>

		<script type="text/javascript">
			jQuery('.woocommerce-notices-wrapper').append('<div class="woocommerce-message" role="alert">Orderstatus har ändrats.</div>');
		</script>

	<?php }

	if( in_array('wc_product_vendors_manager_vendor', $roles) == true )
	{
		$output = '<div class="wrap zg-commision" style="position: relative;">

			<div class="row">
				<div class="col-md-8">
					<h2> Visa Beställningar </h2>
				</div>
			</div>

			<div class="date-range-filter" id="landing-page-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<label class="zg-statuslabel" for="status-filter">Status:</label>
				<select class="status-filter" id="status-filter">
					<option value="">Välj Status</option>';

					if( !empty( $order_statuses ) )
					{
						foreach( $order_statuses as $status_key => $status )
							$output .= '<option value="' . esc_attr( $status_key ) . '">' . esc_html( $status ) . '</option>';
					}

				$output .= '</select>
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>
			
			<div class="manage-child-listing">
				<table class="table table-bordered" id="child-orders-list">
					<thead>
						<tr>
							<th>Beställning</th>
							<th>Beställ efter namn</th>
							<th>Klassens namn - säljarens namn</th>
							<th>Datum</th>
							<th>Status</th>
							<th>Total</th>
							<th class="no-sort">Åtgärder</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>';
	}

	if( in_array('wc_product_vendors_admin_vendor', $roles) == true )
	{
		$output = '<div class="wrap zg-commision" style="position: relative;">

			<div class="row">
				<div class="col-md-8">
					<h2> Visa Beställningar </h2>
				</div>
			</div>

			<div class="date-range-filter" id="landing-page-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<label class="zg-statuslabel" for="status-filter">Status:</label>
				<select class="status-filter" id="status-filter">
					<option value="">Välj Status</option>';

					if( !empty( $order_statuses ) )
					{
						foreach( $order_statuses as $status_key => $status )
							$output .= '<option value="' . esc_attr( $status_key ) . '">' . esc_html( $status ) . '</option>';
					}

				$output .= '</select>
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>
			
			<div class="manage-child-listing">
				<table class="table table-bordered" id="class-child-orders-list">
					<thead>
						<tr>
							<th>Beställning</th>
							<th>Beställ efter namn</th>
							<th>Säljarens namn</th>
							<th>Datum</th>
							<th>Status</th>
							<th>Total</th>
							<th class="no-sort">Åtgärder</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>';
	}

	if( in_array('children', $roles) == true )
	{
		$output = '<div class="wrap zg-commision" style="position: relative;">

			<div class="row">
				<div class="col-md-8">
					<h2>Visa Beställningar</h2>
				</div>
			</div>

			<div class="date-range-filter" id="landing-page-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<label class="zg-statuslabel" for="status-filter">Status:</label>
				<select class="status-filter" id="status-filter">
					<option value="">Välj Status</option>';

					if( !empty( $order_statuses ) )
					{
						foreach( $order_statuses as $status_key => $status )
							$output .= '<option value="' . esc_attr( $status_key ) . '">' . esc_html( $status ) . '</option>';
					}

				$output .= '</select>
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>
			
			<div class="manage-child-listing">
				<table class="table table-bordered" id="seller-orders-list">
					<thead>
						<tr>
							<th>Beställning</th>
							<th>Beställ efter namn</th>
							<th>Datum</th>
							<th>Status</th>
							<th>Total</th>
							<th class="no-sort">Åtgärder</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>';
	}

	echo $output;
}

/**
 * @purpose: View order page template to view order detail for selected order id
 * @param  : $order_id
 * 
 */
function my_account_view_team_orders( $order_id )
{
	$order = wc_get_order( $order_id );

	wc_get_template( 
		'view-team-order.php', 
		array(
			'order_id' => $order_id,
			'order'    => $order
		),
	);
}

/**
 * @purpose: DataTable AJAX request to retrieve list of all seller orders linked to school login
 * @return : (json_array) order list and total count
 * 
 */
function get_order_list_child()
{
	global $wpdb;

	$data  = [];
	$count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$status     = !empty($_POST['status']) ? $_POST['status'] : '';

	$searchText  = !empty($search) ? $search['value'] : '';
	$orderColumn = !empty($order) ? $order[0]['column'] : 0;
	$orderDir    = !empty($order) ? $order[0]['dir'] : 'desc';

	$school_id   = get_current_user_id();

	reset_cookies_custom_plugin(); // Reset all custom cookies for plugin

	if( !empty($status) )
		$status = "'" . $status . "'";
	else
		$status = "'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled'";

	$sql = "SELECT GROUP_CONCAT(u.ID) as child_id
		FROM wp_users u
		INNER JOIN wp_usermeta m ON u.ID = m.user_id
		WHERE m.meta_key = 'wp_capabilities' 
			AND m.meta_value LIKE '%children%'
			AND u.ID IN (
				SELECT user_id
				FROM wp_usermeta
				WHERE meta_key = 'assigned_school' 
				AND meta_value = " . $school_id . "
			)
		GROUP BY meta_value";

	$child_lists = $wpdb->get_row($sql);

	if( !empty($child_lists) )
	{
		$sql1 = "SELECT 
			p.ID AS order_id, 
			p.post_date AS order_date, 
			p.post_status AS order_status, 
			MAX(CASE WHEN pm1.meta_key = '_order_total' THEN pm1.meta_value END) AS order_total, 
			MAX(CASE WHEN pm.meta_key = 'spacial_page' THEN pm.meta_value END) AS special_page_value 
		FROM wp_posts p 
		LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id 
		LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id 
		WHERE p.post_type = 'shop_order' 
			AND p.post_status IN ( " . $status . " ) 
			AND pm.meta_key IN ('spacial_page') 
			AND pm.meta_value IN ( " . $child_lists->child_id . " ) ";

			if( !empty($start_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($start_date)) . "' ";

			if( !empty($end_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($end_date)) . "' ";

		$sql1 .= " GROUP BY p.ID ";

		$count = count($wpdb->get_results($sql1));

		$sql1 .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

		$order_list = $wpdb->get_results($sql1);

		if( !empty($order_list) )
		{
			foreach ($order_list as $key => $orders) 
			{
				$child_details = get_userdata( $orders->special_page_value );
				$class_details = get_userdata(get_user_meta($child_details->ID, 'assigned_class', true));

				$order      = wc_get_order( $orders->order_id );
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();

				$action      = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '" class="woocommerce-button button view">Visa</a>';
				$order_id    = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '"> #' . $orders->order_id . '</a>';
				$order_total = wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
				$order_date  = '<time datetime="' . esc_attr( $order->get_date_created()->date( 'c' ) ) . '">' . esc_html( wc_format_datetime( $order->get_date_created(), DATE_FORMAT ) ) .'</time>';
				$ordered_by  = get_userdata( $order->get_user_id() );
				$guest_user  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				$school_details = $class_details->display_name . ' - ' . $child_details->display_name;

				$data[$key] = [
					$order_id,
					!empty($ordered_by) ? $ordered_by->display_name : $guest_user, 
					$school_details, 
					$order_date, 
					esc_html( wc_get_order_status_name( $order->get_status() ) ),
					$order_total,
					$action,
				];
			}
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
 * @purpose: DataTable AJAX request to retrieve list of all seller orders linked to class login
 * @return : (json_array) order list and total count
 * 
 */
function get_class_seller_order_list()
{
	global $wpdb;

	$data  = [];
	$count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$status     = !empty($_POST['status']) ? $_POST['status'] : '';

	$searchText  = !empty($search) ? $search['value'] : '';
	$orderColumn = !empty($order) ? $order[0]['column'] : 0;
	$orderDir    = !empty($order) ? $order[0]['dir'] : 'desc';
	$class_id    = get_current_user_id();
	$school_id   = get_user_meta($class_id, 'assigned_school', true);

	$class_details = get_userdata($class_id);

	reset_cookies_custom_plugin(); // Reset all custom cookies for plugin

	if( !empty($status) )
		$status = "'" . $status . "'";
	else
		$status = "'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled'";

	$sql = "SELECT GROUP_CONCAT(u.ID) as child_id
		FROM wp_users u
		INNER JOIN wp_usermeta m ON u.ID = m.user_id
		WHERE m.meta_key = 'wp_capabilities' 
			AND m.meta_value LIKE '%children%'
			AND u.ID IN (
				SELECT um.user_id
				FROM wp_usermeta um
				JOIN wp_usermeta um1 ON um.user_id = um1.user_id
				WHERE um.meta_key = 'assigned_school' 
					AND um.meta_value = " . $school_id . "
					AND um1.meta_key = 'assigned_class' 
					AND um1.meta_value = " . $class_id . "
			)
		GROUP BY m.meta_value";

	$child_lists = $wpdb->get_row($sql);

	if( !empty($child_lists) )
	{
		$sql1 = "SELECT 
			p.ID AS order_id, 
			p.post_date AS order_date, 
			p.post_status AS order_status, 
			MAX(CASE WHEN pm1.meta_key = '_order_total' THEN pm1.meta_value END) AS order_total, 
			MAX(CASE WHEN pm.meta_key = 'spacial_page' THEN pm.meta_value END) AS special_page_value 
		FROM wp_posts p 
		LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id 
		LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id 
		WHERE p.post_type = 'shop_order' 
			AND p.post_status IN ( " . $status . " ) 
			AND pm.meta_key IN ('spacial_page') 
			AND pm.meta_value IN ( " . $child_lists->child_id . " ) ";

			if( !empty($start_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($start_date)) . "' ";

			if( !empty($end_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($end_date)) . "' ";

		$sql1 .= " GROUP BY p.ID ";

		$count = count($wpdb->get_results($sql1));

		$sql1 .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

		$order_list = $wpdb->get_results($sql1);

		if( !empty($order_list) )
		{
			foreach ($order_list as $key => $orders) 
			{
				$child_details = get_userdata( $orders->special_page_value );

				$order      = wc_get_order( $orders->order_id );
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();

				$action      = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '" class="woocommerce-button button view">Visa</a>';
				$order_id    = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '"> #' . $orders->order_id . '</a>';
				$order_total = wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
				$order_date  = '<time datetime="' . esc_attr( $order->get_date_created()->date( 'c' ) ) . '">' . esc_html( wc_format_datetime( $order->get_date_created(), DATE_FORMAT ) ) .'</time>';
				$ordered_by  = get_userdata( $order->get_user_id() );
				$guest_user  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				$school_details = $child_details->display_name;

				$data[$key] = [
					$order_id,
					!empty($ordered_by) ? $ordered_by->display_name : $guest_user, 
					$school_details, 
					$order_date, 
					esc_html( wc_get_order_status_name( $order->get_status() ) ),
					$order_total,
					$action,
				];
			}
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
 * @purpose: DataTable AJAX request to retrieve list of all seller orders
 * @return : (json_array) order list and total count
 * 
 */
function get_seller_order_list()
{
	global $wpdb;

	$data  = [];
	$count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$status     = !empty($_POST['status']) ? $_POST['status'] : '';

	$searchText  = !empty($search) ? $search['value'] : '';
	$orderColumn = !empty($order) ? $order[0]['column'] : 0;
	$orderDir    = !empty($order) ? $order[0]['dir'] : 'desc';
	$seller_id   = get_current_user_id();
	$school_id   = get_user_meta($seller_id, 'assigned_school', true);
	$class_id    = get_user_meta($seller_id, 'assigned_class', true);

	$seller_details = get_userdata($seller_id);

	reset_cookies_custom_plugin(); // Reset all custom cookies for plugin

	if( !empty($status) )
		$status = "'" . $status . "'";
	else
		$status = "'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled'";

	$sql1 = "SELECT 
			p.ID AS order_id, 
			p.post_date AS order_date, 
			p.post_status AS order_status, 
			MAX(CASE WHEN pm1.meta_key = '_order_total' THEN pm1.meta_value END) AS order_total, 
			MAX(CASE WHEN pm.meta_key = 'spacial_page' THEN pm.meta_value END) AS special_page_value 
		FROM wp_posts p 
		LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id 
		LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id 
		WHERE p.post_type = 'shop_order' 
			AND p.post_status IN ( " . $status . " ) 
			AND pm.meta_key IN ('spacial_page') 
			AND pm.meta_value IN ( " . $seller_id . " ) ";

			if( !empty($start_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($start_date)) . "' ";

			if( !empty($end_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($end_date)) . "' ";

		$sql1 .= " GROUP BY p.ID ";

		$count = count($wpdb->get_results($sql1));

		$sql1 .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

		$order_list = $wpdb->get_results($sql1);

		if( !empty($order_list) )
		{
			foreach ($order_list as $key => $orders) 
			{
				$child_details = get_userdata( $orders->special_page_value );

				$order      = wc_get_order( $orders->order_id );
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();

				$action      = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '" class="woocommerce-button button view">Visa</a>';
				$order_id    = '<a href="' . wc_get_endpoint_url( 'view-team-orders', $orders->order_id, wc_get_page_permalink( 'myaccount' ) ) . '"> #' . $orders->order_id . '</a>';
				$order_total = wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
				$order_date  = '<time datetime="' . esc_attr( $order->get_date_created()->date( 'c' ) ) . '">' . esc_html( wc_format_datetime( $order->get_date_created(), DATE_FORMAT ) ) .'</time>';
				$ordered_by  = get_userdata( $order->get_user_id() );
				$guest_user  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				$data[$key] = [
					$order_id,
					!empty($ordered_by) ? $ordered_by->display_name : $guest_user, 
					$order_date, 
					esc_html( wc_get_order_status_name( $order->get_status() ) ),
					$order_total,
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