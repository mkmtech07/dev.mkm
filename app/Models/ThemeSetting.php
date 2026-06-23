<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    public const COLOR_FIELDS = [
        'primary_color', 'secondary_color', 'accent_color', 'body_bg_color', 'text_color',
        'heading_color', 'link_color', 'link_hover_color', 'button_bg_color', 'button_text_color',
        'button_hover_bg_color', 'button_hover_text_color', 'header_bg_color', 'header_text_color',
        'header_link_color', 'header_link_hover_color', 'footer_bg_color', 'footer_text_color',
        'footer_link_color', 'footer_link_hover_color',
    ];

    public const FONT_FAMILIES = [
        'system-ui, -apple-system, "Segoe UI", sans-serif' => 'System / Segoe UI',
        'Arial, Helvetica, sans-serif' => 'Arial',
        'Verdana, Geneva, sans-serif' => 'Verdana',
        'Tahoma, Geneva, sans-serif' => 'Tahoma',
        'Georgia, "Times New Roman", serif' => 'Georgia',
        '"Times New Roman", Times, serif' => 'Times New Roman',
        '"Trebuchet MS", Arial, sans-serif' => 'Trebuchet MS',
        '"Courier New", Courier, monospace' => 'Courier New',
    ];

    public const LAYOUT_STYLES = ['full_width', 'boxed'];
    public const HEADER_STYLES = ['light', 'dark', 'transparent', 'custom'];
    public const FOOTER_STYLES = ['light', 'dark', 'custom'];
    public const THEME_MODES = ['light', 'dark', 'auto'];

    public const PUBLIC_FIELDS = [
        'theme_name', ...self::COLOR_FIELDS, 'font_family', 'heading_font_family', 'font_size',
        'container_width', 'border_radius', 'button_radius', 'layout_style', 'header_style',
        'footer_style', 'theme_mode', 'custom_css',
    ];

    protected $fillable = [
        ...self::PUBLIC_FIELDS,
        'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'theme_name' => 'Default Theme',
            ...array_fill_keys(self::COLOR_FIELDS, null),
            'font_family' => null,
            'heading_font_family' => null,
            'font_size' => null,
            'container_width' => null,
            'border_radius' => null,
            'button_radius' => null,
            'layout_style' => 'full_width',
            'header_style' => 'light',
            'footer_style' => 'dark',
            'theme_mode' => 'light',
            'custom_css' => null,
            'status' => true,
        ];
    }

    /** @return array<string, mixed> */
    public function publicValues(): array
    {
        return $this->only(self::PUBLIC_FIELDS);
    }
}
