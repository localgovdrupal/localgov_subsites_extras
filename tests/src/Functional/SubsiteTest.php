<?php

namespace Drupal\Tests\localgov_subsites_extras\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for subsites extras.
 */
class SubsiteTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_subsites',
    'localgov_subsites_extras',
  ];

  /**
   * Test that we can set up a subsite using this module.
   */
  public function testLoadAdminView() {

    $user = $this->createUser([], 'admintestuser', TRUE);

    // "theme_a" is the only default value in a fresh install of
    // localgov_subsites.
    $parentNode = Node::create([
      'type' => 'localgov_subsites_overview',
      'title' => $this->randomMachineName(),
      'uid' => $user->id(),
      'status' => 1,
      'localgov_subsites_theme' => 'theme_a',
    ]);
    $parentNode->save();

    $parentMenuLink = MenuLinkContent::create([
      'link' => [['uri' => 'entity:node/' . $parentNode->id()]],
      'title' => $parentNode->label(),
      'menu_name' => 'subsites',
    ]);
    $parentMenuLink->save();

    $childNode = Node::create([
      'type' => 'localgov_subsites_page',
      'title' => $this->randomMachineName(),
      'uid' => $user->id(),
      'status' => 1,
    ]);
    $childNode->save();

    MenuLinkContent::create([
      'link' => [['uri' => 'entity:node/' . $childNode->id()]],
      'title' => $childNode->label(),
      'menu_name' => 'subsites',
      'parent' => 'menu_link_content:' . $parentMenuLink->uuid(),
    ])->save();

    $this->drupalGet('/node/' . $childNode->id());

    // Check the class for the color scheme is on the body of the child node.
    $this->assertSession()->elementAttributeContains('xpath', '/body', 'class', 'subsite-extra--color-theme_a');
  }

}
