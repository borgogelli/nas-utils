<?php

namespace Borgogelli;

/**
 * @see http://paulyg.github.io/blog/2014/01/31/using-phps-recursivefilteriterator.html
 *
 */
class MyRecursiveFilterIterator extends \RecursiveFilterIterator {

    public function accept() {
      $filename = $this->current()->getFilename();
      // echo "****** $filename " . PHP_EOL;
      // Skip hidden files and directories.
      if ( ($filename === '.') || ($filename === '..') ){
        return false;
      }
      $file = $this->current();
      if ($file->isDir()) {
        // Only recurse into intended subdirectories.
        // return $name === 'wanted_dirname';
        return true;
      } else {
        // Only consume files of interest.
        // return strpos($name, 'wanted_filename') === 0;
        return true;
      }
    }

  }