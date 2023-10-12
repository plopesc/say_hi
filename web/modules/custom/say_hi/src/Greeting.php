<?php

declare(strict_types=1);

namespace Drupal\say_hi;

use Drupal\user\UserInterface;

/**
 * Maps a greeting message and its properties.
 */
final class Greeting {

  /**
   * Constructs a new Greeting object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity to receive the message.
   * @param string $name
   *   The sender name.
   * @param string $message
   *   The greeting message.
   */
  public function __construct(
    public readonly UserInterface $user,
    public readonly string $name,
    public readonly string $message
  ) {
  }

}
