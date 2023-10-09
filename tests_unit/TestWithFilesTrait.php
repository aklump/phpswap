<?php

namespace AKlump\PhpSwap\Tests;


trait TestWithFilesTrait {

  /**
   * Delete all the files in the test files directory.
   *
   * This should generally be added to the tearDown method.
   *
   * @return void
   */
  public function deleteAllTestFiles() {
    $basepath = $this->getTestFilesDirectory();
    $all_files = array_diff(scandir($basepath), ['.', '..']);
    foreach ($all_files as $file) {
      $this->deleteRecursively("$basepath/$file");
    }
  }

  /**
   * @param $path
   *   An absolute or relative path to a file in the test files directory to be deleted.  Absolute files must be in the test directory.
   *
   * @return void
   *
   * @see \AKlump\PhpSwap\Tests\TestWithFilesTrait::getTestFilesDirectory
   */
  public function deleteTestFile($test_file) {
    if (empty($test_file)) {
      throw new \InvalidArgumentException('$test_file cannot be empty');
    }
    $is_absolute = substr($test_file, 0, 1) === '/';
    if ($is_absolute && !$this->isTestFile($test_file)) {
      throw new \InvalidArgumentException(sprintf('You cannot delete absolute paths outside of the sandbox: %s', $test_file));
    }
    if (!$is_absolute) {
      $test_file = $this->getTestFileFilepath($test_file);
    }
    if (file_exists($test_file)) {
      if (is_dir($test_file)) {
        $this->deleteRecursively($test_file);
      }
      else {
        unlink($test_file);
      }
    }
  }

  private function isTestFile($path) {
    return strpos($path, $this->getTestFilesDirectory()) === 0;
  }

  private function getTestFilesDirectory() {
    $basepath = sys_get_temp_dir() . '/phpswap/';
    if (!file_exists($basepath)) {
      mkdir($basepath, 0755, TRUE);
    }
    if (!$basepath || !is_writable($basepath)) {
      throw new \RuntimeException(sprintf('Failed to establish a sanbox base directory: %s', $basepath));
    }

    return $basepath;
  }

  private function deleteRecursively($path) {
    if (!$this->isTestFile($path)) {
      throw new \RuntimeException(sprintf('$path is not in the files sandbox and cannot be deleted. %s', $path));
    }
    if (!is_dir($path)) {
      unlink($path);

      return;
    }
    $files = array_diff(scandir($path), ['.', '..']);
    foreach ($files as $file) {
      $this->deleteRecursively("$path/$file");
    }
    rmdir($path);
  }

  public function getTestFileFilepath($relative = '', $create = FALSE) {
    $basedir = $this->getTestFilesDirectory();
    if (empty($relative)) {
      return $basedir;
    }
    $path = $basedir . ltrim($relative, '/');
    $is_dir = substr($path, -1) === '/';
    if ($is_dir) {
      if ($create && !file_exists($path)) {
        mkdir($path, 0755, TRUE);
      }
    }
    else {
      // For all files, always create the parent structure to make it easy on
      // the implementing test to work with the filepath.  No harm as the
      // teardown method should be calling deleteAll(), which will remove the
      // created directories.
      $parent = dirname($path);
      if (!file_exists($parent)) {
        mkdir($parent, 0755, TRUE);
      }
      if ($create && !file_exists($path)) {
        touch($path);
      }
    }

    return $path;
  }

}
