<?php

namespace Drupal\localgov_subsites_extras\Cache;

use Drupal\node\NodeInterface;

/**
 * Defines the subsites cache context service.
 *
 * Cache context ID: 'subsites'.
 */
class SubsitesCacheContext extends SubsitesBaseCacheContext {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Subsites");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): string {
    $subsiteHomePage = $this->subsiteService->getHomePage();
    return $subsiteHomePage instanceof NodeInterface ? $subsiteHomePage->id() : '';
  }

}
