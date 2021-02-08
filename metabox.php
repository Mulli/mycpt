<?php
/* meta boxes and custom fields */
// Add custom fields and meta boxes - eg: Price, Spec, etc.
// add_action('admin_init', 'add_digma_meta_boxes'); 
function add_digma_meta_boxes(){
  global $digma_post_types;

  foreach ($digma_post_types as $dpt) {
    // Show on side of admin add/edit form
    add_meta_box('product_price-meta', 'Product Price', 'meta_box_product_price', 'digma_'.$dpt, 'side', 'low');
    // Show below normal admin add/edit form
    add_meta_box('product_spec-meta', 'Technical Specifications', 'meta_box_product_spec', 'digma_'.$dpt, 'normal', 'low');
  }
}
// callbacks to create the meta boxes
function meta_box_product_price(){
  global $post;
  $custom = get_post_custom($post->ID);
  $product_price = $custom['product_price'][0];
  ?>
  <input name='product_price' value='<?php echo $product_price; ?>' />
  <?php
}
function meta_box_product_spec(){
  global $post;
  $custom = get_post_custom($post->ID);
  $product_spec = $custom['product_spec'][0];
  ?>
  <p><textarea style='width:99%;' cols='500' rows='3' name='product_spec'><?php echo $product_spec; ?></textarea></p>
  <?php
}
// Saves/updates data from our new custom meta boxes
add_action('save_post', 'save_product_info');
function save_product_info(){
  global $post;
  update_post_meta($post->ID, 'product_price', $_POST['product_price']);  // Sanitisation recommended!!
  update_post_meta($post->ID, 'product_spec', $_POST['product_spec']);    // Sanitisation recommended!!
}
?>