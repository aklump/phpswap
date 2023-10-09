<?php

namespace AKlump\PhpSwap;

class Bash {

  private $resultCode = 0;

  public function system($script) {
    return system($script, $this->resultCode);
  }

  public function getResultCode() {
    return $this->resultCode;
  }

}
