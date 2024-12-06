<?php
/*
Plugin Name: Säljarens specialsida
Description: Lägg till alternativ för barnprofil för att kopiera och dela sidlänk
Version: 1.0
Author: Mahesh Sharma
*/

// Action hooks and filters
add_action( 'show_user_profile', 'child_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'child_custom_user_profile_fields' );

/**
 * @purpose: User profile page for seller login to add option to copy special page link for sharing and purchasing orders.
 * @param  : $user
 * 
 */
function child_custom_user_profile_fields( $user ) 
{
	if( in_array('children', $user->roles) == true )
	{
		$child_id   = get_userdata(get_current_user_id());
		$page_link  = get_permalink(1085);
		$append_url = base64_encode($child_id->ID . '&' . $child_id->display_name);
		$share_link = $page_link . '?param=' . $append_url;

		$output = '<div class="child-special-page" style="margin-top: 30px;">
			<h4>Dela sidans URL</h4>
			<a id="linkToCopy" value="' . $share_link . '" href="' . $share_link . '" target="_blank"> ' . $share_link . ' </a> &emsp;<span class="copy-link" id="copyButton"><i style="font-size: 24px;" class="fa-solid fa-copy"></i></span>
		</div>';

		echo $output;
	}

	?>

	<script type="text/javascript">
		document.getElementById("copyButton").addEventListener("click", function() {
			var linkToCopy = document.getElementById("linkToCopy");
			var linkValue = linkToCopy.getAttribute("href");
			var tempInput = document.createElement("input");
			document.body.appendChild(tempInput);
			tempInput.value = linkValue;
			tempInput.select();

			document.execCommand("copy");
			document.body.removeChild(tempInput);

			alert("Länken har kopierats till urklipp");
		});
	</script>

	<?php 
}


/**
 * @purpose: Show seller special page link on woocommerce my-account dashboard
 * 
 */
function my_account_show_special_page()
{
	$child_id   = get_userdata(get_current_user_id());

	if( in_array('children', $child_id->roles) == true )
	{
		$page_link  = get_permalink(1085);
		$append_url = base64_encode($child_id->ID . '&' . $child_id->display_name);
		$share_link = $page_link . '?param=' . $append_url;

		$output = '<div class="child-special-page" style="margin-top: 30px;">
			<h4>Dela sidans URL</h4>
			<a id="linkToCopy" value="' . $share_link . '" href="' . $share_link . '" target="_blank"> ' . $share_link . ' </a> &emsp;<span class="copy-link" id="copyButton"><i style="font-size: 24px;" class="fa-solid fa-copy"></i></span>
		</div>';
		
		echo $output; ?>

		<script type="text/javascript">
			document.getElementById("copyButton").addEventListener("click", function() {
				var linkToCopy = document.getElementById("linkToCopy");
				var linkValue = linkToCopy.getAttribute("href");
				var tempInput = document.createElement("input");
				document.body.appendChild(tempInput);
				tempInput.value = linkValue;
				tempInput.select();

				document.execCommand("copy");
				document.body.removeChild(tempInput);

				alert("Länken har kopierats till urklipp");
			});
		</script>

		<?php 
	}
}


function add_custom_url_to_my_account_menu($menu_links) {
	$roles        = wp_get_current_user()->roles;
	$logout_index = array_search('customer-logout', array_keys($menu_links));
	$child_id     = get_userdata(get_current_user_id());

    // Add a custom link with the key 'custom-link' and the link text 'Custom Link'
    if( in_array('children', $roles) == true )
	{
		$page_link  = get_permalink(1085);
		$append_url = base64_encode($child_id->ID . '&' . $child_id->display_name);
		$share_link = $page_link . '?param=' . $append_url;

		if ($logout_index !== false) {
			$menu_links = array_slice($menu_links, 0, $logout_index, true)
				+ array('custom-link' => 'Lägg beställning')
				+ array_slice($menu_links, $logout_index, NULL, true);

			echo '<script>
			    jQuery(document).ready(function($) {
			        $("li.woocommerce-MyAccount-navigation-link--custom-link a").on("click", function(e) {
			            e.preventDefault();
			            window.open("' . esc_url($share_link) . '", "_blank");
			        });
			    });
			</script>';
    	}
    }

    return $menu_links;
}
add_filter('woocommerce_account_menu_items', 'add_custom_url_to_my_account_menu');
