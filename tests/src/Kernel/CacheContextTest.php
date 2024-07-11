<?php

namespace Drupal\Tests\localgov_subsites_extras\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_subsites_extras\Service\SubsiteServiceInterface;
use Drupal\node\NodeInterface;

/**
 * Tests this module's cache contexts.
 */
class CacheContextTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_subsites_extras',
  ];

  /**
   * Node mock.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Subsite service mock.
   *
   * @var \Drupal\localgov_subsites_extras\Service\SubsiteServiceInterface
   */
  protected $subSiteService;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->node = $this->createMock(NodeInterface::class);
    $this->node->method('id')->willReturn(127);

    $this->subSiteService = $this->createMock(SubsiteServiceInterface::class);
    $this->subSiteService->method('getHomePage')->willReturn($this->node);
    $this->subSiteService->method('getCurrentSubsiteTheme')->willReturn('teal');

    $this->container->set('localgov_subsites_extras.service', $this->subSiteService);
  }

  /**
   * Data provider.
   *
   * Returns cache context name, and the expected value.
   */
  public function cacheContextProvider() {
    yield ['subsites', 127];
    yield ['subsites.homepage', 127];
    yield ['subsites.theme', 'teal'];
  }

  /**
   * Tests cache contexts.
   *
   * @dataProvider cacheContextProvider
   */
  public function testCacheContexts($cacheContext, $value) {

    $cacheContextsManager = $this->container->get('cache_contexts_manager');

    // Check the output from CacheContextsManager::convertTokensToKeys()
    // is what we expect.
    $expectedKey = '[' . $cacheContext . ']=' . $value;
    $this->assertEquals([$expectedKey], $cacheContextsManager->convertTokensToKeys([$cacheContext])->getKeys());
  }

}
