<?php
/*
Plugin Name: Category Link Manager
Version: 0.1
Description: Adds admin panel to choose which categories appear in the site navigation.
Author: Garrett Murphey
Author URI: http://gmurphey.com/
Plugin URI: http://gmurphey.com/2006/10/15/wordpress-plugin-category-link-manager/
*/

$gdmAllCats = get_all_category_ids();
if (!get_option('gdm_excluded_categories'))
	gdm_cat_links_activate();
	
function gdm_list_selected_cats($query = '') {
	wp_list_cats(gdm_cat_links_rebuild_query($query));
}

function gdm_cat_links_rebuild_query($query = '') {
	$excludedCats = get_option('gdm_excluded_categories');
	parse_str($query, $queryPieces);
	if (isset($queryPieces['exclude'])) {
		$manualExcludes = explode(',', $queryPieces['exclude']);
		foreach ($manualExcludes as $id)
			$excludedCats[] = $id;
		sort($excludedCats);
	}
	$queryPieces['exclude'] = implode(',', $excludedCats);
	$queryString = '';
	foreach ($queryPieces as $key => $value) {
		$queryString .= $key . '=' . $value . '&';
	}
	return rtrim($queryString, '&');
}

function gdm_cat_links_management_form() {
	global $gdmAllCats;	
	$excludedCats = get_option('gdm_excluded_categories');
	?>
	<div class="wrap">
	<h2>Manage Category Links</h2>
	<p><b>Note:</b> You must be using gdm_list_selected_cats() in place of wp_list_cats() for these settings to take effect.</p>
	<fieldset class="options">
	<legend>Select the Categories to Include in Site Navigation</legend>
	<form action="" method="post">
		<?php
		foreach ($gdmAllCats as $c) {
			$cat = get_category($c);
			?>
			<p><input type="checkbox" name="includedCats[]" value="<?php echo $cat->cat_ID; ?>"<?php if (!in_array($cat->cat_ID, $excludedCats)) { ?> checked<?php } ?> /> <?php echo $cat->cat_name; ?></p>
			<?php
		}
		?>
		<input type="submit" name="gdm_submit" value="Update Category List" />
	</form>
	</fieldset>
	</div>
	<?php
}

function gdm_cat_links_management() {
	global $gdmAllCats;
	if (empty($_POST['gdm_submit'])) {
		gdm_cat_links_management_form();
	} else {
		$excludedCats = array_diff($gdmAllCats, $_POST['includedCats']);
		update_option('gdm_excluded_categories', $excludedCats);
		?><div id="message" class="updated fade"><p><strong>Category Links Updated.</strong></p></div><?php
		gdm_cat_links_management_form();
	}
}

function gdm_add_category_link_manager_admin_pages() {
	add_management_page('Category Links', 'Category Links', 5, __FILE__, 'gdm_cat_links_management');
}

function gdm_cat_links_activate() {
	if (!get_option('gdm_excluded_categories'))
		add_option('gdm_excluded_categories', array());
}

function gdm_cat_links_deactivate() {
	delete_option('gdm_excluded_categories');
}

add_action('admin_menu', 'gdm_add_category_link_manager_admin_pages');
add_action('activate_category_links_manager.php', 'gdm_cat_links_activate');
add_action('deactivate_category_links_manager.php', 'gdm_cat_links_deactivate');
?>