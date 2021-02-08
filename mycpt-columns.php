<?php
/* extra columns to the admin page */
// Finally, let's add some extra columns to the admin page for our Products list
// NB: Uses: manage_$post_type_posts_columns  - So replace $post_type with 'acme_product'
add_filter('manage_acme_product_posts_columns', 'product_columns');
function product_columns($columns){
  $columns = array(
    'cb' => '<input type="checkbox" />',
    'title' => 'Product Name',
    'product_price' => 'Price',
    'product_type' => 'Type',
  );
 
  return $columns;
}
// Use: manage_{$post_type}_posts_custom_column 
add_action('manage_acme_product_posts_custom_column',  'products_custom_columns');
function products_custom_columns($column){
  global $post;
 
  switch ($column) {
    case 'product_price':
      $custom = get_post_custom();
      echo $custom['product_price'][0];
      break;
    case 'product_type':
      echo get_the_term_list($post->ID, 'product_type', '', ', ','');
      break;
  }
}

// $digma_cpt_single = ["families" => "family", "parents" => "parent", "kids" => "kid", "workplans"=>"workplan", "team" => "member"];
// 		if ($dpt == "families") add_filter('manage_'. $dpt .'_columns', $dpt.'_columns');

function families_columns($columns) {
	printr($columns);
	$columns['family_name'] = 'Family Name';
	$columns['join_date'] = 'Join Date';
	$columns['os_care'] = 'OS care';
	$columns['responsible_care'] = 'אחראי';
	return $columns;
}
function parents_columns($columns) {
	$columns['views'] = 'Views';
	return $columns;
}
function kids_columns($columns) {
	$columns['views'] = 'Views';
	return $columns;
}
function workplans_columns($columns) {
	$columns['views'] = 'Views';
	return $columns;
}
function team_columns($columns) {
	$columns['views'] = 'Views';
	return $columns;
}