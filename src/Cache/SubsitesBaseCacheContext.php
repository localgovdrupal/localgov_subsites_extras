<?php

namespace Drupal\localgov_subsites_extras\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\localgov_subsites_extras\Service\SubsiteServiceInterface;

/**
 * Base class for subsites cache contexts.
 */
abstract class SubsitesBaseCacheContext implements CacheContextInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\localgov_subsites_extras\Service\SubsiteServiceInterface $subsiteService
   *   The localgov_subsites_extras.service service.
   */
  public function __construct(protected SubsiteServiceInterface $subsiteService) {
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return new CacheableMetadata();
  }

}
