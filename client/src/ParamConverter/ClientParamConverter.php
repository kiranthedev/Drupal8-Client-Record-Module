<?php

namespace Drupal\client\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;
use Drupal\client\ClientStorage;

/**
 * Param converter for url param of type {client}.
 */
class ClientParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!ClientStorage::exists($value)) {
      return 'invalid';
    }
    return ClientStorage::load($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'client');
  }

}
