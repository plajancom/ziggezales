<?php
add_action("admin_init", "download_csv");
function download_csv() {
	if(isset($_POST['export-pick-list'])) {
		$data = fetchQuery(true);
		if(!empty($data['data'])) {

			$user_CSV[0] = array('Föremålsnamn', 'Kategori', 'Antal artiklar', 'Skolans/föreningens namn', 'Alternativ');

			if(isset($_POST['seller_id']) && !empty($_POST['seller_id'])) {
				$user_CSV[0][] = 'Säljare';
				$user_CSV[0][] = 'Beställning ID';
			}
			// very simple to increment with i++ if looping through a database result 
			$row = 1;
			foreach($data['data'] as $d) {
				$user_CSV[$row] = array($d[0], $d[1], $d[2], $d[3], $d[4]);
				if(isset($_POST['seller_id']) && !empty($_POST['seller_id'])) {
					$user_CSV[$row][] = $d[5];
					$user_CSV[$row][] = $d[6];
				}
				++$row;
			}

			$fp = fopen('php://output', 'wb');
			foreach ($user_CSV as $line) {
				fputcsv($fp, $line, ',');
			}
			fclose($fp);
		}
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"report.csv\";" );
		header("Content-Transfer-Encoding: binary");
		exit(0);
	}
}
// Action hooks and filters
add_action('admin_menu', 'admin_menu_pick_order');
add_action('admin_enqueue_scripts', 'pick_orders_enqueue_scripts');
add_action('wp_ajax_get_pick_order_details', 'get_pick_order_details');
add_action('wp_ajax_nopriv_get_pick_order_details', 'get_pick_order_details');

add_action( 'wp_ajax_get_pick_school_class', 'get_pick_school_class' );
add_action( 'wp_ajax_nopriv_get_pick_school_class', 'get_pick_school_class' );

add_action( 'wp_ajax_get_pick_class_seller', 'get_pick_class_seller' );
add_action( 'wp_ajax_nopriv_get_pick_class_seller', 'get_pick_class_seller' );

/**
 * @purpose: Include style and script for plugins
 * 
 */
function pick_orders_enqueue_scripts() {
	$random_version = rand(1, 99999);

	if (is_admin() && ( isset($_GET['page']) && $_GET['page'] == 'wp-pick-orders') ) {
        wp_enqueue_style('selling-order-css', SO_PLUGIN_URL . 'css/admin-selling-order-menu.css', array(), $random_version);
		wp_enqueue_script('pick-order-script', SO_PLUGIN_URL . 'js/admin-pick-order-menu.js', array('jquery'), $random_version, true);
		wp_localize_script('pick-order-script', 'pick_order_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('custom-ajax-nonce')));
	}
}

/**
 * @purpose: Add custom menu for showing products quantity for pick order for admin login depending on school list
 * 
 */
function admin_menu_pick_order() {
	$logged_in_user = get_userdata(get_current_user_id());

	if( in_array('administrator', $logged_in_user->roles) == true ) {
		add_menu_page(
			'Plocklista',
			'Plocklista',
			'manage_options',
			'wp-pick-orders',
			'render_pick_order_page',
			'dashicons-list-view', 
			57
		);
	}
}

/**
 * @purpose: Callback function to display view page for pick orders list
 * 
 */
function render_pick_order_page() {
	global $wpdb;
	$output = '';

	$school_data = get_pick_school('filter');
	$school_ids  = $school_data['school_ids'];
	$school_name = $school_data['school_name'];
    ob_start();
    require_once( plugin_dir_path( __FILE__ ) . 'pick-order-list.php');
	$output = ob_get_contents();
    ob_end_clean();

	echo $output;
}

/**
 * @purpose: AJAX call to get list of all products with their count linked with schools
 * @return : (JSON) with product details and total quantity
 * 
 */
function get_pick_order_details() {
	$draw = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$data = fetchQuery();
	$response = array(
		"draw"            => intval($draw),
		"recordsTotal"    => $data['count'],
		"recordsFiltered" => $data['count'],
		"data"            => $data['data'],
	);

	wp_send_json($response);
}

function fetchQuery($export = false) {
	global $wpdb;
	$wpdb->prepare('SET SESSION group_concat_max_len = 10000000');

	$school_ids = $data = $class_ids = [];
	$count = 0;

	$start = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length = !empty($_POST['length']) ? $_POST['length'] : 10;
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : '';
	$class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : '';
	$seller_id = !empty($_POST['seller_id']) ? $_POST['seller_id'] : '';

	if( $school_id > 0 ) {
		array_push($school_ids, $school_id);
	} else {
		$school_data = get_pick_school();
		$school_ids  = $school_data['school_ids'];
	}

	if( $class_id > 0 ) {
		array_push($class_ids, $class_id);
	}

	if(empty($seller_id)) {
		$sqlClass = "SELECT GROUP_CONCAT(u.ID) as child_id
			FROM " . $wpdb->prefix . "users u
			INNER JOIN " . $wpdb->prefix . "usermeta AS um ON u.ID = um.user_id ";
		if(!empty($class_id)) {
			$sqlClass .= "WHERE um.meta_key = 'assigned_class' AND um.meta_value IN (" . implode(',', $class_ids) . ") ";
		} else {
			$sqlClass .= "WHERE um.meta_key = 'assigned_school' AND um.meta_value IN (" . implode(',', $school_ids) . ") ";
		}
		$sqlClass .= "AND u.ID IN (
				SELECT user_id
				FROM " . $wpdb->prefix . "usermeta
				WHERE meta_key = '" . $wpdb->prefix . "capabilities'
				AND meta_value LIKE '%children%'
			)";

		$u = $wpdb->get_row($sqlClass);
		$child_users = $u->child_id;
	} else {
		$child_users = $seller_id;
	}

	if( !empty($child_users) ) {
		$sqlOrder = "SELECT 
			um.meta_value as assigned_school,
			p.ID, m3.meta_value as child_id,oi.order_id,
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
			AND m3.meta_value IN (" . $child_users . ")
			AND m1.meta_value IS NOT NULL
			AND m2.meta_value IS NOT NULL
			AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'wc-completed', 'wc-on-hold', 'trash') 
			AND ( 
				( oim.meta_key = '_product_id' AND ( oim.meta_value IS NOT NULL OR oim.meta_value != 0 ) ) 
				OR 
				( oim.meta_key = '_qty'  AND ( oim.meta_value != 0  OR oim.meta_value IS NOT NULL  ) ) 
			)
			AND um.meta_key = 'assigned_school'
			AND p.id IN(SELECT post_id FROM wp_postmeta WHERE meta_key ='_order_shipping' AND meta_value = 0)
		";

		if( !empty($start_date) )
			$sqlOrder .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

		if( !empty($end_date) )
			$sqlOrder .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";
		if(empty($seller_id)) {
			$sqlOrder .= " GROUP BY assigned_school, oi.order_item_name ";
		} else {
			$sqlOrder .= " GROUP BY oi.order_id ";
		}

		$count = $wpdb->get_results($sqlOrder);
		$count = !empty($count) ? count($count) : 0;

		$sqlOrder .= " ORDER BY p.ID DESC";
		if(!$export) {
			$sqlOrder .= " LIMIT " . $length . " OFFSET " . $start;
		}

		$orderDetails = $wpdb->get_results($sqlOrder);

		if( !empty($orderDetails) ) {
			foreach ($orderDetails as $key => $orders) {
				$category = '';
				$order = wc_get_order($orders->ID);
				$child_id = $orders->child_id;
				$assigned_school_id = get_user_meta($child_id, 'assigned_school', true);
				$school_details = get_userdata($assigned_school_id);

				$assigned_class_id = get_user_meta($child_id, 'assigned_class', true);
				$class_details = get_userdata($assigned_class_id);

				$child_details = get_userdata($child_id);

				if( !empty($orders->product_id) && $orders->product_id > 0 ) {
					$product_categories = get_the_terms($orders->product_id, 'product_cat');
					if ( !empty($product_categories) ) {
						foreach ($product_categories as $key1 => $category_detail) {
							if( $key1 == 0 ) {
								$category .= $category_detail->name;
							} else {
								$category .= ", " . $category_detail->name;
							}
						}
					}
				}

				$product_name = explode(' - ', $orders->product_name);
				$product_details_name = $product_name[0];

				if( !empty($product_name[1]) )
					$product_details_name .= ' (' . $product_name[1] . ')';

				$data[$key] = [$product_details_name, $category, $orders->total_quantity, $school_details->display_name, $class_details->display_name];
				if(!empty($seller_id)) {
					$data[$key][] = $child_details->display_name;
					$data[$key][] = $orders->order_id;
				} else {
					$data[$key][] = '';
					$data[$key][] = '';
				}
			}
		}
	}
	return ['data' => $data, 'count' => $count];
}

