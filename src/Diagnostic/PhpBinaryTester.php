<?php

namespace AKlump\PhpSwap\Diagnostic;

class PhpBinaryTester {

  /**
   * Test a PHP binary by running php -v.
   *
   * @param string $version The expected version.
   * @param string $binary The path to the PHP binary.
   *
   * @return PhpBinaryDiagnostic
   */
  public function test($version, $binary) {
    if (!file_exists($binary)) {
      return new PhpBinaryDiagnostic($version, $binary, 127, '', 'Binary does not exist.');
    }
    if (!is_executable($binary)) {
      return new PhpBinaryDiagnostic($version, $binary, 126, '', 'Binary is not executable.');
    }

    $command = escapeshellarg($binary) . ' -v';
    $descriptors = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w'),
      2 => array('pipe', 'w'),
    );

    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
      return new PhpBinaryDiagnostic($version, $binary, 1, '', 'Failed to open process.');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exit_code = proc_close($process);

    return new PhpBinaryDiagnostic($version, $binary, $exit_code, $stdout, $stderr);
  }
}
