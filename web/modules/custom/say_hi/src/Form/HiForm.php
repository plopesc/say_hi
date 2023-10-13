<?php

namespace Drupal\say_hi\Form;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Form\FormStateInterface;
use Drupal\say_hi\Greetings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a basic Say Hi form.
 */
class HiForm extends HiFormBase {

  /**
   * Constructs a new say hello form.
   *
   * @param \Drupal\say_hi\Greetings $greetings
   *   The greetings service.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time service.
   */
  public function __construct(
    protected Greetings $greetings,
    protected Time $time
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('say_hi.greetings'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'say_hi_hi';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $start_time = $this->time->getCurrentMicroTime();
    $message = $form_state->getValue('message');
    $name = $form_state->getValue('name');

    $users = $this->greetings->sendGreetingsMultiple($name, $message);

    $final_time = $this->time->getCurrentMicroTime();
    $memory_peak = memory_get_peak_usage() / 1024 / 1024;
    $this->messenger()->addStatus($this->t('Greetings sent to @count folks. It took @time seconds. Memory peak @peak MB.',
      [
        '@count' => count($users),
        '@time' => $final_time - $start_time,
        '@peak' => $memory_peak,
      ]));
  }

}