/**
 * @purpose: Get list of all users whose role is schools
 * @param  : $type
 * @return : (array) with school ids and school names
 */
function get_pick_school($type = null) {
	$school_ids = [];

	$args = array(
		'role'    => 'wc_product_vendors_manager_vendor',
		'orderby' => 'display_name',
		'order'   => 'ASC'
	);
	
	$users = get_users( $args );

	$school_ids = array_map(function ($user) {
		return $user->ID;
	}, $users);
	$school_name = [];
	if( $type == 'filter' ) {
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

function get_pick_school_class() {
	$output = '';
	$school_id = !empty($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
    
	if( $school_id > 0 ) {
		$args = array(
			'orderby' => 'display_name',
			'role'    => 'wc_product_vendors_admin_vendor',
			'meta_query' => array(
				array(
					'key' => 'assigned_school',
					'value' => $school_id,
					'compare' => '='
				)
			)
		);

		$wp_user_query = new WP_User_Query($args);
		$class_object = $wp_user_query->get_results();

		if( !empty($class_object) ) {
			$output .= '<option value="">Välj alternativ</option>';

			foreach($class_object as $class) {
				$output .= '<option value="' . $class->ID . '">' . $class->display_name . '</option>';
			}
		} else {
			$output = '<option value="">Välj alternativ</option>';
        }
	} else {
		$output = '<option value="">Välj alternativ</option>';
    }
	echo $output;
	wp_die();
}

function get_pick_class_seller() {
	$output = '<option value="">Välj Säljare</option>';

	$seller_role = 'children';
	$class_id = !empty($_REQUEST['class_id']) ? $_REQUEST['class_id'] : 0;
	$school_id = ( $class_id > 0 ) ? get_user_meta($class_id, 'assigned_school', true) : 0;

	if( $class_id > 0 && $school_id > 0 ) {
		$args = array(
			'role' => $seller_role,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'assigned_school',
					'value' => $school_id,
					'compare' => '='
				),
				array(
					'key' => 'assigned_class',
					'value' => $class_id,
					'compare' => '='
				)
			),
		);

		$user_query = new WP_User_Query($args);
		$seller_list = $user_query->get_results();

		if( !empty($seller_list) ) {
			foreach ($seller_list as $seller_detail)  {
				$output .= '<option value="' . $seller_detail->ID . '">' . $seller_detail->display_name . '</option>';
			}
		}
	}

	echo $output;
	wp_die();
}