<?php

namespace Drupal\instagram_api\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client as GuzzleClient;
use Drupal\Core\Url;

/**
 * Class Client.
 *
 * @package Drupal\instagram_api\Service
 */
class Client {

  use StringTranslationTrait;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Client constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation.
   */
  public function __construct(ConfigFactory $config,
                              CacheBackendInterface $cacheBackend,
                              TranslationInterface $stringTranslation) {
    // Get the config.
    $this->config = $config->get('instagram_api.settings');

    // Cache Backend.
    $this->cacheBackend = $cacheBackend;

    // String Translation.
    $this->stringTranslation = $stringTranslation;

    $this->api_base_uri = $this->config->get('api_base_uri');
    $this->api_cache_maximum_age = $this->config->get('api_cache_maximum_age');
    $this->access_token = $this->config->get('access_token');

    $this->guzzleClient = new GuzzleClient([
      'base_uri' => $this->api_base_uri,
    ]);

  }

  /**
   * Instagram request.
   *
   * @param string $method
   *   Method to call.
   * @param array $args
   *   Args to request.
   * @param bool $cacheable
   *   Is it cachable.
   *
   * @return bool|array
   *   Either an array or false.
   */
  public function request($method, array $args, $cacheable = TRUE) {
    // Build the arg_hash.
    $args = $this->buildArgs($args, $method);
    $argHash = $this->buildArgHash($args);

    // $response = &drupal_static(__FUNCTION__);
    // // Can be replaced with the `__METHOD__`.
    $cid = 'instagram_api:' . md5($argHash);

    // Check cache.
    if ($cache = $this->cacheBackend->get($cid)) {
      $response = $cache->data;

      // Return result from cache if found.
      return $response;
    }
    // No cache. Do it the hard way.
    else {
      // TODO Implement try catch.
      $response = $this->doRequest($method, $args);
      if ($response) {
        // Cache the response if we got one.
        if ($this->api_cache_maximum_age != 0 && $cacheable == TRUE) {
          $this->cacheBackend->set($cid, $response, time() + $this->api_cache_maximum_age);
        }

        // Return result from source if found.
        return $response;
      }
    }

    // Tough luck, no results mate.
    return FALSE;
  }

  /**
   * Build default args.
   *
   * @param array $args
   *   Args to request.
   * @param string $method
   *   Method to call.
   * @param string $format
   *   Format to request.
   *
   * @return array
   *   Return the args array.
   */
  private function buildArgs(array $args, $method, $format = 'json') {
    // Add in additional parameters then sort them for signing.
    $args['access_token'] = $this->access_token;
    ksort($args);

    return $args;
  }

  /**
   * Build Hash from Args array.
   *
   * @param array $args
   *   Args to request.
   *
   * @return string
   *   Return string.
   */
  private function buildArgHash(array $args) {
    // Build an argument hash API signing (we'll also use it for the cache id).
    $argHash = '';

    foreach ($args as $k => $v) {
      $argHash .= $k . $v;
    }

    return $argHash;
  }

  /**
   * Guzzle request for Instagram.
   *
   * @param string $url
   *   Url.
   * @param array $parameters
   *   Parameters.
   * @param string $requestMethod
   *   Request method.
   *
   * @return bool|array
   *   False or array.
   */
  private function doRequest($url, array $parameters = [], $requestMethod = 'GET') {
    if ($this->access_token == "") {
      $msg = $this->t('Instagram API Access Token is not set. It can be set on the <a href=":config_page">configuration page</a>.',
        [':config_page' => Url::fromRoute('instagram_api.settings')]
      );

      drupal_set_message($msg, 'error');
      return FALSE;
    }

    $response = $this->guzzleClient->request($requestMethod, $url, ['query' => $parameters]);

    // TODO Error checking can be improved.
    if ($response->getStatusCode() == !200) {
      return FALSE;
    }

    // TODO Add some checking.
    $body = $response->getBody();

    // TODO Add some checking.
    return json_decode((string) $body, TRUE);
  }

}
