<?php

namespace Drupal\libraries_switch;

/**
 * Interface LibrariesSwitchManagerInterface.
 *
 * @package Drupal\libraries_switch
 */
interface LibrariesSwitchManagerInterface {

  /**
   * Get active libraries.
   *
   * @param string $extension
   *   The extension.
   *
   * @return array
   */
  public function getActiveLibraries(string $extension);

}
