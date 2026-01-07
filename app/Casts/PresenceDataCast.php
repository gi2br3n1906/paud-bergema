<?php

namespace App\Casts;

use App\DataTransferObjects\DailyLog\PresenceData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PresenceDataCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PresenceData
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);

        return PresenceData::from($data);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof PresenceData) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode(PresenceData::from($value)->toArray());
        }

        return json_encode($value);
    }
}
