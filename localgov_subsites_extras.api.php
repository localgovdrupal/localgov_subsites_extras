<?php

/**
 * @file
 * Hooks provided by the Locagov Subsites Extras module.
 */

use Drupal\node\NodeInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the current node, before subsite determination.
 *
 * This alter hook is called before we walk the menu tree to determine if we're
 * looking at a page in a subsite or not.
 *
 * It'll be passed the current node, acquired from routeMatch. If no node is
 * found there, this hook will be passed null. This gives the opportunity to
 * include non-node pages in a subsite if you wish.
 *
 * If you want to indicate that a subsite is not being viewed, set $node to
 * NULL.
 *
 * Usually, this hook will be used to use a property of the node passed, such as
 * a reference to another node (such as the parent node field for guides,
 * directories, etc) to swop out the current node for a different node, which is
 * in the menu and therefore part of a subsite.
 */
function hook_localgov_subsites_extras_current_node_alter(?NodeInterface &$node) {
  if ($node instanceof NodeInterface && $node->hasField('field_parent')) {
    $parent = $node->field_parent->entity;
    if ($parent instanceof NodeInterface) {
      $node = $parent;
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
