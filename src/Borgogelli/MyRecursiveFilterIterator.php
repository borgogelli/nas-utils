<?php

namespace Borgogelli;

class MyRecursiveFilterIterator extends \RecursiveFilterIterator {

    public function accept() {
      $filename = $this->current()->getFilename();
      // echo "****** $filename " . PHP_EOL;
      // Skip hidden files and directories.
      if ( ($filename === '.') || ($filename === '..') ){
        return false;
      }
      if ($this->isDir()) {
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