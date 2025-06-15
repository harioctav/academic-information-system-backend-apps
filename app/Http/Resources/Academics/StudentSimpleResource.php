<?php

namespace App\Http\Resources\Academics;

use App\Http\Resources\Evaluations\RecommendationResource;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSimpleResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'nim' => $this->nim,
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone,
		];
	}
}
