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
    'localgov_subsites_extras'
  ];

  /**
   * Test that we can set up a subsite using this module.
   */
  public function testLoadAdminView() {

    $user = $this->createUser([], 'admintestuser', TRUE);

    $parentNode = Node::create([
      'type' => 'localgov_subsites_overview',
      'title' => $this->randomMachineName(),
      'uid' => $user->id(),
      'status' => 1,
    ]);
    $parentNode->save();

    $parentMenuLink = MenuLinkContent::create([
      'link' => [['uri' => 'entity:node/' . $parentNode->id()]],
      'title' => $parentNode->label(),
      'menu_name' => 'subsites',
      'parent' =>
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
      'parent' => $parentMenuLink->id(),
    ])->save();
  }

}
