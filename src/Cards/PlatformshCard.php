<?php

namespace Drupal\platformsh_teams\Cards;

use Sebbmyr\Teams\AbstractCard as Card;

/**
 * PlatformSH card for microsoft teams.
 */
class PlatformshCard extends Card {

  /**
   * Retuns card message.
   *
   * @return array
   *   Card message.
   */
  public function getMessage() {
    return [
      "@context" => "http://schema.org/extensions",
      "@type" => "MessageCard",
      "themeColor" => !empty($this->data['themeColor']) ? $this->data['themeColor'] : "0072C6",
      "title" => $this->data['title'],
      "text" => $this->data['text'],
    ];
  }

}
