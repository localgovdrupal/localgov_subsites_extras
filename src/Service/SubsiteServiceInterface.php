<?php

namespace Drupal\localgov_subsites_extras\Service;

use Drupal\node\NodeInterface;

/**
 * Subsite service interface.
 */
interface SubsiteServiceInterface {

  /**
   * Get the subsite homepage node if we're in a subsite.
   *
   * This will only call ::findHomePage() once per request, so it's fine to call
   * from multiple preprocess functions without a performance penalty.
   */
  public function getHomePage(?NodeInterface $node = NULL): ?NodeInterface;

  /**
   * Gets the theme of the current subsite, if there is one.
   *
   * NB that this is not a drupal theme. It's the chosen colour scheme, etc.
   */
  public function getCurrentSubsiteTheme(): ?string;

}
