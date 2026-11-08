<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'category',
        'subject',
        'subject_ar',
        'title',
        'title_ar',
        'body',
        'body_ar',
        'action_url',
        'action_text',
        'banner_url',
        'channels',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function automatedRules(): HasMany
    {
        return $this->hasMany(AutomatedRule::class, 'template_id');
    }

    /**
     * Parse template variables with context data
     */
    public function render(array $context): array
    {
        $renderedTitle = $this->title;
        $renderedSubject = $this->subject ?? $this->title;
        $renderedBody = $this->body;
        $renderedActionUrl = $this->action_url;
        $renderedActionText = $this->action_text;

        foreach ($context as $key => $value) {
            $val = is_scalar($value) ? (string)$value : '';
            $placeholder = '{{' . $key . '}}';
            $placeholderSpaced = '{{ ' . $key . ' }}';

            $renderedTitle = str_replace([$placeholder, $placeholderSpaced], $val, $renderedTitle);
            $renderedSubject = str_replace([$placeholder, $placeholderSpaced], $val, $renderedSubject);
            $renderedBody = str_replace([$placeholder, $placeholderSpaced], $val, $renderedBody);
            if ($renderedActionUrl) {
                $renderedActionUrl = str_replace([$placeholder, $placeholderSpaced], $val, $renderedActionUrl);
            }
            if ($renderedActionText) {
                $renderedActionText = str_replace([$placeholder, $placeholderSpaced], $val, $renderedActionText);
            }
        }

        return [
            'subject' => $renderedSubject,
            'title' => $renderedTitle,
            'body' => $renderedBody,
            'action_url' => $renderedActionUrl,
            'action_text' => $renderedActionText,
            'banner_url' => $this->banner_url,
        ];
    }
}
