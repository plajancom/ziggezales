<?php
/*
Plugin Name: Commission Management
Description: Administratörsalternativ för att ställa in skolwebbplatsprovision. Visa klass-/barnprovision för skola, klass och säljarinloggning.
Version: 1.0
Author: Mahesh Sharma
*/

define( 'CO_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'CO_PLUGIN_PATH', plugin_dir_path(__FILE__) );

// Action/Filter hooks for plugin
add_action('admin_menu', 'admin_menu_commission_management');
add_action('admin_enqueue_scripts', 'commission_enqueue_datatable_script');
add_action('wp_enqueue_scripts', 'commission_enqueue_datatable_script');
add_filter( 'woocommerce_account_menu_items', 'manage_commission_for_users', 40 );
add_action( 'init', 'zz_add_frontend_endpoints_commission' );
add_action( 'woocommerce_account_skolkommission_endpoint', 'school_my_account_manage_commission' );
add_action( 'woocommerce_account_skol-klass-kommission_endpoint', 'school_class_my_account_manage_commission' );
add_action( 'woocommerce_account_skola-säljarkommission_endpoint', 'school_child_my_account_manage_commission' );
add_action( 'woocommerce_account_klass-kommission_endpoint', 'class_my_account_manage_commission' );
add_action( 'woocommerce_account_säljarprovision_endpoint', 'child_my_account_manage_commission' );
add_action('wp_ajax_get_school_commission_details', 'get_school_commission_details');
add_action('wp_ajax_nopriv_get_school_commission_details', 'get_school_commission_details');
add_action('wp_ajax_get_class_commission_details', 'get_class_commission_details');
add_action('wp_ajax_nopriv_get_class_commission_details', 'get_class_commission_details');
add_action('wp_ajax_get_child_commission_detail', 'get_child_commission_detail');
add_action('wp_ajax_nopriv_get_child_commission_detail', 'get_child_commission_detail');
add_action('wp_ajax_get_commission_details', 'get_commission_details');
add_action('wp_ajax_nopriv_get_commission_details', 'get_commission_details');
add_action('wp_ajax_get_sc_commission_details', 'get_sc_commission_details');
add_action('wp_ajax_nopriv_get_sc_commission_details', 'get_sc_commission_details');
add_action('wp_ajax_get_schild_commission_details', 'get_schild_commission_details');
add_action('wp_ajax_nopriv_get_schild_commission_details', 'get_schild_commission_details');


/**
 * @purpose: Include style and script for plugins
 * 
 */
function commission_enqueue_datatable_script()
{
	$random_version = rand(1, 99999);

	if (is_admin()) 
	{
		// Bootstrap Style and Script
		wp_enqueue_script('bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array('jquery'), $random_version, true);
		wp_enqueue_style('bootstrap-style', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css', array(), $random_version);

		// DataTable Style and Script
		wp_enqueue_script('jquery-datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), $random_version, true);
		wp_enqueue_script('bootstrap-datatables', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', array('jquery'), $random_version, true);
		wp_enqueue_style('jquery-datatables-css', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', array(), $random_version);

		// Font Awesome Style and Script
		wp_enqueue_script('font-awesome-script', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js', array('jquery'), $random_version, true);
		wp_enqueue_style('font-awesome-style', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), $random_version);

		// Bootstrap datepicker
		wp_enqueue_script('datepicker-script', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js', array('jquery'), $random_version, true);
		wp_enqueue_script('datepicker-locale-script', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.sv.min.js', array('jquery'), $random_version, true);
		wp_enqueue_style('datepciker-style', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css', array(), $random_version);

		// jQuery UI Style
		wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css', array(), $random_version);

		if(isset($_GET['page']) && ( $_GET['page'] == 'wp-commissions' || $_GET['page'] == 'wp-class-commission' || $_GET['page'] == 'wp-child-commission' ) )
			wp_enqueue_style('commission-css', CO_PLUGIN_URL . 'css/commission-style.css', array(), $random_version);
	}

	wp_enqueue_script('commission-script', CO_PLUGIN_URL . 'js/commission.js', array('jquery'), $random_version, true);
	wp_localize_script('commission-script', 'commission_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('custom-ajax-nonce')));
}


/**
 * @purpose: Add custom menu for setting commission management for admin login 
 * 
 */
function admin_menu_commission_management() 
{
	if (is_plugin_active('woocommerce/woocommerce.php')) 
	{
		$logged_in_user = get_userdata(get_current_user_id());

		if( in_array('administrator', $logged_in_user->roles) == true )
		{
			add_options_page(
				'Inställning av provision', 
				'Inställning av provision', 
				'manage_options', 
				'commission-setting', 
				'commission_setting_page_callback'
			);

			add_menu_page( __( 'Provision', 'woocommerce-product-vendors' ), 
				__( 'Provision', 'woocommerce-product-vendors' ), 
				'manage_vendors', 
				'wp-commissions', 
				'render_commission_page', 
				'dashicons-chart-pie', 
				56 
			);

			add_submenu_page(
				null,
				'Klass/lag Provision',
				'Klass/lag Provision',
				'manage_vendors',
				'wp-class-commission',
				'render_commission_page'
			);

			add_submenu_page(
				null,
				'Säljar Provision',
				'Säljar Provision',
				'manage_vendors',
				'wp-child-commission',
				'render_commission_page'
			);
		}
	}
}


/**
 * @purpose: Callback function save and display form to set commission for School/Website
 * 
 */
function commission_setting_page_callback() 
{
	if( !empty($_POST) )
	{
		if (isset($_POST['school_commission_percentage']))
			update_option('school_commission_percentage', sanitize_text_field($_POST['school_commission_percentage']));

		if (isset($_POST['website_commission_percentage']))
			update_option('website_commission_percentage', sanitize_text_field($_POST['website_commission_percentage']));
	}

	$school_commission_percentage  = get_option('school_commission_percentage');
	$website_commission_percentage = get_option('website_commission_percentage');

	$output = '<div class="wrap">
		<h2>Inställning av provision</h2>
		<div class="form-fields">
			<form method="post" action="">
				<div class="table-responsive">
					<table class="form-table" role="presentation">
						<tr class="form-field">
							<th><label for="school_commission_percentage">Provision: </label> <small>i procent(%)</small></th>
							<td>
								<input style="width: 300px;" type="text" id="school_commission_percentage" name="school_commission_percentage" required value="'. ( !empty($school_commission_percentage) ? $school_commission_percentage : 0 ) .'">
							</td>
						</tr>
						<tr class="form-field">
							<th><label for="website_commission_percentage">Webbplatskommission: </label> <small>i procent(%)</small></th>
							<td>
								<input style="width: 300px;" type="text" id="website_commission_percentage" name="website_commission_percentage" required value="'. ( !empty($website_commission_percentage) ? $website_commission_percentage : 0 ) .'">
							</td>
						</tr>
						<tr class="form-field">
							<td style="padding-left: 0;" colspan="2"><input id="submit-class" class="button button-primary" name="submit" type="submit" value="Skicka in"></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
	</div>';

	echo $output;
}

/**
 * @purpose: Display commission menu for school login before logout.
 * 			 Display commission menu for class and seller login depending on access provided by school.
 * @param  : $menu_links (my-account page menu links)
 * @return : (array) $menu_links (Updated links for my-account page)
 * 
 */
function manage_commission_for_users($menu_links)
{
	$logged_in_user = get_userdata(get_current_user_id());
	$roles          = $logged_in_user->roles;
	$logout_index = array_search('customer-logout', array_keys($menu_links));

	if( in_array('wc_product_vendors_manager_vendor', $roles) == true )
	{
		if ($logout_index !== false) {
			$menu_links = array_slice($menu_links, 0, $logout_index, true)
				+ array('skolkommission' => 'Försäljning')
				+ array_slice($menu_links, $logout_index, NULL, true);
		} 
		else
			$menu_links['skolkommission'] = 'Försäljning';
	}

	if( in_array('wc_product_vendors_admin_vendor', $roles) == true )
	{
		$assigned_school       = get_user_meta($logged_in_user->ID, 'assigned_school', true);
		$commission_show_class = get_user_meta($assigned_school, 'commission_show_class', true);

		if( $commission_show_class == '1' )
		{
			if ($logout_index !== false) {
				$menu_links = array_slice($menu_links, 0, $logout_index, true)
					+ array('klass-kommission' => 'Försäljning')
					+ array_slice($menu_links, $logout_index, NULL, true);
			} 
			else
				$menu_links['klass-kommission'] = 'Försäljning';
		}
	}

	if( in_array('children', $roles) == true )
	{
		$assigned_school       = get_user_meta($logged_in_user->ID, 'assigned_school', true);
		$assigned_class        = get_user_meta($logged_in_user->ID, 'assigned_class', true);
		$commission_show_child = get_user_meta($assigned_school, 'commission_show_child', true);

		if( $commission_show_child == '1' )
		{
			if ($logout_index !== false) {
				$menu_links = array_slice($menu_links, 0, $logout_index, true)
					+ array('säljarprovision' => 'Försäljning')
					+ array_slice($menu_links, $logout_index, NULL, true);
			} 
			else
				$menu_links['säljarprovision'] = 'Försäljning';
		}
	}

	return $menu_links;
}

/**
 * @purpose: Rewrite endpoints URL
 * 
 */
function zz_add_frontend_endpoints_commission()
{
	add_rewrite_endpoint( 'skolkommission', EP_PAGES );
	add_rewrite_endpoint( 'skol-klass-kommission', EP_PAGES );
	add_rewrite_endpoint( 'skola-säljarkommission', EP_PAGES );
	add_rewrite_endpoint( 'klass-kommission', EP_PAGES );
	add_rewrite_endpoint( 'säljarprovision', EP_PAGES );
}

/**
 * @purpose: Display total commission and order total for school and view for school commission 
 * 
 */
function school_my_account_manage_commission() 
{
	global $wpdb;
	$output = '';

	$sql = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value = " . get_current_user_id() . "
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	$orderTotal       = $wpdb->get_row($sql);
	$total_order      = !empty($orderTotal->order_total) ? $orderTotal->order_total : 0;
	$total_commission = !empty($orderTotal->total_commission) ? $orderTotal->total_commission : 0;

	$output = '<div class="wrap zg-commision">

		<div class="row">
		<div class="col-md-8">
			<h4>Uppgifter om Provision</h4>
		</div>
		<div class="col-md-4">
			<div class="view-class-child-commission">
				<a href="' . wc_get_account_endpoint_url( 'skol-klass-kommission' ) . '" class="zg-cta-small zg-bordercta">Klass/lag Provision</a>
			</div>
		</div>
		</div>

		<div class="zg-sale-details">
			<span class="total-sale"><b>Total försäljning: kr <span class="total">' . number_format($total_order, 2, ',', '.') . '</span></b></span> &emsp;
			<span class="total-commission ml-auto"><b>Total provision: kr <span class="commission">' . number_format($total_commission, 2, ',', '.') . '</span></b></span>
		</div>

		<div class="date-range-filter" id="landing-page-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
		</div>

		<div class="school-commission-list">
			<div class="table-responsive">
				<table class="table table-striped" id="table-school-commission"> 
					<thead>
						<th valign="middle">Klass / kund</th>
						<th width="15%" valign="middle">Namn</th>
						<th valign="middle">Beställ från</th>
						<th width="20%" valign="middle">E-post</th>
						<th valign="middle">Orderdatum</th>
						<th valign="middle">Ordersumma</th>
						<th valign="middle">Provision (%)</th>
						<th valign="middle">Provisionsbelopp (kr)</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>';

	echo $output;
}

/**
 * @purpose: Display total commission and order total for school and view for school commission 
 * 
 */
function school_class_my_account_manage_commission()
{
	global $wpdb;
	$output = '';

	$sql = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value = " . get_current_user_id() . "
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	$orderTotal       = $wpdb->get_row($sql);
	$total_order      = !empty($orderTotal->order_total) ? $orderTotal->order_total : 0;
	$total_commission = !empty($orderTotal->total_commission) ? $orderTotal->total_commission : 0;

	$output = '<div class="wrap zg-commision">
		<div class="row">
		<div class="col-md-8">
		<h4>Klass/lag Provision</h4>
		</div>
		<div class="col-md-4">
		<div class="view-class-child-commission">
			<a class="redirect-back zg-cta-small zg-bordercta" href="' . wc_get_account_endpoint_url( 'skolkommission' ) . '" class="button button-secondary">Tillbaka</a>
		</div>
		</div>
		</div>

		<div class="zg-sale-details">
			<span class="total-sale"><b>Total försäljning: kr <span class="total">' . number_format($total_order, 2, ',', '.') . '</span></b></span> &emsp;
			<span class="total-commission ml-auto"><b>Total provision: kr <span class="commission">' . number_format($total_commission, 2, ',', '.') . '</span></b></span>
		</div>

		<div class="date-range-filter" id="landing-page-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
		</div>

		<div class="class-commission-list">
			<table class="table table-striped" id="table-class-commission">
				<thead>
					<th valign="middle">Klass Namn</th>
					<th valign="middle">E-post</th>
					<th valign="middle">Total försäljning (kr)</th>
					<th valign="middle">Provisionsbelopp (kr)</th>
					<th valign="middle">Handlingar</thead>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>';

	echo $output;
}

/**
 * @purpose: Display total commission and order total for school based on child linked to school
 * 
 */
function school_child_my_account_manage_commission()
{
	global $wpdb;
	$output = '';

	$sql = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value = " . get_current_user_id() . "
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	$orderTotal       = $wpdb->get_row($sql);
	$total_order      = !empty($orderTotal->order_total) ? $orderTotal->order_total : 0;
	$total_commission = !empty($orderTotal->total_commission) ? $orderTotal->total_commission : 0;

	$class_id = !empty($_GET['class']) ? $_GET['class'] : 0;
	$class_details = get_userdata($class_id);

	$output = '<div class="wrap zg-commision">
		<h4>Säljar Provision (Klass/lagnamn: ' . $class_details->display_name . ')</h4>

		<div class="view-class-child-commission">
			<a class="redirect-back zg-cta-small zg-bordercta" href="' . wc_get_account_endpoint_url( 'skol-klass-kommission' ) . '" class="button button-secondary">Tillbaka</a>
		</div>

		<div class="zg-sale-details">
			<span class="total-sale"><b>Total försäljning: kr <span class="total">' . number_format($total_order, 2, ',', '.') . '</span></b></span> &emsp;
			<span class="total-commission ml-auto"><b>Total provision: kr <span class="commission">' . number_format($total_commission, 2, ',', '.') . '</span></b></span>
		</div>

		<div class="date-range-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
		</div>

		<div class="child-commission-list">
			<table class="table table-striped" id="table-child-commission">
				<thead>
					<th valign="middle">Säljarens namn</th>
					<th valign="middle">E-post</th>
					<th valign="middle">Beställningssumma (kr)</th>
					<th valign="middle">Provision (%)</th>
					<th valign="middle">Provisionsbelopp (kr)</th>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>';

	echo $output;
}


/**
 * @purpose: Show class commission as per child
 * 
 */
function class_my_account_manage_commission()
{
	$output = '<div class="wrap zg-commision">
		<h4>Information om klass/lag Provision</h4>

		<div class="date-range-filter" id="landing-page-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
		</div>

		<div class="class-commission-list">
			<table class="table table-bordered" id="table-class-commission-cs">
				<thead>
					<th valign="middle">Säljarens namn</th>
					<th valign="middle">E-post</th>
					<th valign="middle">Total försäljning (kr)</th>
					<th valign="middle">Provisionsbelopp (kr)</th>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>';

	echo $output;
}


/**
 * @purpose: Show child commission details for seller login
 * 
 */
function child_my_account_manage_commission()
{
	$output = '<div class="wrap zg-commision">
		<h4>Information om säljare Provision</h4>

		<div class="date-range-filter" id="landing-page-filter">
			<label for="start-date">Start datum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
			<label for="end-date">Slutdatum:</label>
			<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
			<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
		</div>

		<div class="child-commission-list">
			<table class="table table-bordered" id="table-ch-commission">
				<thead>
					<th valign="middle">Total försäljning (kr)</th>
					<th valign="middle">Provisionsbelopp (kr)</th>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>';

	echo $output;
}


/**
 * @purpose: AJAX call to get commission details for logged in school
 * @return : (JSON) - with commission details and total
 * 
 */
function get_school_commission_details() 
{
	global $wpdb;
	$data = [];
	$total_commission = $total_order_total = $count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';

	$sql1 = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value = " . get_current_user_id() . "
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	if( !empty($start_date) )
		$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$orderTotal        = $wpdb->get_row($sql1);
	$total_order_total = !empty($orderTotal->order_total) ? $orderTotal->order_total : 0;
	$total_commission  = !empty($orderTotal->total_commission) ? $orderTotal->total_commission : 0;

	$sql = "SELECT p.*
	FROM wp_posts p
	INNER JOIN wp_postmeta m ON p.ID = m.post_id
	WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash')
		AND m.meta_key = 'selected_school_id'
		AND m.meta_value = " . get_current_user_id();

	if( !empty($start_date) )
		$sql .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sql .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$count = $wpdb->get_results($sql);
	$count = $wpdb->num_rows;

	$sql .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

	$orderList = $wpdb->get_results($sql);

	if( !empty($orderList) )
	{
		foreach ($orderList as $key => $orders) 
		{
			$commission = 0;

			$order_commission   = get_post_meta($orders->ID, 'order_commission_percent', true); 
			$spacial_page       = get_post_meta($orders->ID, 'spacial_page', true); 
			$selected_school_id = get_post_meta($orders->ID, 'selected_school_id', true); 

			$order_commission = $order_commission > 0 ? $order_commission : 0;

			$order_details = wc_get_order($orders->ID);
			$order_total = $order_details->get_total();

			if( $order_commission > 0 )
				$commission = ( $order_commission / 100 ) * $order_total;

			$commission = $commission > 0 ? number_format($commission, 2) : 0;
			$order_date = date(DATE_FORMAT, strtotime($order_details->get_date_created()));

			if( $spacial_page )
			{
				$assigned_class = get_user_meta($spacial_page, 'assigned_class', true);
				$class_details = get_userdata($assigned_class);

				$class_cust = 'Klass/lag';
				$name       = $class_details->display_name;
				$order_from = 'Specialsida för säljare';
				$email      = $class_details->user_email;
				$action = '';
			}
			else
			{
				$first_name = !empty($order_details->get_billing_first_name()) ? $order_details->get_billing_first_name() : $order_details->get_shipping_first_name();
				$last_name = !empty($order_details->get_billing_last_name()) ? $order_details->get_billing_last_name() : $order_details->get_shipping_last_name();
				$email = !empty($order_details->get_billing_email()) ? $order_details->get_billing_email() : '';

				$class_cust = 'Kund';
				$name       = $first_name . ' ' . $last_name;
				$order_from = 'Webbshop';
				$action = '';
			}

			$data[$key] = [$class_cust, ( !empty($name) ? $name : '-'), $order_from, ( !empty($email) ? $email : '-'), $order_date, 'kr ' . number_format($order_total, 2, ',', '.'), $order_commission . '%', 'kr ' . number_format($commission, 2, ',', '.')];
		}
	}
	
	$response = array(
		"draw"              => intval($draw),
		"recordsTotal"      => $count,
		"recordsFiltered"   => $count,
		"data"              => $data,
		"total_order_total" => number_format($total_order_total, 2, ',', '.'),
		"total_commission"  => number_format($total_commission, 2, ',', '.'),
	);

	wp_send_json($response);
}

/**
 * @purpose: AJAX call to get commission details for class linked to logged in school
 * @return : (JSON) - with commission details and total
 * 
 */
function get_sc_commission_details()
{
	global $wpdb;
	$data = [];
	$total_school_order = $total_school_commission = $total_website_order = $total_website_commission = $total_order_total = $total_commission = $count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';

	$school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : get_current_user_id();
	$page      = !empty($_POST['page']) ? $_POST['page'] : '';

	$sql = "SELECT DISTINCT u.ID, ( SELECT GROUP_CONCAT(um.user_id) FROM wp_usermeta um WHERE um.meta_key = 'assigned_class' AND um.meta_value = u.ID ) as child_ids 
		FROM wp_users u
		INNER JOIN wp_usermeta m ON u.ID = m.user_id
		WHERE m.meta_key = 'wp_capabilities' 
			AND m.meta_value LIKE '%wc_product_vendors_admin_vendor%'
			AND u.ID IN (
				SELECT user_id
				FROM wp_usermeta
				WHERE meta_key = 'assigned_school' 
				AND meta_value = " . $school_id . "
			)";

	$class_users = $wpdb->get_results($sql);

	if( !empty($class_users) )
	{
		$keys = 0;

		foreach ($class_users as $key => $class) 
		{
			if( !empty($class->child_ids) )
			{
				$sqlTot = "SELECT 
					SUM(m1.meta_value) AS order_total,
					SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
				FROM wp_posts p
				INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
				INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
				INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
				WHERE p.post_type = 'shop_order'
					AND m1.meta_key = '_order_total'
					AND m2.meta_key = 'order_commission_percent'
					AND m3.meta_key = 'spacial_page'
					AND m3.meta_value IN (" . $class->child_ids . ")
					AND m1.meta_value IS NOT NULL
					AND m2.meta_value IS NOT NULL
					AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

				if( !empty($start_date) )
					$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

				if( !empty($end_date) )
					$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

				$orderTotal        = $wpdb->get_row($sqlTot);
				$total_school_order = $total_order_total = !empty($orderTotal->order_total) ? $total_order_total + $orderTotal->order_total : $total_order_total + 0;
				$total_school_commission = $total_commission  = !empty($orderTotal->total_commission) ? $total_commission + $orderTotal->total_commission : $total_commission + 0;


				$sql1 = "SELECT 
					SUM(m1.meta_value) AS order_total,
					SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
				FROM wp_posts p
				INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
				INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
				INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
				WHERE p.post_type = 'shop_order'
					AND m1.meta_key = '_order_total'
					AND m2.meta_key = 'order_commission_percent'
					AND m3.meta_key = 'spacial_page' 
					AND m3.meta_value IN (" . $class->child_ids . ")
					AND m1.meta_value IS NOT NULL
					AND m2.meta_value IS NOT NULL
					AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

				if( !empty($start_date) )
					$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

				if( !empty($end_date) )
					$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

				$order_details = $wpdb->get_row($sql1);

				$class_details    = get_userdata($class->ID);
				$order_total      = !empty($order_details->order_total) ? $order_details->order_total : 0;
				$commission_amt = !empty($order_details->total_commission) ? $order_details->total_commission : 0;

				if( $page == 'wp-class-commission' )
					$action = '<a href="' . site_url() . '/wp-admin/admin.php?page=wp-child-commission&class=' . $class->ID . '" title="Visa Säljarkommission"><i class="fa-solid fa-eye"></i></a>';
				else
					$action = '<a href="' . wc_get_account_endpoint_url( 'skola-säljarkommission' ) . '?class=' . $class->ID . '" title="Visa Säljarkommission"><i class="fa-solid fa-eye"></i></a>';

				$data[$keys] = [$class_details->display_name, $class_details->user_email, 'kr ' . number_format($order_total, 2, ',', '.'), 'kr ' . number_format($commission_amt, 2, ',', '.'), $action];

				$keys++;
			}
		}
	}

	$response = array(
		"draw"                     => intval($draw),
		"recordsTotal"             => count($class_users),
		"recordsFiltered"          => count($class_users),
		"data"                     => $data,
		"total_order_total"        => number_format($total_order_total, 2, ',', '.'),
		"total_commission"         => number_format($total_commission, 2, ',', '.'),
		"total_school_order"       => number_format($total_school_order, 2, ',', '.'),
		"total_school_commission"  => number_format($total_school_commission, 2, ',', '.'),
		"total_website_order"      => number_format($total_website_order, 2, ',', '.'),
		"total_website_commission" => number_format($total_website_commission, 2, ',', '.'),
	);

	wp_send_json($response);
}

/**
 * @purpose: AJAX call to get commission details for child linked to logged in school
 * @return : (JSON) - with commission details and total
 * 
 */
function get_schild_commission_details()
{
	global $wpdb;

	$data = [];
	$total_school_order = $total_website_order = $total_school_commission = $total_website_commission = $total_commission = $total_order_total = $count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$class_id   = !empty($_POST['class_id']) ? $_POST['class_id'] : '';

	$sql = "SELECT GROUP_CONCAT(um.user_id) as user_id FROM wp_usermeta um WHERE um.meta_key = 'assigned_class' AND um.meta_value = " . $class_id;
	$child_lists = $wpdb->get_row($sql);

	$sqlTot = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'spacial_page'
		AND m3.meta_value IN (" . $child_lists->user_id . ")
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	if( !empty($start_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$orderTotal              = $wpdb->get_row($sqlTot);
	$total_school_order      = $total_order_total = !empty($orderTotal->order_total) ? $orderTotal->order_total : 0;
	$total_school_commission = $total_commission  = !empty($orderTotal->total_commission) ? $orderTotal->total_commission : 0;

	if( !empty($child_lists) )
	{
		$sql1 = "SELECT p.*
		FROM wp_posts p
		INNER JOIN wp_postmeta m ON p.ID = m.post_id
		WHERE p.post_type = 'shop_order'
			AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
			AND m.meta_key = 'spacial_page'
			AND m.meta_value IN (" . $child_lists->user_id . ") ";

		if( !empty($start_date) )
			$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

		if( !empty($end_date) )
			$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

		$count = $wpdb->get_results($sql1);
		$count = !empty($count) ? count($count) : 0;

		$sql1 .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

		$orderList = $wpdb->get_results($sql1);

		if( !empty($orderList) ) 
		{
			foreach ($orderList as $key => $orders) 
			{
				$order_total = $commission = 0;

				$order_details = wc_get_order($orders->ID);
				$order_total = $order_details->get_total();

				$order_commission = get_post_meta($orders->ID, 'order_commission_percent', true); 
				$spacial_page     = get_post_meta($orders->ID, 'spacial_page', true); 
				$order_commission = $order_commission > 0 ? $order_commission : 0;

				if( $order_commission > 0 )
					$commission = ( $order_commission / 100 ) * $order_total;

				$commission = $commission > 0 ? number_format($commission, 2) : 0;

				$child_details = get_userdata($spacial_page);

				$data[$key] = [$child_details->display_name, $child_details->user_email, 'kr ' . number_format($order_total, 2, ',', '.'), $order_commission . '%', 'kr ' . number_format($commission, 2, ',', '.')];
			}
		}
	}

	$response = array(
		"draw"                     => intval($draw),
		"recordsTotal"             => $count,
		"recordsFiltered"          => $count,
		"data"                     => $data,
		"total_order_total"        => number_format($total_order_total, 2, ',', '.'),
		"total_commission"         => number_format($total_commission, 2, ',', '.'),
		"total_school_order"       => number_format($total_school_order, 2, ',', '.'),
		"total_school_commission"  => number_format($total_school_commission, 2, ',', '.'),
		"total_website_order"      => number_format($total_website_order, 2, ',', '.'),
		"total_website_commission" => number_format($total_website_commission, 2, ',', '.'),
	);

	wp_send_json($response);
}

/**
 * @purpose: AJAX call to get commission details for logged in seller
 * @return : (JSON) - with commission details and total
 * 
 */
function get_child_commission_detail()
{
	global $wpdb;
	$data = [];
	$count = 1;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';

	$child_id = get_current_user_id();

	$sql1 = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'spacial_page' 
		AND m3.meta_value IN (" . $child_id . ")
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	if( !empty($start_date) )
		$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$orderList = $wpdb->get_row($sql1);

	$order_total = !empty($orderList) && $orderList->order_total > 0 ? $orderList->order_total : 0;
	$total_commission = !empty($orderList) && $orderList->total_commission > 0 ? $orderList->total_commission : 0;

	$data[0] = ['kr ' . number_format($order_total, 2, ',', '.'), 'kr ' . number_format($total_commission, 2, ',', '.')];

	$response = array(
		"draw"            => intval($draw),
		"recordsTotal"    => $count,
		"recordsFiltered" => $count,
		"data"            => $data,
	);

	wp_send_json($response);
}

/**
 * @purpose: AJAX call to get commission details for logged in class
 * @return : (JSON) - with commission details and total
 * 
 */
function get_class_commission_details()
{
	global $wpdb;
	$data = [];
	$count = 1;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';

	$class_id = get_current_user_id();

	$sql = "SELECT um.user_id FROM wp_usermeta um WHERE um.meta_key = 'assigned_class' AND um.meta_value = " . $class_id;
	$child_lists = $wpdb->get_results($sql);

	if( !empty($child_lists) )
	{
		foreach ($child_lists as $key => $child) 
		{
			$child_details = get_userdata($child->user_id);

			$sql1 = "SELECT 
				SUM(m1.meta_value) AS order_total,
				SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
			FROM wp_posts p
			INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
			INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
			INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
			WHERE p.post_type = 'shop_order'
				AND m1.meta_key = '_order_total'
				AND m2.meta_key = 'order_commission_percent'
				AND m3.meta_key = 'spacial_page' 
				AND m3.meta_value IN (" . $child->user_id . ")
				AND m1.meta_value IS NOT NULL
				AND m2.meta_value IS NOT NULL
				AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

			if( !empty($start_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

			if( !empty($end_date) )
				$sql1 .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

			$orderList = $wpdb->get_row($sql1);

			$order_total = !empty($orderList) && $orderList->order_total > 0 ? $orderList->order_total : 0;
			$total_commission = !empty($orderList) && $orderList->total_commission > 0 ? $orderList->total_commission : 0;

			$data[$key] = [$child_details->display_name, $child_details->user_email, 'kr ' . number_format($order_total, 2, ',', '.'), 'kr ' . number_format($total_commission, 2, ',', '.')];
		}
	}

	$response = array(
		"draw"            => intval($draw),
		"recordsTotal"    => count($child_lists),
		"recordsFiltered" => count($child_lists),
		"data"            => $data,
	);

	wp_send_json($response);
}

/**
 * @purpose: Callback function to show view page for commission details
 * 
 */
function render_commission_page()
{
	global $wpdb;

	$school_ids  = [];
	$school_names = [];

	$args = array(
		'role'    => 'wc_product_vendors_manager_vendor',
		'orderby' => 'user_nicename',
		'order'   => 'ASC'
	);
	
	$users = get_users( $args );

	$school_ids = array_map(function ($user) {
		return $user->ID;
	}, $users);

	$school_name = array_map(function ($user) {
		return $user->display_name;
	}, $users);

	$sql = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value IN (" . implode(',', $school_ids) . ")
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL 
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	$schoolOrderTotal        = $wpdb->get_row($sql);
	$school_total_order      = !empty($schoolOrderTotal->order_total) ? $schoolOrderTotal->order_total : 0;
	$school_total_commission = !empty($schoolOrderTotal->total_commission) ? $schoolOrderTotal->total_commission : 0;

	$sql = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	LEFT JOIN wp_postmeta m1 ON p.ID = m1.post_id AND m1.meta_key = '_order_total'
	LEFT JOIN wp_postmeta m2 ON p.ID = m2.post_id AND m2.meta_key = 'order_commission_percent'
	LEFT JOIN wp_postmeta m3 ON p.ID = m3.post_id AND m3.meta_key = 'selected_school_id'
	WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
		AND (m3.meta_key IS NULL OR m3.meta_value IS NULL)
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL
	";

	$websiteOrderTotal       = $wpdb->get_row($sql);
	$website_order_total      = !empty($websiteOrderTotal->order_total) ? $websiteOrderTotal->order_total : 0;
	$website_total_commission = !empty($websiteOrderTotal->total_commission) ? $websiteOrderTotal->total_commission : 0;

	if(isset($_GET['page']) && $_GET['page'] == 'wp-commissions')
	{
		$output = '<div class="wrap zg-commision">
			<h2>Provision</h2>

			<div class="zg-sale-details">
				<div class="row">
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-website-sale">
							<span>Total webbplatsförsäljning:</span> 
							<label> kr <abbr>' . number_format($website_order_total, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-website-commission">
							<span>Total webbplatskommission:</span> 
							<label>kr <abbr>' . number_format($website_total_commission, 2, ',', '.') . '</abbr></label>
						</div> 
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-school-sale">
							<span>Total skola/föreningsförsäljning:</span> 
							<label>kr <abbr>' . number_format($school_total_order, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-school-commission">
							<span>Total skola/föreningskommission:</span> 
							<label>kr <abbr>' . number_format($school_total_commission, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
				</div>
			</div>

			<div class="date-range-filter" id="landing-page-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<label for="commission-type">Kommissionstyp:</label>
				<select id="commission-type">
					<option value="all">Allt</option>
					<option value="website">Hemsida</option>
					<option value="school">Skola/Förening</option>
				</select>
				<label class="school-commission" for="school-commission">Välj Skola/Förening:</label>
				<select class="school-commission">
					<option value="">Välj Skola/Förening</option>';
					
					if( !empty($school_ids) )
					{
						foreach ($school_ids as $key => $school) 
							$output .= '<option value="' . $school . '"> ' . $school_name[$key] . ' </option>';
					}

				$output .= '</select>
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>

			<div class="admin-commission-list">
				<table class="table table-bordered" id="table-admin-commission">
					<thead>
						<th width="14%" valign="middle">Beställ efter Namn</th>
						<th width="18%" valign="middle">E-post</th>
						<th valign="middle">Beställ från</th>
						<th valign="middle">Provision till</th>
						<th valign="middle">Orderdatum</th>
						<th valign="middle">Ordersumma</th>
						<th valign="middle">Provision (%)</th>
						<th width="10%" valign="middle">Provisionsbelopp (kr)</th>
						<th valign="middle">Handlingar</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>';
	}

	if(isset($_GET['page']) && $_GET['page'] == 'wp-class-commission')
	{
		$school_details = get_userdata($_GET['school_id']);

		$output = '<div class="wrap zg-commision">
			<h2>Commission <small>(Skolnamn: ' . $school_details->display_name . ')</small></h2>

			<a class="redirect-back" href="' . site_url() . '/wp-admin/admin.php?page=wp-commissions"> <i class="fa-solid fa-arrow-left"></i> Tillbaka </a>

			<div class="zg-sale-details">
				<div class="row">
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-website-sale">
							<span>Total webbplatsförsäljning:</span> 
							<label>kr <abbr>' . number_format($website_order_total, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-website-commission">
							<span>Total webbplatskommission:</span> 
							<label>kr <abbr>' . number_format($website_total_commission, 2, ',', '.') . '</abbr></label>
						</div> 
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-school-sale">
							<span>Total skola/föreningsförsäljning:</span> 
							<label>kr <abbr>' . number_format($school_total_order, 2, ',', '.') . '</abbr></label>
						</div> 
					</div>
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-school-commission">
							<span>Total skola/föreningskommission:</span> 
							<label>kr <abbr>' . number_format($school_total_commission, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
				</div>
			</div>

			<div class="date-range-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>

			<div class="class-commission-list">
				<table class="table table-bordered" id="table-admin-class-commission">
					<thead>
						<th valign="middle">Klass Namn</th>
						<th valign="middle">E-post</th>
						<th valign="middle">Total försäljning (kr)</th>
						<th valign="middle">Provisionsbelopp (kr)</th>
						<th valign="middle">Handlingar</thead>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>';
	}

	if( isset($_GET['page']) && $_GET['page'] == 'wp-child-commission' )
	{
		$class_id = !empty($_GET['class']) ? $_GET['class'] : 0;
		$class_details = get_userdata($class_id);
		$assigned_school = get_user_meta($class_id, 'assigned_school', true);

		$output = '<div class="wrap zg-commision">
			<h2>Säljar Provision (Klass/lagnamn: ' . $class_details->display_name . ')</h2>

			<a class="redirect-back" href="' . site_url() . '/wp-admin/admin.php?page=wp-class-commission&school_id=' . $assigned_school . '"> <i class="fa-solid fa-arrow-left"></i> Tillbaka </a>

			<div class="zg-sale-details">
				<div class="row">
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-website-sale">
							<span>Total webbplatsförsäljning:</span> 
							<label>kr <abbr>' . number_format($website_order_total, 2, ',', '.') . '</abbr></label>
						</div>
					</div> 
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-website-commission">
							<span>Total webbplatskommission:</span> 
							<label>kr <abbr>' . number_format($website_total_commission, 2, ',', '.') . '</abbr></label>
						</div>
					</div> 
					<div class="col-md-3">
						<div class="zg-saleblock total-sale total-school-sale">
							<span>Total skola/föreningsförsäljning:</span> 
							<label>kr <abbr>' . number_format($school_total_order, 2, ',', '.') . '</abbr></label>
						</div>
					</div> 
					<div class="col-md-3">
						<div class="zg-saleblock total-commission total-school-commission">
							<span>Total skola/föreningskommission:</span> 
							<label>kr <abbr>' . number_format($school_total_commission, 2, ',', '.') . '</abbr></label>
						</div>
					</div>
				</div>
			</div>

			<div class="date-range-filter">
				<label for="start-date">Start datum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="start-date">
				<label for="end-date">Slutdatum:</label>
				<input type="text" placeholder="'. DATE_FORMAT_PLACEHOLDER .'" id="end-date">
				<input type="submit" class="button button-primary" value="Filtrera" id="sort-date-range">
			</div>

			<div class="child-commission-list">
				<table class="table table-bordered" id="table-child-commission">
					<thead>
						<th valign="middle">Säljarens namn</th>
						<th valign="middle">E-post</th>
						<th valign="middle">Beställningssumma (kr)</th>
						<th valign="middle">Provision (%)</th>
						<th valign="middle">Provisionsbelopp (kr)</th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>';
	}

	echo $output;
}

/**
 * @purpose: AJAX call to get commission details received by school or website
 * @return : (JSON) - with commission details and total
 * 
 */
function get_commission_details()
{
	global $wpdb;

	$school_ids = $data = [];
	$total_school_order = $total_website_order = $total_school_commission = $total_website_commission = $count = 0;

	$draw       = !empty($_POST['draw']) ? $_POST['draw'] : 0;
	$start      = !empty($_POST['start']) ? $_POST['start'] : 0;
	$length     = !empty($_POST['length']) ? $_POST['length'] : 10;
	$search     = !empty($_POST['search']) ? $_POST['search'] : [];
	$order      = !empty($_POST['order']) ? $_POST['order'] : [];
	$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : '';
	$end_date   = !empty($_POST['end_date']) ? $_POST['end_date'] : '';
	$type       = !empty($_POST['type']) ? $_POST['type'] : 'all';
	$school_id  = !empty($_POST['school_id']) ? $_POST['school_id'] : 0;

	$sqlTot = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	LEFT JOIN wp_postmeta m1 ON p.ID = m1.post_id AND m1.meta_key = '_order_total'
	LEFT JOIN wp_postmeta m2 ON p.ID = m2.post_id AND m2.meta_key = 'order_commission_percent'
	LEFT JOIN wp_postmeta m3 ON p.ID = m3.post_id AND m3.meta_key = 'selected_school_id'
	WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
		AND (m3.meta_key IS NULL OR m3.meta_value IS NULL)
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL ";

	if( !empty($start_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$websiteOrderTotal        = $wpdb->get_row($sqlTot);

	if( $school_id > 0 )
		array_push($school_ids, $school_id);
	else
	{
		$args = array(
			'role'    => 'wc_product_vendors_manager_vendor',
			'orderby' => 'user_nicename',
			'order'   => 'ASC'
		);

		$users = get_users( $args );

		$school_ids = array_map(function ($user) {
			return $user->ID;
		}, $users);
	}

	$sqlTot = "SELECT 
		SUM(m1.meta_value) AS order_total,
		SUM((m1.meta_value * m2.meta_value / 100)) AS total_commission
	FROM wp_posts p
	INNER JOIN wp_postmeta m1 ON p.ID = m1.post_id
	INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id
	INNER JOIN wp_postmeta m3 ON p.ID = m3.post_id
	WHERE p.post_type = 'shop_order'
		AND m1.meta_key = '_order_total'
		AND m2.meta_key = 'order_commission_percent'
		AND m3.meta_key = 'selected_school_id'
		AND m3.meta_value IN (" . implode(',', $school_ids) . ")
		AND m1.meta_value IS NOT NULL
		AND m2.meta_value IS NOT NULL 
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') ";

	if( !empty($start_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sqlTot .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$schoolOrderTotal        = $wpdb->get_row($sqlTot);

	if( $type == 'school' )
	{
		$total_school_order      = !empty($schoolOrderTotal->order_total) ? $schoolOrderTotal->order_total : 0;
		$total_school_commission = !empty($schoolOrderTotal->total_commission) ? $schoolOrderTotal->total_commission : 0;
	}
	else if( $type == 'website' )
	{
		$total_website_order      = !empty($websiteOrderTotal->order_total) ? $websiteOrderTotal->order_total : 0;
		$total_website_commission = !empty($websiteOrderTotal->total_commission) ? $websiteOrderTotal->total_commission : 0;
	}
	else
	{
		$total_website_order      = !empty($websiteOrderTotal->order_total) ? $websiteOrderTotal->order_total : 0;
		$total_website_commission = !empty($websiteOrderTotal->total_commission) ? $websiteOrderTotal->total_commission : 0;
		$total_school_order       = !empty($schoolOrderTotal->order_total) ? $schoolOrderTotal->order_total : 0;
		$total_school_commission  = !empty($schoolOrderTotal->total_commission) ? $schoolOrderTotal->total_commission : 0;
	}


	if( $type == 'school' )
	{
		$sql = "SELECT p.*
		FROM wp_posts p
		INNER JOIN wp_postmeta m ON p.ID = m.post_id
		WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
		AND m.meta_key = 'selected_school_id'";

		if( $school_id > 0 )
			$sql .= " AND m.meta_value = " . $school_id;
		else
			$sql .= " AND m.meta_value IS NOT NULL ";
	}
	else if( $type == 'website' )
	{
		$sql = "SELECT p.*
		FROM wp_posts p
		LEFT JOIN wp_postmeta m ON p.ID = m.post_id AND m.meta_key = 'selected_school_id'
		WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
		AND (m.meta_key IS NULL OR m.meta_value IS NULL)";
	}
	else
	{
		$sql = "SELECT p.*
		FROM wp_posts p
		LEFT JOIN wp_postmeta m ON p.ID = m.post_id AND m.meta_key = 'selected_school_id'
		WHERE p.post_type = 'shop_order'
		AND p.post_status NOT IN ('wc-cancelled', 'wc-failed', 'trash') 
		AND (m.meta_key IS NULL OR m.meta_value IS NULL OR m.meta_value IS NOT NULL)";
	}

	if( !empty($start_date) )
		$sql .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') >= '" . date(DATE_FORMAT, strtotime($start_date)) . "' ";

	if( !empty($end_date) )
		$sql .= " AND DATE_FORMAT(p.post_date, '%Y-%m-%d') <= '" . date(DATE_FORMAT, strtotime($end_date)) . "' ";

	$count = $wpdb->get_results($sql);
	$count = !empty($count) ? count($count) : 0;

	$sql .= " ORDER BY p.ID DESC LIMIT " . $length . " OFFSET " . $start;

	$orderList = $wpdb->get_results($sql);

	if( !empty($orderList) ) 
	{
		foreach ($orderList as $key => $orders) 
		{
			$commission = 0;

			$order_commission   = get_post_meta($orders->ID, 'order_commission_percent', true); 
			$spacial_page       = get_post_meta($orders->ID, 'spacial_page', true); 
			$selected_school_id = get_post_meta($orders->ID, 'selected_school_id', true); 
			$selected_class_id  = get_post_meta($orders->ID, 'selected_class_id', true); 
			
			$school_details     = get_userdata($selected_school_id);
			$select_class_details      = get_userdata($selected_class_id);

			$order_commission = $order_commission > 0 ? $order_commission : 0;

			$order_details = wc_get_order($orders->ID);
			$order_total = $order_details->get_total();

			if( $order_commission > 0 )
				$commission = ( $order_commission / 100 ) * $order_total;

			$commission = $commission > 0 ? $commission : 0;

			$order_date = date(DATE_FORMAT, strtotime($order_details->get_date_created()));

			$first_name = !empty($order_details->get_billing_first_name()) ? $order_details->get_billing_first_name() : $order_details->get_shipping_first_name();
			$last_name = !empty($order_details->get_billing_last_name()) ? $order_details->get_billing_last_name() : $order_details->get_shipping_last_name();
			$email = !empty($order_details->get_billing_email()) ? $order_details->get_billing_email() : '';
			$name = $first_name . ' ' . $last_name;

			if( $spacial_page )
			{
				$assigned_class = get_user_meta($spacial_page, 'assigned_class', true);
				$class_details  = get_userdata($assigned_class);

				$order_from    = 'Specialsida för säljare';
				$commission_to = $school_details->display_name . ' - ' . $class_details->display_name;
				$action        = '<a href="' . site_url() . '/wp-admin/admin.php?page=wp-class-commission&school_id=' . $selected_school_id . '" title="Se klass-/lagkommission"><i class="fa-solid fa-eye"></i></a>';
			}
			else
			{
				$order_from    = 'Webbshop';
				$commission_to = "Hemsida";
				$action        = '-';
			}

			if( $selected_school_id )
			{
				$commission_to = $school_details->display_name . ' - ' . $select_class_details->display_name;
				$action        = '<a href="' . site_url() . '/wp-admin/admin.php?page=wp-class-commission&school_id=' . $selected_school_id . '" title="Se klass-/lagkommission"><i class="fa-solid fa-eye"></i></a>';
			}

			$data[$key] = [$name, $email, $order_from, $commission_to, $order_date, 'kr ' . number_format($order_total, 2, ',', '.'), $order_commission . '%', 'kr ' . number_format($commission, 2, ',', '.'), $action];
		}
	}

	$response = array(
		"draw"                     => intval($draw),
		"recordsTotal"             => $count,
		"recordsFiltered"          => $count,
		"data"                     => $data,
		"total_school_order"       => number_format($total_school_order, 2, ',', '.'),
		"total_school_commission"  => number_format($total_school_commission, 2, ',', '.'),
		"total_website_order"      => number_format($total_website_order, 2, ',', '.'),
		"total_website_commission" => number_format($total_website_commission, 2, ',', '.'),
	);

	wp_send_json($response);
}

