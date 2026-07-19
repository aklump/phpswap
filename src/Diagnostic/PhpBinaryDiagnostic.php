<?php

namespace AKlump\PhpSwap\Diagnostic;

class PhpBinaryDiagnostic {

  private $version;

  private $binary;

  private $exitCode;

  private $stdout;

  private $stderr;

  private $passed;

  public function __construct($version, $binary, $exitCode, $stdout, $stderr) {
    $this->version = $version;
    $this->binary = $binary;
    $this->exitCode = $exitCode;
    $this->stdout = $stdout;
    $this->stderr = $stderr;
    $this->passed = ($exitCode === 0);
  }

  public function getVersion() {
    return $this->version;
  }

  public function getBinary() {
    return $this->binary;
  }

  public function getExitCode() {
    return $this->exitCode;
  }

  public function getStdout() {
    return $this->stdout;
  }

  public function getStderr() {
    return $this->stderr;
  }

  public function hasPassed() {
    return $this->passed;
  }

  public function getFailureOutput() {
    if (!empty($this->stderr)) {
      return $this->stderr;
    }

    return $this->stdout;
  }
}
