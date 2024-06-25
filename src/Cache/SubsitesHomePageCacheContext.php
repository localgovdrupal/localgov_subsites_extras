<?php

namespace Drupal\localgov_subsites_extras\Cache;

/**
 * Defines the subsites homepage cache context service.
 *
 * Cache context ID: 'subsites.homepage'.
 */
class SubsitesHomePageCacheContext extends SubsitesCacheContext {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Subsites homepage");
  }

}
