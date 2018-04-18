<?php

namespace Drupal\instagram_api\Service;

/**
 * Class Tags.
 *
 * @package Drupal\instagram_api\Service
 */
class Tags {

  /**
   * Client.
   *
   * @var \Drupal\instagram_api\Service\Client
   */
  protected $client;

  /**
   * Tags constructor.
   *
   * @param \Drupal\instagram_api\Service\Client $client
   *   Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerChannelFactoryInterface.
   */
  public function __construct(Client $client,
                              LoggerChannelFactoryInterface $logger_factory) {
    // Instagram API Client.
    $this->client = $client;
    $this->logger = $logger_factory;
  }

  /**
   * Get information about a tag object.
   *
   * @param $tag
   * Tag for which we need info
   * @param bool $cacheable
   * Cacheable.
   *
   * @return array|bool
   * Response array.
   * https://api.instagram.com/v1/tags/{tag-name}?access_token=ACCESS-TOKEN
   * @see https://www.instagram.com/developer/endpoints/tags/
   */
  public function tagInfo($tag, $cacheable = TRUE) {
    $response = $this->client->request(
      'tags/' . $tag,
      [],
      $cacheable
    );

    if ($response) {
      return $response;
    }

    return FALSE;
  }

  /**
   * Get a list of recently tagged media.
   *
   * @param $tag
   * Tag for which we need info
   * @param bool $args
   * Args, see API docs for options.
   * @param bool $cacheable
   * Cacheable.
   *
   * @return array|bool
   * Response array.
   * https://api.instagram.com/v1/tags/{tag-name}/media/recent?access_token=ACCESS-TOKEN
   * @see https://www.instagram.com/developer/endpoints/tags/
   */
  public function tagMediaRecent($tag, array $args = [], $cacheable = TRUE) {
    $response = $this->client->request(
      'tags/' . $tag . '/media/recent',
      $args,
      $cacheable
    );

    if ($response) {
      return $response;
    }

    return FALSE;
  }


  /**
   * Search for tags by name.
   *
   * @param $query
   * Query to search
   * @param bool $args
   * Args, see API docs for options.
   * @param bool $cacheable
   * Cacheable.
   *
   * @return array|bool
   * Response array.
   * https://api.instagram.com/v1/tags/search?q=snowy&access_token=ACCESS-TOKEN
   * @see https://www.instagram.com/developer/endpoints/tags/
   */
  public function tagSearch($query, $cacheable = TRUE) {
    $response = $this->client->request(
      'tags/search',
      ['q' => $query],
      $cacheable
    );

    if ($response) {
      return $response;
    }

    return FALSE;
  }
}
