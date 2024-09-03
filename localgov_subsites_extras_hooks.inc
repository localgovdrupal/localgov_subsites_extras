<?php

/**
 * @file
 * Hooks that we're temporarily implementing on behalf of other modules.
 *
 * Ideally, we want these hook implementations in their respective modules. For
 * the moment, we've implemented them here, and when this module is heading
 * towards a proper release, we'll open PRs to move them. Each hook is wrapped
 * in function_exists, so that the hook can exist in both modules temporarily.
 */

use Drupal\node\NodeInterface;

if (!function_exists('localgov_directories_localgov_subsites_extras_current_node_alter')) {

  /**
   * Implements hook_localgov_subsites_extras_current_node_alter().
   */
  function localgov_directories_localgov_subsites_extras_current_node_alter(?NodeInterface &$node) {
    if ($node instanceof NodeInterface && $node->hasField('localgov_directory_channels')) {
      $directoryChannel = $node->localgov_directory_channels->entity;
      if ($directoryChannel instanceof NodeInterface) {
        $node = $directoryChannel;
      }
    }
  }

}

if (!function_exists('localgov_guides_localgov_subsites_extras_current_node_alter')) {

  /**
   * Implements hook_localgov_subsites_extras_current_node_alter().
   */
  function localgov_guides_localgov_subsites_extras_current_node_alter(?NodeInterface &$node) {
    if ($node instanceof NodeInterface && $node->hasField('localgov_guides_parent')) {
      $guideParent = $node->localgov_guides_parent->entity;
      if ($guideParent instanceof NodeInterface) {
        $node = $guideParent;
      }
    }
  }

}
