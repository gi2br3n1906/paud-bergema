<?php

namespace App\Casts;

use App\DataTransferObjects\DailyLog\WorshipData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class WorshipDataCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?WorshipData
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);

        return WorshipData::from($data);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof WorshipData) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode(WorshipData::from($value)->toArray());
        }

        return json_encode($value);
    }
}
