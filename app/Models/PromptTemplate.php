<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'template',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods

    public function render(array $data): string
    {
        $template = $this->template;

        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }

    public function hasAllVariables(array $data): bool
    {
        if (empty($this->variables)) {
            return true;
        }

        foreach ($this->variables as $variable) {
            if (!isset($data[$variable])) {
                return false;
            }
        }

        return true;
    }
}
