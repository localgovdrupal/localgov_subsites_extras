<?php

declare(strict_types=1);

namespace Drupal\localgov_menu_subsites\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Subsite service.
 */
class SubsiteService {

  // Disable phpcs for a bit, so we don't have to add a load of stuff that's
  // made redundant by type hints.
  // phpcs:disable
  private EntityTypeManagerInterface $entityTypeManager;
  private MenuLinkManagerInterface $menuLinkService;
  private RouteMatchInterface $routeMatch;
  private ConfigFactory $configFactory;
  private ?NodeInterface $subsiteHomePage;
  private bool $searched = false;
  private ?array $subsiteTypes;
  private ?string $themeField;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    MenuLinkManagerInterface $menuLinkService,
    RouteMatchInterface $routeMatch,
    ConfigFactory $configFactory
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->menuLinkService = $menuLinkService;
    $this->routeMatch = $routeMatch;
    $this->configFactory = $configFactory;
  }
  // phpcs:enable

  /**
   * Get the subsite homepage node if we're in a subsite.
   *
   * This will only call ::findHomePage() once per request, so it's fine to call
   * from multiple preprocess functions without a performance penalty.
   */
  public function getHomePage(): ?NodeInterface {

    if ($this->searched === FALSE) {
      $this->subsiteHomePage = $this->findHomePage();
      $this->searched = TRUE;
    }

    return $this->subsiteHomePage;
  }

  public function getCurrentSubsiteTheme() {

    $this->themeField = $this->configFactory->get('localgov_menu_subsites.settings')->get('theme_field');

    // If the current node is part of a subsite, $subsiteHomePage will be the
    // subsite's homepage node. If it's not, it'll be null.
    $subsiteHomePage = $this->getHomePage();
    if ($subsiteHomePage) {
      return $subsiteHomePage->get($this->themeField)->value;
    }

    return NULL;
  }

 /**
  * Is the given node a subsite root node?
  */
  private function isSubsiteType(NodeInterface $node): bool {
    return in_array($node->bundle(), $this->subsiteTypes);
  }

  /**
   * Walks up the menu tree to look for a subsite homepage node.
   */
  private function walkMenuTree(NodeInterface $node) {

    if ($this->isSubsiteType($node)) {
      return $node;
    }

    $result = $this->menuLinkService->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

    if (!empty($result)) {
      $menuLink = reset($result);
      $parentMenuLinkID = $menuLink->getParent();

      if ($parentMenuLinkID) {
        $parentNode = $this->loadNodeForMenuLink($parentMenuLinkID);
        return $this->walkMenuTree($parentNode);
      }
    }
    return NULL;
  }

  /**
   * Loads the node for the supplied menu link ID.
   */
  private function loadNodeForMenuLink($menuLinkContentID) {
    $menuLink = $this->menuLinkService->createInstance($menuLinkContentID);
    $pluginDefinition = $menuLink->getPluginDefinition();

    if (!empty($pluginDefinition['route_parameters']['node'])) {
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

    if (!$node instanceof NodeInterface) {
      return NULL;
    }

    $this->subsiteTypes = $this->configFactory->get('localgov_menu_subsites.settings')->get('subsite_types');

    $subsiteHomePage = $this->walkMenuTree($node);

    // @todo Move this out to an event or hook or something.
    if (empty($subsiteHomePage) && $node->getType() === 'localgov_directories_page') {
      /** @var \Drupal\node\NodeInterface $directoryChannel */
      $directoryChannel = $node->localgov_directory_channels->entity;
      $subsiteHomePage = $this->walkMenuTree($directoryChannel);
    }

    return $subsiteHomePage;
  }

}
