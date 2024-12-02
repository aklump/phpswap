<?php

namespace AKlump\PhpSwap\Tests;

use AKlump\PhpSwap\Helper\Bash;

trait TestWithBashTrait {

  /**
   * @param int $result_code
   *   The result code to send.
   * @param string $script
   *   This will populate with the value sent to system()
   */
  private function getBash($result_code, &$script) {
    $bash = $this->createConfiguredMock(Bash::class, [
      'getResultCode' => $result_code,
    ]);
    $bash->method('system')
      ->willReturnCallback(function ($arg1) use (&$script) {
        $script = $arg1;

        return '';
      });

    return $bash;
  }
}
