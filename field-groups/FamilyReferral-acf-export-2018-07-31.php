if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5b49a61b22b9d',
	'title' => 'הפניות משפחה',
	'fields' => array(
		array(
			'key' => 'field_5b49a634fe225',
			'label' => 'גורם מפנה',
			'name' => 'family_referral_initiator',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'לשכת הרווחה' => 'לשכת הרווחה',
				'עצמאי' => 'עצמאי',
				'אחר' => 'אחר',
			),
			'default_value' => array(
				0 => 'עצמאי',
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b49a684fe226',
			'label' => 'תאריך',
			'name' => 'family_referral_date',
			'type' => 'date_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'display_format' => 'd/m/Y',
			'return_format' => 'd/m/Y',
			'first_day' => 0,
		),
		array(
			'key' => 'field_5b49a6defe227',
			'label' => 'relations',
			'name' => 'family_referral_relations',
			'type' => 'relationship',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
			),
			'taxonomy' => array(
			),
			'filters' => array(
				0 => 'search',
				1 => 'post_type',
				2 => 'taxonomy',
			),
			'elements' => '',
			'min' => '',
			'max' => '',
			'return_format' => 'object',
		),
		array(
			'key' => 'field_5b4cfc1805e8a',
			'label' => 'סטטוס',
			'name' => 'family_referral_status',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'התקבלה פניה אך טרם התבצע בירור' => 'התקבלה פניה אך טרם התבצע בירור',
				'בבירור עם גורם מפנה' => 'בבירור עם גורם מפנה',
				'בבירור עם המשפחה' => 'בבירור עם המשפחה',
				'אחר' => 'אחר',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b4cfc3f05e8b',
			'label' => 'מלווה',
			'name' => 'family_referral_team',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5b5ad379597b7',
			'label' => 'סיבה מרכזית',
			'name' => 'family_referral_reason',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'קשיים בהתנהלות כלכלית שוטפת' => 'קשיים בהתנהלות כלכלית שוטפת',
				'חובות כבדים' => 'חובות כבדים',
				'פשיטת רגל' => 'פשיטת רגל',
				'הכנסות נמוכות' => 'הכנסות נמוכות',
				'אבטלה או תעסוקה לא איכותית' => 'אבטלה או תעסוקה לא איכותית',
				'מצב משפחתי רעוע' => 'מצב משפחתי רעוע',
				'קשיים של ילדים' => 'קשיים של ילדים',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b5ad41b597b8',
			'label' => 'לקוחה של הרווחה',
			'name' => 'family_referral_os_client',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'כן' => 'כן',
				'לא' => 'לא',
				'היתה בעבר' => 'היתה בעבר',
			),
			'default_value' => array(
				0 => 'כן',
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b5eb53bcd99f',
			'label' => 'החלטת מנהלת',
			'name' => 'family_referral_mgr_decision',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'קבלה לתוכנית' => 'קבלה לתוכנית',
				'דחיה' => 'דחיה',
				'המשך בירור' => 'המשך בירור',
				'מעבר לכתף לכתף' => 'מעבר לכתף לכתף',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b5eb5aacd9a0',
			'label' => 'הסבר החלטת מנהלת',
			'name' => 'family_referral_mgr_explain',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
		),
		array(
			'key' => 'field_5b5eb5ffcd9a1',
			'label' => 'החלטת משפחה',
			'name' => 'family_referral_family_decision',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'הצטרפות לתכנית' => 'הצטרפות לתכנית',
				'ויתור' => 'ויתור',
				'נדחתה ע"י התכנית' => 'נדחתה ע"י התכנית',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5b5eb6b3cd9a2',
			'label' => 'הסבר החלטת משפחה',
			'name' => 'family_referral_family_explain',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5b6018d4c33a8',
			'label' => 'קוד',
			'name' => 'family_referral_code',
			'type' => 'number',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => '',
			'max' => '',
			'step' => '',
		),
		array(
			'key' => 'field_5b601904c33a9',
			'label' => 'שם משפחה',
			'name' => 'family_referral_family_name',
			'type' => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5b60192dc33aa',
			'label' => 'אשה - שם פרטי',
			'name' => 'family_referral_woman_firstname',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5b601969c33ab',
			'label' => 'גבר - שם פרטי',
			'name' => 'family_referral_man_firstname',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'digma_referrals',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'digma_families',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array(
		0 => 'the_content',
		1 => 'excerpt',
		2 => 'discussion',
		3 => 'author',
		4 => 'format',
		5 => 'page_attributes',
		6 => 'featured_image',
		7 => 'send-trackbacks',
	),
	'active' => 1,
	'description' => '',
));

endif;