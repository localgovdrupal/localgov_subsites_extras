<?php

declare(strict_types=1);

namespace Drupal\localgov_subsites_extras\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Subsite service.
 */
class SubsiteService implements SubsiteServiceInterface {

  /**
   * Subsite homepage.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  private ?NodeInterface $subsiteHomePage;

  /**
   * Searched flag.
   *
   * @var bool
   */
  private bool $searched = FALSE;

  /**
   * Subsite content types.
   *
   * @var array|null
   */
  private ?array $subsiteTypes = [];

  public function __construct(
    private ConfigFactory $configFactory,
    private EntityTypeManagerInterface $entityTypeManager,
    private MenuLinkManagerInterface $menuLinkManager,
    private ModuleHandlerInterface $moduleHandler,
    private RouteMatchInterface $routeMatch,
  ) {}

  /**
   * {@inheritDoc}
   */
  public function getHomePage(?NodeInterface $node = NULL): ?NodeInterface {

    if ($this->searched === FALSE) {
      $this->subsiteHomePage = $this->findHomePage($node);
      $this->searched = TRUE;
    }

    return $this->subsiteHomePage;
  }

  /**
   * {@inheritDoc}
   */
  public function getCurrentSubsiteTheme(): ?string {

    $themeField = $this->configFactory->get('localgov_subsites_extras.settings')->get('theme_field');

    // If the current node is part of a subsite, $subsiteHomePage will be the
    // subsite's homepage node. If it's not, it'll be null.
    $subsiteHomePage = $this->getHomePage();
    if ($subsiteHomePage instanceof NodeInterface) {
      return $subsiteHomePage->get($themeField)->value;
    }

    return NULL;
  }

  /**
   * Is the given node a subsite root node?
   */
  private function isSubsiteType(NodeInterface $node): bool {
    return in_array($node->bundle(), $this->subsiteTypes, strict: TRUE);
  }

  /**
   * Walks up the menu tree to look for a subsite homepage node.
   */
  private function walkMenuTree(MenuLinkInterface $menuLink): ?NodeInterface {

    // Get the node associated with this menu link if there is one.
    // If there is one and it's a subsite homepage, we're done.
    $node = $this->loadNodeForMenuLink($menuLink);
    if (($node instanceof NodeInterface) && $this->isSubsiteType($node)) {
      return $node;
    }

    // Otherwise, get the parent link of the current link and try again.
    $parentMenuLinkID = $menuLink->getParent();
    if ($parentMenuLinkID !== '') {
      $parentMenuLink = $this->menuLinkManager->createInstance($parentMenuLinkID);
      if ($parentMenuLink instanceof MenuLinkInterface) {
        return $this->walkMenuTree($parentMenuLink);
      }
    }

    return NULL;
  }

  /**
   * Loads the node for the supplied menu link ID.
   */
  private function loadNodeForMenuLink($menuLink): ?NodeInterface {
    $pluginDefinition = $menuLink->getPluginDefinition();

    if (isset($pluginDefinition['route_parameters']['node'])) {
      $node_id = $pluginDefinition['route_parameters']['node'];
      // Load the nodes we found.
      $node = $this->entityTypeManager
        ->getStorage('node')
        ->load($node_id);

      return $node;
    }

    return NULL;
  }

  /**
   * Loads the menu link for the supplied node.
   */
  private function loadMenuLinkForNode(NodeInterface $node): ?MenuLinkInterface {
    $result = $this->menuLinkManager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    if ($result !== []) {
      return reset($result);
    }

    return NULL;
  }

  /**
   * Get the subsite homepage node if we're in a subsite.
   */
  private function findHomePage(?NodeInterface $node = NULL): ?NodeInterface {

    // If a node wasn't passed in, use the current node, if there is one.
    if (!$node instanceof NodeInterface) {
      $node = $this->routeMatch->getParameter('node');

      // This needs to happen on the preview page instead.
      if (!$node instanceof NodeInterface) {
        $node = $this->routeMatch->getParameter('node_preview');
      }
    }

    $this->moduleHandler->alter('localgov_subsites_extras_current_node', $node);

    if (!$node instanceof NodeInterface) {
      return NULL;
    }

    $subsiteTypes = $this->configFactory->get('localgov_subsites_extras.settings')->get('subsite_types');
    if (is_array($subsiteTypes)) {
      $this->subsiteTypes = $subsiteTypes;
    }

    $menuLink = $this->loadMenuLinkForNode($node);
    if ($menuLink instanceof MenuLinkInterface) {
      return $this->walkMenuTree($menuLink);
    }

    return NULL;
  }

}
