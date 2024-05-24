<?php

namespace Drupal\Tests\localgov_subsites_extras\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
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
    'localgov_guides',
  ];

  /**
   * Creates a menu link to the given node in the subsites menu.
   */
  protected function createMenuLinkForNode($node, $parentLink = null) {
    $properties = [
      'link' => [['uri' => 'entity:node/' . $node->id()]],
      'title' => $node->label(),
      'menu_name' => 'subsites',
    ];
    if ($parentLink instanceof MenuLinkContent) {
      $properties['parent'] = 'menu_link_content:' . $parentLink->uuid();
    }
    $menuLink = MenuLinkContent::create($properties);
    $menuLink->save();
    return $menuLink;
  }

  /**
   * Test that we can set up a subsite using this module.
   *
   * Structure is a single subsite page under a subsite overview.
   */
  public function testSubsitePage() {

    // "theme_a" is the only value in a fresh install of localgov_subsites.
    $parentNode = $this->createNode([
      'type' => 'localgov_subsites_overview',
      'localgov_subsites_theme' => 'theme_a',
    ]);
    $parentMenuLink = $this->createMenuLinkForNode($parentNode);

    $childNode = $this->createNode([
      'type' => 'localgov_subsites_page',
    ]);
    $this->createMenuLinkForNode($childNode, $parentMenuLink);

    $this->drupalGet('/node/' . $childNode->id());

    // Check the class for the color scheme is on the body of the child node.
    $this->assertSession()->elementAttributeContains('xpath', '/body', 'class', 'subsite-extra--color-theme_a');
  }

  /**
   * Test that we can set up a guide in a subsite using this module.
   *
   * Structure is a single guide page under a guide overview under a subsite
   * overview.
   */
  public function testGuidePage() {

    // "theme_a" is the only value in a fresh install of localgov_subsites.
    $subsiteNode = $this->createNode([
      'type' => 'localgov_subsites_overview',
      'localgov_subsites_theme' => 'theme_a',
    ]);
    $subsiteMenuLink = $this->createMenuLinkForNode($subsiteNode);

    $guideOverviewNode = $this->createNode([
      'type' => 'localgov_guides_overview',
    ]);
    $this->createMenuLinkForNode($guideOverviewNode, $subsiteMenuLink);

    $guidePageNode = $this->createNode([
      'type' => 'localgov_guides_page',
      'localgov_guides_parent' => $guideOverviewNode->id(),
    ]);

    $this->drupalGet('/node/' . $guidePageNode->id());

    // Check the class for the color scheme is on the body of the child node.
    $this->assertSession()->elementAttributeContains('xpath', '/body', 'class', 'subsite-extra--color-theme_a');
  }
}
