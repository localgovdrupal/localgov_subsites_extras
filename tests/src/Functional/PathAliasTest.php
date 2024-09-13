<?php

namespace Drupal\Tests\localgov_subsites_extras\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Path alias pattern tests for subsites extras.
 */
class PathAliasTest extends BrowserTestBase {

  /**
   * Ensures path alias pattern.
   *
   * Checks that the Subsite homepage path alias is prepended to child page path
   * aliases.
   */
  public function testPathAlias() {

    $subsiteHome = $this->getNodeByTitle(self::SUBSITE_HOMEPAGE_TITLE);
    $subsiteChild = $this->getNodeByTitle(self::SUBSITE_CHILDPAGE_TITLE);
    self::assertStringStartsWith($subsiteHome->path->alias, $subsiteChild->path->alias);
  }

  /**
   * Sets up a small subsite.
   */
  protected function setUp(): void {

    parent::setUp();

    $subsiteHome = $this->createNode([
      'type'   => 'localgov_subsites_overview',
      'title'  => self::SUBSITE_HOMEPAGE_TITLE,
      'status' => NodeInterface::PUBLISHED,
    ]);

    $subsiteHomeMenuLink = MenuLinkContent::create([
      'link'      => [['uri' => 'entity:node/' . $subsiteHome->id()]],
      'title'     => $subsiteHome->label(),
      'menu_name' => 'subsites',
    ]);
    $subsiteHomeMenuLink->save();
    $subsiteHome->save();

    $subsiteChild = $this->createNode([
      'type'   => 'localgov_services_page',
      'title'  => self::SUBSITE_CHILDPAGE_TITLE,
      'status' => NodeInterface::PUBLISHED,
      'body' => [
        'summary' => $this->randomString(16),
        'value'   => $this->randomString(64),
      ],
    ]);

    MenuLinkContent::create([
      'link'   => [['uri' => 'entity:node/' . $subsiteChild->id()]],
      'title'  => $subsiteChild->label(),
      'menu_name' => 'subsites',
      'parent'    => 'menu_link_content:' . $subsiteHomeMenuLink->uuid(),
    ])->save();
    $subsiteChild->save();
  }

  const SUBSITE_HOMEPAGE_TITLE  = 'A subsite homepage';
  const SUBSITE_CHILDPAGE_TITLE = 'A subsite childpage';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_subsites',
    'localgov_subsites_extras',
    'localgov_services_page',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
