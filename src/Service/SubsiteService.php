<?php

declare(strict_types=1);

namespace Drupal\localgov_subsites_extras\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

/**
 * Subsite service.
 */
class SubsiteService {

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

  /**
   * Theme field.
   *
   * @var string|null
   */
  private ?string $themeField;

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private MenuLinkManagerInterface $menuLinkService,
    private RouteMatchInterface $routeMatch,
    private ConfigFactory $configFactory,
    private ModuleHandlerInterface $moduleHandler,
  ) {}

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

  /**
   * Gets the theme of the current subsite, if there is one.
   *
   * NB that this is not a drupal theme. It's the chosen colour scheme, etc.
   */
  public function getCurrentSubsiteTheme(): ?string {

    $this->themeField = $this->configFactory->get('localgov_subsites_extras.settings')->get('theme_field');

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
  private function walkMenuTree(NodeInterface $node): ?NodeInterface {

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
  private function loadNodeForMenuLink($menuLinkContentID): ?NodeInterface {
    $menuLink = $this->menuLinkService->createInstance($menuLinkContentID);
    $pluginDefinition = $menuLink->getPluginDefinition();

    if (!empty($pluginDefinition['route_parameters']['node'])) {
      $node_id = $pluginDefinition['route_parameters']['node'];
      // Load the nodes we found.
      /** @var \Drupal\node\NodeInterface $node */
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

    $subsiteHomePage = NULL;
    if ($node instanceof NodeInterface) {
      $subsiteTypes = $this->configFactory->get('localgov_subsites_extras.settings')
        ->get('subsite_types');
      if (is_array($subsiteTypes)) {
        $this->subsiteTypes = $subsiteTypes;
      }

      $subsiteHomePage = $this->walkMenuTree($node);

      // @todo Move this out to an event or hook or something.
      if (empty($subsiteHomePage) && $node->getType() === 'localgov_directories_page') {
        /** @var \Drupal\node\NodeInterface $directoryChannel */
        $directoryChannel = $node->localgov_directory_channels->entity;
        $subsiteHomePage = $this->walkMenuTree($directoryChannel);
      }
    }

    $this->moduleHandler->alter('localgov_subsites_extras_homepage', $subsiteHomePage);

    return $subsiteHomePage;
  }

}
