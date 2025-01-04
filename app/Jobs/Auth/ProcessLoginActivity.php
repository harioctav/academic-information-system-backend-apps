<?php

namespace App\Jobs\Auth;

use App\Repositories\Security\SecurityRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLoginActivity implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected array $loginData;

  /**
   * Create a new job instance.
   */
  public function __construct(array $loginData)
  {
    $this->loginData = $loginData;
  }

  /**
   * Execute the job.
   */
  public function handle(SecurityRepository $repository)
  {
    $repository->create($this->loginData);
  }
}
