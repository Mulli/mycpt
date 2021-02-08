Mycpt plugin.

Support for Yahav/Digma project.

Currently running with digmame, celestial themse which can affect ACF.

Mycpt (also MBO) responsibilities:
1. Generate all post types:
							"referrals" => "referral",
							"families" => "family",
							"parents" => "parent",
							"kids" => "kid",
							"services"=>"service",
							"workplans"=>"workplan",
							"meetings" => "meeting",
							"team" => "member"

							Note: weekly that is defines - IS NOT in this list

2. USING ACF to generate field groups
2.1 Install acf, acfpro
2.2 Define all you need in field groups, for each cpt per your design
2.3 Export to .php files to your plugin
2.4 You can forget (deactivate!) acf, acfpro...

2. All get/set rest api interface (not yet working!!!)
Referrals - handles all family referrals
Families  - WILL handle all aspects of handling a family
					initialized from Referral
					will have General Info - NOT A POST TYPE
					AND will include those post types:
							"parents" => "parent",
							"kids" => "kid",
							"services"=>"service",
							"workplans"=>"workplan",
							"meetings" => "meeting",

Major open issues:
1. Rest API update the fields & field groups in the post types
2. No need for a post for each type which is a form-group

Can I generate with new theme & plugin (or just a theme???!!!) & ACF, ACF PRO
1. All the form groups 
2. 4 post types referrals & families & team members & services