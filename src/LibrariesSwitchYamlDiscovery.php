<?php

namespace Drupal\libraries_switch;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Class LibrariesSwitchYamlDiscovery.
 *
 * @package Drupal\libraries_switch
 */
class LibrariesSwitchYamlDiscovery extends YamlDiscovery {

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE, $extension = NULL) {
    $definitions = $this->getDefinitions();
    return $this->doGetDefinition($definitions, $plugin_id, $exception_on_invalid, $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return $this->discovery->findAll();
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
  public function hasDefinition($plugin_id, $extension = NULL) {
    if (!isset($extension)) {
      return parent::hasDefinition($plugin_id);
    }

    return (bool) $this->getDefinition($plugin_id, FALSE, $extension);
  }

}
