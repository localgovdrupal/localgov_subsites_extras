# LocalGov Menu Subsites

There's currently no UI for settings, so to configure this:

```
$config['localgov_menu_subsites.settings'] = [
  'subsite.types' => ['localgov_subsites_overview', lbhf_subsite_homepage'],
  'theme.field' => 'field_subsite_color',
];
```
The code currently assumes that field_subsite_color is of a type that has 
'value' as a key. EG a string list field.
