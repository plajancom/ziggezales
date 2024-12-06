<?php
/*
Plugin Name: Admin försäljningsorder
Description: Visa beställningsmässig produkt och visa antal utskrifter i försäljningslängd för admin.
Version: 1.0
Author: Mahesh Sharma
*/

define( 'SO_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'SO_PLUGIN_PATH', plugin_dir_path(__FILE__) );

include( plugin_dir_path( __FILE__ ) . 'pick-list.php');

// Action hooks and filters
add_action('admin_menu', 'admin_menu_selling_order');
add_action('admin_enqueue_scripts', 'selling_orders_enqueue_scripts');
add_action('wp_ajax_get_selling_order_details', 'get_selling_order_details');
add_action('wp_ajax_nopriv_get_selling_order_details', 'get_selling_order_details');

/**
 * @purpose: Include style and script for plugins
 * 
 */
function selling_orders_enqueue_scripts()
{
	$random_version = rand(1, 99999);

	if (is_admin() && ( isset($_GET['page']) && $_GET['page'] == 'wp-selling-orders') )
	{
		wp_enqueue_style('selling-order-css', SO_PLUGIN_URL . 'css/admin-selling-order-menu.css', array(), $random_version);
		wp_enqueue_script('selling-order-script', SO_PLUGIN_URL . 'js/admin-selling-order-menu.js', array('jquery'), $random_version, true);
		wp_localize_script('selling-order-script', 'selling_order_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('custom-ajax-nonce')));
	}
}

/**
 * @purpose: Add custom menu for showing products quantity for selling order for admin login depending on school list
 * 
 */
function admin_menu_selling_order()
{
	$logged_in_user = get_userdata(get_current_user_id());

	if( in_array('administrator', $logged_in_user->roles) == true )
	{
		add_menu_page(
			'Försäljningsorder',
			'Försäljningsorder',
			'manage_options',
			'wp-selling-orders',
			'render_selling_order_page',
			'dashicons-list-view', 
			57
		);
	}
}

/**
 * @purpose: Callback function to display view page for selling orders list
 * 
 */
function render_selling_order_page()
{
	global $wpdb;
	$output = '';

	$school_data = get_school('filter');
	$school_ids  = $school_data['school_ids'];
	$school_name = $school_data['school_name'];

	$output .= '<div class="wrap zg-selling-order">
		<h2>Försäljningsorder detalj</h2>

		<div class="date-range-filter" id="landing-page-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<label class="school-dropdown" for="school-dropdown">Välj Skola/Förening:</label>
			<select class="school-dropdown">
				<option value="">Välj Skola/Förening</option>';
				
				if( !empty($school_ids) )
				{
					foreach ($school_ids as $key => $school) 
						$output .= '<option value="' . $school . '"> ' . $school_name[$key] . ' </option>';
				}

			$output .= '</select>
			<input type="submit" class="button button-primary" value="Filtrera" id="filter-selling-order">
		</div>

		<div class="selling-order-list">
			<div class="table-responsive">
				<table class="table table-striped" id="table-selling-order"> 
					<thead>
						<th valign="middle">Föremålsnamn</th>
						<th valign="middle">Kategori</th>
						<th valign="middle">Antal artiklar</th>
						<th valign="middle">Skolans/föreningens namn</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>';

	echo $output;
}

/**
 * @purpose: AJAX call to get list of all products with their count linked with schools
 * @return : (JSON) with product details and total quantity
 * 
 */
function get_selling_order_details()
{
	global $wpdb;
	$wpdb->prepare('SET SESSION group_concat_max_len = 10000000');

	$school_ids = $data = [];
	$count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$school_id  = !empty($_POST['school_id']) ? $_POST['school_id'] : '';

	if( $school_id > 0 )
		array_push($school_ids, $school_id);
	else
	{
		$school_data = get_school();
		$school_ids  = $school_data['school_ids'];
	}

	$sqlClass = "SELECT GROUP_CONCAT(u.ID) as child_id
	FROM " . $wpdb->prefix . "users u
	INNER JOIN " . $wpdb->prefix . "usermeta AS um ON u.ID = um.user_id
	WHERE um.meta_key = 'assigned_school' 
	AND um.meta_value IN (" . implode(',', $school_ids) . ") 
	AND u.ID IN (
		SELECT user_id
		FROM " . $wpdb->prefix . "usermeta
		WHERE meta_key = '" . $wpdb->prefix . "capabilities'
		AND meta_value LIKE '%children%'
	)";

	$child_users = $wpdb->get_row($sqlClass);

	if( !empty($child_users) )
	{
		$sqlOrder = "SELECT 
			um.meta_value as assigned_school,
			p.ID, m3.meta_value as child_id,
			MAX(CASE WHEN oim.meta_key = '_product_id' THEN oim.meta_value END) AS product_id,
			MAX(CASE WHEN oim.meta_key = '_product_id' THEN oi.order_item_name END) AS product_name,
			SUM(CASE WHEN oim.meta_key = '_qty' THEN oim.meta_value END) AS total_quantity
		FROM wp_posts p
		INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
		INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
		INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
		INNER JOIN wp_woocommerce_order_items oi ON oi.order_id = p.ID
		INNER JOIN wp_woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
		INNER JOIN wp_users u ON m3.meta_value = u.ID 
		INNER JOIN wp_usermeta um ON u.ID = um.user_id 
		WHERE p.post_type = 'shop_order'
			AND m1.meta_key = '_order_total'
			AND m2.meta_key = 'order_commission_percent'
			AND m3.meta_key = 'spacial_page' 
			AND m3.meta_value IN (" . $child_users->child_id . ")
			AND m1.meta_value IS NOT NULL
			AND m2.meta_value IS NOT NULL
			AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'wc-completed', 'wc-on-hold', 'trash') 
			AND ( 
				( oim.meta_key = '_product_id' AND ( oim.meta_value IS NOT NULL OR oim.meta_value != 0 ) ) 
				OR 
				( oim.meta_key = '_qty'  AND ( oim.meta_value != 0  OR oim.meta_value IS NOT NULL  ) ) 
			)
			AND um.meta_key = 'assigned_school'
		";

		if( !empty($start_date) )
			$sqlOrder .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

		if( !empty($end_date) )
			$sqlOrder .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

		$sqlOrder .= " GROUP BY assigned_school, oi.order_item_name ";

		$count = $wpdb->get_results($sqlOrder);
		$count = !empty($count) ? count($count) : 0;

		$sqlOrder .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

		$orderDetails = $wpdb->get_results($sqlOrder);

		if( !empty($orderDetails) )
		{
			foreach ($orderDetails as $key => $orders) 
			{
				$category = '';

				$order              = wc_get_order($orders->ID);
				$child_id           = $orders->child_id;
				$assigned_school_id = get_user_meta($child_id, 'assigned_school', true);
				$school_details     = get_userdata($assigned_school_id);
				$order_date         = date(DATE_FORMAT, strtotime($order->get_date_created()));

				if( !empty($orders->product_id) && $orders->product_id > 0 )
				{
					$product_categories = get_the_terms($orders->product_id, 'product_cat');
					if ( !empty($product_categories) ) 
					{
						foreach ($product_categories as $key1 => $category_detail)  
						{
							if( $key1 == 0 )
								$category .= $category_detail->name;
							else
								$category .= ", " . $category_detail->name;
						}
					}
				}

				$product_name = explode(' - ', $orders->product_name);
				$product_details_name = $product_name[0];

				if( !empty($product_name[1]) )
					$product_details_name .= ' (' . $product_name[1] . ')';

				$data[$key] = [$product_details_name, $category, $orders->total_quantity, $school_details->display_name];
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
 * @purpose: Get list of all users whose role is schools
 * @param  : $type
 * @return : (array) with school ids and school names
 */
function get_school($type = null)
{
	$school_names = $school_ids = [];

	$args = array(
		'role'    => 'wc_product_vendors_manager_vendor',
		'orderby' => 'display_name',
		'order'   => 'ASC'
	);
	
	$users = get_users( $args );

	$school_ids = array_map(function ($user) {
		return $user->ID;
	}, $users);

	if( $type == 'filter' )
	{
		$school_name = array_map(function ($user) {
			return $user->display_name;
		}, $users);
	}

	$response = [
		'school_ids'  => $school_ids,
		'school_name' => $school_name,
	];

	return $response;
}