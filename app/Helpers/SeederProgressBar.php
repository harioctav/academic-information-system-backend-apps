<?php

namespace App\Helpers;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class SeederProgressBar
{
  public static function withProgressBar(
    Command $command,
    int $amount,
    Closure $createCollectionOfOne
  ): Collection {
    $progressBar = new ProgressBar($command->getOutput(), $amount);
    $progressBar->start();

    $items = new Collection;

    foreach (range(1, $amount) as $i) {
      $items = $items->merge(
        $createCollectionOfOne()
      );
      $progressBar->advance();
    }

    $progressBar->finish();
    $command->getOutput()->writeln('');

    return $items;
  }
}
