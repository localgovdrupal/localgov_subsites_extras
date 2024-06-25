<?php

namespace Drupal\localgov_subsites_extras\Cache;

/**
 * Defines the subsites theme cache context service.
 *
 * Cache context ID: 'subsites.theme'.
 */
class SubsitesThemeCacheContext extends SubsitesBaseCacheContext {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Subsites theme");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): string {
    return $this->subsiteService->getCurrentSubsiteTheme() ?? '';
  }

}
