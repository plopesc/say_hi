<?php

namespace Drupal\say_hi\Form;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\say_hi\GreetingQueueItem;
use Drupal\say_hi\Greetings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Say Hello form.
 */
class HiQueueForm extends FormBase {

  /**
   * Constructs a new say hello form.
   *
   * @param \Drupal\say_hi\Greetings $greetings
   *   The greetings service.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory service.
   */
  public function __construct(
    protected Greetings $greetings,
    protected Time $time,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QueueFactory $queueFactory
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('say_hi.greetings'),
      $container->get('datetime.time'),
      $container->get('entity_type.manager'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'say_hi_hi_queue';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => 'test',
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#default_value' => 'test',
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
  $start_time = $this->time->getCurrentMicroTime();
  $queue = $this->queueFactory->get('say_hi_greetings');
  $message = $form_state->getValue('message');
  $name = $form_state->getValue('name');

  $uids = $this->entityTypeManager->getStorage('user')
    ->getQuery()
    ->accessCheck(FALSE)
    ->condition('uid', 0 , '>')
    ->execute();

  array_walk($uids, function (int $uid) use ($name, $message, $queue) {
    $queue->createItem(new GreetingQueueItem($uid, $name, $message));
  });

  $final_time = $this->time->getCurrentMicroTime();
  $memory_peak = memory_get_peak_usage() / 1024 / 1024;
  $this->messenger()->addStatus($this->t('Greetings sent to @count folks. It took @time seconds. Memory peak @peak MB.',
    [
      '@count' => count($uids),
      '@time' => $final_time - $start_time,
      '@peak' => $memory_peak,
    ]));
}

}
