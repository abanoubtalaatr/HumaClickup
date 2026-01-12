<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'custom_field_id',
        'customizable_type',
        'customizable_id',
        'value',
    ];

    // Relationships
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }

    public function customizable(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper Methods
    public function getFormattedValue(): mixed
    {
        $field = $this->customField;
        
        return match($field->type) {
            'number' => (float) $this->value,
            'checkbox' => (bool) $this->value,
            'date' => $this->value ? date('Y-m-d', strtotime($this->value)) : null,
            'dropdown' => $this->value,
            default => $this->value,
        };
    }
}
