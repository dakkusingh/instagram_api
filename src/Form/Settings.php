<?php

namespace Drupal\instagram_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements the Instagram api Settings form.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'instagram_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'instagram_api.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('instagram_api.settings');

    $form['client'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Client Settings'),
    ];

    $form['client']['help'] = [
      '#type' => '#markup',
      '#markup' => $this->t('To get your Client ID, you need to register your application on @link.',
        [
          '@link' => Link::fromTextAndUrl('https://www.instagram.com/developer/clients/manage/',
          Url::fromUri('https://www.instagram.com/developer/clients/manage/'))->toString()
        ]),
    ];

    $form['client']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
    ];

    $form['client']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('client_secret'),
    ];

    if ($config->get('client_id') != '' && $config->get('client_secret') != '') {
      $form['client']['access_token'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Access Token'),
        '#default_value' => $config->get('access_token'),
        '#description' => $this->t('To get your Access Token, @link.',
          [
            '@link' => Link::fromTextAndUrl('click here', Url::fromUri($this->accessUrl()))->toString(),
          ]),
      ];
    }


    $form['caching'] = [
      '#type' => 'details',
      '#title' => $this->t('Instagram API Caching'),
      '#open' => TRUE,
      '#description' => $this->t('API caching is recommended for all websites.'),
    ];

    // Identical options to the ones for block caching.
    // @see \Drupal\Core\Block\BlockBase::buildConfigurationForm()
    $period = [
      0,
      60,
      180,
      300,
      600,
      900,
      1800,
      2700,
      3600,
      10800,
      21600,
      32400,
      43200,
      86400,
    ];

    $period = array_map([$this->dateFormatter, 'formatInterval'], array_combine($period, $period));
    $period[0] = '<' . $this->t('no caching') . '>';
    $form['caching']['api_cache_maximum_age'] = [
      '#type' => 'select',
      '#title' => $this->t('API cache maximum age'),
      '#default_value' => $config->get('api_cache_maximum_age'),
      '#options' => $period,
      '#description' => $this->t('The maximum time a API request can be cached by Drupal.'),
    ];

//    $foo2 = \Drupal::service('instagram_api.tags');
    $foo2 = \Drupal::service('instagram_api.tags')->tagMediaRecent('drupal');
    kint($foo2);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('instagram_api.settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('access_token', $form_state->getValue('access_token'))
      ->set('api_cache_maximum_age', $form_state->getValue('api_cache_maximum_age'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Generate the Access Url.
   *
   * @return string
   */
  private function accessUrl() {
    $config = $this->config('instagram_api.settings');
    $redirectUrl = Url::fromUri('internal:/instagram_api/callback', ['absolute' => TRUE])->toString();

    // TODO Fix this Drupal way.

    return $config->get('api_uri') . 'authorize/?client_id=' . $config->get('client_id') . '&response_type=code&redirect_uri=' . $redirectUrl;
  }

}
