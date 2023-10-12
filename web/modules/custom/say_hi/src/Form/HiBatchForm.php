<?php

namespace Drupal\say_hi\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Say Hello form.
 */
class HiBatchForm extends FormBase {

  /**
   * Constructs a new say hello form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'say_hi_hi_batch';
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
    $users = $this->entityTypeManager->getStorage('user')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', 0 , '>')
      ->execute();
    // Set up batch operations.
    $batch = [
      'operations' => [
        ['\Drupal\say_hi\Form\HiBatchForm::initBatch', [count($users)]],
      ],
      'finished' => '\Drupal\say_hi\Form\HiBatchForm::finishedCallback',
      'title' => $this->t('Sending greetings...'),
      'init_message' => $this->t('Starting processing'),
      'error_message' => $this->t('An error occurred during processing'),
    ];

    $message = $form_state->getValue('message');
    $name = $form_state->getValue('name');
    $chunks = array_chunk($users, 50);

    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        '\Drupal\say_hi\Form\HiBatchForm::processItems',
        [$chunk, $name, $message],
      ];
    }

    batch_set($batch);
  }

  /**
   * Inits the batch process.
   *
   * @param int $total
   *   The total number of items.
   * @param array $context
   *   The batch context.
   */
  public static function initBatch(int $total, array &$context): void {
    $context['results']['start_time'] = \Drupal::time()->getCurrentMicroTime();
    $context['results']['memory_peak'] = [];
    $context['results']['total'] = $total;
  }

  /**
   * Sends the greetings.
   *
   * @param int[] $uids
   *   The uids to send the message to.
   * @param string $name
   *   The sender name.
   * @param string $message
   *   The message.
   * @param array $context
   *   The batch context.
   */
  public static function processItems(array $uids, string $name, string $message, array &$context): void {

    \Drupal::service('say_hi.greetings')->sendGreetingsMultiple($name, $message, $uids);

    $memory_peak = memory_get_peak_usage() / 1024 / 1024;
    $context['results']['memory_peak'][] = $memory_peak;
  }

  /**
   * Finish batch.
   *
   * This function is a static function to avoid serializing the ConfigSync
   * object unnecessarily.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in update_do_one().
   * @param array $operations
   *   A list of the operations that had not been completed by the batch API.
   */
  public static function finishedCallback(bool $success, array $results, array $operations): void {
    if ($success) {
      $final_time = \Drupal::time()->getCurrentMicroTime();
      \Drupal::messenger()->addStatus(t('Greetings sent to @count folks. It took @time seconds. Memory peak @peak MB.', [
        '@count' => $results['total'],
        '@time' => $final_time - $results['start_time'],
        '@peak' => max($results['memory_peak']),
      ]));
    }
    else {
      \Drupal::messenger()->addError(t('Batch process failed.'));
    }
  }

}
