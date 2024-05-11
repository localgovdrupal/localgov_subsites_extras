<?php

namespace Drupal\Tests\localgov_subsites_extras\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Admin tests for subsites extras.
 */
class AdminViewTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [];

  /**
   * Test that the subsites menu is added to all content types.
   */
  public function testLoadAdminView() {
    // Create an admin user.
    $adminUser = $this->createUser([], 'admintestuser', TRUE);
    $this->drupalLogin($adminUser);

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = $this->container->get('module_installer');

    // Install services first. This tests that this module adds the subsites
    // menu to existing content types.
    $this->assertTrue($moduleInstaller->install(['localgov_services']));
    $this->assertTrue($moduleInstaller->install(['localgov_subsites_extras']));

    // Check that we can add a service landing page to the subsites menu.
    $this->drupalGet('node/add/localgov_services_landing');
    $this->assertSession()->optionExists('edit-menu-menu-parent', 'subsites:');

    // Now install step-by-step. This tests that this module adds the subsites
    // menu to new content types.
    $this->assertTrue($moduleInstaller->install(['localgov_step_by_step']));

    // Check that we can add a service landing page to the subsites menu.
    $this->drupalGet('node/add/localgov_step_by_step_overview');
    $this->assertSession()->optionExists('edit-menu-menu-parent', 'subsites:');
  }

}
