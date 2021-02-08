<?php /* acf update values */

// get meta field 'acf' ???
// foreach field - inclucing repeater
// field name is <field_group>_<field_name>
// and maybe <field_group>_<field_name>_<sub_field_name>
//     update field and sub_field
function mbo_update_field_group( $value, $post_id, $field ) {
        if (! empty($value)) {
            foreach ($value as $field_key => $field_value) {
                foreach ( $field['sub_fields'] as $sub_field ) {
                    if ($field_key == $sub_field['key']) {
                        // update field
                        $sub_field_name = $sub_field['name'];
                        $sub_field['name'] = "{$field['name']}_{$sub_field_name}";
                        acf_update_value( $field_value, $post_id, $sub_field );
                        break;
                    }
                }
            }
        }
        return null;
    }