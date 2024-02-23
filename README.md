# LocalGov Menu Subsites

1) Enable the module.
2) Add settings. There's currently no UI for this, so you have to add them to settings.php yourself. If you want to use the types and field provided by localgov_subsites, you can add this:
```
$config['localgov_subsites_extras.settings'] = [
  'subsite_types' => ['localgov_subsites_overview'],
  'theme_field' => 'localgov_subsites_theme',
];
```
  The code currently assumes that the theme field is of a type that has
'value' as a key. EG a string list field.

3) If you want to, you can make a new menu for the subsites. You don't have to do this - using Main Navigation works fine, and is what we do on H&F, as it's not used for anything else, but if you are using Main Navigation, making a new menu for subsites will keep things nicely separated out. NB that if you do make a new menu, you'll need to edit every content type that you'd like to be added to subsites to allow the type to be put in the new menu.
4) Add a subsite overview, or whatever type you chose to be your subsite. Choose a theme, and choose to create a menu link in the subsites menu. Save the page.
5) When you view the page, you can inspect the markup, and should see classes 'subsite' and 'color--x' (where x is the theme you chose) applied to the body tag.
6) Create another page. This time, choose to create a menu link and select the page you just created under "Parent link".
7) Again, you can inspect the markup of this new page, and should see classes 'subsite' and 'color--x' on the body tag, picked up from the parent.
9) To set up the menus, place a new menu block. Choose the menu you're using for your subsites when creating the block. Under "Menu levels" set Initial visibility level to 2, and no of levels to display to 1.
10) This module will add a variable into the menu template for the subsite homepage when you're in a subsite. (EG, this is the link styled like a house on https://www.lbhf.gov.uk/celebrating-hf). To make use of this, add this to your menu template: 
```
  {% if subsite_homepage_link %}
    <div class="subsite--menu__title">
      {{ subsite_homepage_link }}
    </div>
  {% endif %}
```
