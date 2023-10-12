<?php

namespace Drupal\say_hi;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;

/**
 * Service description.
 */
class Greetings {

  use StringTranslationTrait;

  /**
   * Constructs a Greetings object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   Mail manager service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected MailManagerInterface $mailManager) {
  }

  /**
   * Sends a greetings message to a multiple users.
   *
   * @param string $name
   *   The sender name.
   * @param string $message
   *   The greeting message.
   * @param array|null $uids
   *   (Optional) The user IDs to send the greetings message. Broadcast if NULL.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Array containing the users that received the message
   */
  public function sendGreetingsMultiple(string $name, string $message, $uids = NULL): array {
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
    // Exclude user 0.
    unset($users[0]);
    array_walk($users, function (UserInterface $user) use ($name, $message) {
      $this->sendGreetings(new Greeting($user, $name, $message));
    });

    return $users;
  }

  /**
   * Sends a greetings message to a specific user.
   *
   * @param \Drupal\say_hi\Greeting $greeting
   *   The greeting object to send.
   */
  public function sendGreetings(Greeting $greeting): void {
    $module = 'say_hi';
    $key = 'hi';
    $to = $greeting->user->getEmail();
    $params['message'] = $greeting->message;
    $params['subject'] = $this->t('Greetings from @name!', ['@name' => $greeting->name]);
    $langcode = $greeting->user->getPreferredLangcode();

    $this->mailManager->mail($module, $key, $to, $langcode, $params);
  }

}
