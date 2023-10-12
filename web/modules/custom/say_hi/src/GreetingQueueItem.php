<?php

declare(strict_types=1);

namespace Drupal\say_hi;

/**
 * Maps a Greeting Queue Item object.
 */
final class GreetingQueueItem {

  /**
   * Constructs a GreetingQueueItem object.
   *
   * @param int $uid
   *   The receiver user ID.
   * @param string $name
   *   The sender name.
   * @param string $message
   *   The greetings message.
   */
  public function __construct(
    public readonly int $uid,
    public readonly string $name,
    public readonly string $message
  ) {
  }

}
