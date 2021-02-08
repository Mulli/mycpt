<?php
/* rest api interface */
// Now expose the custom post types to the REST API
add_action('rest_api_init', 'register_product_rest');
function register_product_rest() {
  register_rest_field( 'acme_product',
    'product_price',
    array(
      'get_callback'    => 'get_product_cb',
      'schema' => array(
          'description' => __( 'The product price' ),
          'type' => 'integer',
          'context' => array('view', 'edit')
      )
    )
  );
  register_rest_field( 'acme_product',
    'product_spec',
    array(
       'get_callback'    => 'get_product_cb',
       'schema' => array(
          'description' => __( 'The product technical specifications' ),
          'type' => 'string',
          'context' => array('view', 'edit')
      )
    )
  );
}
function get_product_cb( $object, $field_name, $request ) {
  return get_post_meta( $object[ 'id' ], $field_name )[0];
}
?>