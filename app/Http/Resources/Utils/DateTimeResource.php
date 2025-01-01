<?php

namespace App\Http\Resources\Utils;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DateTimeResource extends JsonResource
{
  private $indonesianDays = [
    'Sunday' => 'Min',
    'Monday' => 'Sen',
    'Tuesday' => 'Sel',
    'Wednesday' => 'Rab',
    'Thursday' => 'Kam',
    'Friday' => 'Jum',
    'Saturday' => 'Sab'
  ];

  private $indonesianMonths = [
    'January' => 'Jan',
    'February' => 'Feb',
    'March' => 'Mar',
    'April' => 'Apr',
    'May' => 'Mei',
    'June' => 'Jun',
    'July' => 'Jul',
    'August' => 'Agu',
    'September' => 'Sep',
    'October' => 'Okt',
    'November' => 'Nov',
    'December' => 'Des'
  ];

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $dayName = $this->indonesianDays[$this->format('l')];
    $monthName = $this->indonesianMonths[$this->format('F')];

    return [
      'human' => $this->diffForHumans(),
      'machine' => $this->toDateTimeString(),
      'formatted' => "{$dayName}, {$this->format('d')} {$monthName} {$this->format('Y H:i')}"
    ];
  }
}
