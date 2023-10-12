<?php

namespace Drupal\say_hi\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\say_hi\Greeting;
use Drupal\say_hi\GreetingQueueItem;
use Drupal\say_hi\Greetings;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'say_hi_greetings' queue worker.
 *
 * @QueueWorker(
 *   id = "say_hi_greetings",
 *   title = @Translation("Greetings"),
 *   cron = {"time" = 50}
 * )
 */
class GreetingsSender extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a GreetingsSender object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\say_hi\Greetings $greetings
   *   The greetings service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected Greetings $greetings,
    protected EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('say_hi.greetings'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (!$data instanceof GreetingQueueItem) {
      return;
    }

    $user = $this->entityTypeManager->getStorage('user')->load($data->uid);
    if (!$user instanceof UserInterface) {
      return;
    }

    $this->greetings->sendGreetings(new Greeting($user, $data->name, $data->message));
  }

}
