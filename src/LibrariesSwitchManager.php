<?php

namespace Drupal\libraries_switch;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;

/**
 * Class LibrariesSwitchManager.
 *
 * @package Drupal\libraries_switch
 */
class LibrariesSwitchManager extends DefaultPluginManager implements PluginManagerInterface, LibrariesSwitchManagerInterface {

  /**
   * The key for alter info, cache key, cache tags, and ...
   *
   * @var string
   */
  const KEY = 'libraries_switch';

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a LibrariesSwitchManager object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    $this->alterInfo(self::KEY . '_info');
    $this->themeHandler = $theme_handler;
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, self::KEY, [self::KEY]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new LibrariesSwitchYamlDiscovery(self::KEY, $this->themeHandler->getThemeDirectories());
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE, $extension = NULL) {
    // Fetch definitions if they're not loaded yet.
    if (!isset($this->definitions)) {
      $this->getDefinitions();
    }

    return $this->doGetDefinition($this->definitions, $plugin_id, $exception_on_invalid, $extension);
  }

  /**
   * {@inheritdoc}
   */
  protected function doGetDefinition(array $definitions, $plugin_id, $exception_on_invalid, $extension = NULL) {
    if (!isset($extension)) {
      return parent::doGetDefinition($definitions, $plugin_id, $exception_on_invalid);
    }

    if (isset($definitions[$extension]) && !isset($plugin_id)) {
      return $definitions[$extension];
    }
    if (isset($definitions[$extension]) && isset($plugin_id)) {
      return parent::doGetDefinition($definitions[$extension], $plugin_id, $exception_on_invalid);
    }
    elseif (!$exception_on_invalid) {
      return NULL;
    }

    $valid_ids = implode(', ', array_keys($definitions));
    throw new PluginNotFoundException($plugin_id, sprintf('The "%s" extension does not exist. Valid plugin IDs for %s are: %s', $extension, static::class, $valid_ids));
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLibraries(string $extension) {
    $definition = $this->getDefinition(NULL, FALSE, $extension);
    $settings = array_filter(theme_get_setting(self::KEY, $extension) ?? []);
    return ($definition && $settings) ? array_intersect_key($definition, $settings) : [];
  }

}
