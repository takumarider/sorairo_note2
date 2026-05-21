<?php

namespace App\Support;

class WelcomeStyleHelper
{
    public static function themeBackgroundClass(?string $value): string
    {
        return [
            'sky' => 'bg-gradient-to-br from-sky-50 via-white to-cyan-100',
            'mint' => 'bg-gradient-to-br from-emerald-50 via-white to-teal-100',
            'sand' => 'bg-gradient-to-br from-amber-50 via-white to-orange-100',
            'indigo' => 'bg-gradient-to-br from-indigo-50 via-white to-violet-100',
            'cyan' => 'bg-gradient-to-br from-cyan-50 via-white to-sky-100',
            'amber' => 'bg-gradient-to-br from-amber-50 via-white to-rose-100',
        ][$value ?? ''] ?? 'bg-gradient-to-br from-sky-50 via-white to-cyan-100';
    }

    public static function accentClasses(?string $value): array
    {
        return [
            'sky' => [
                'badge' => 'bg-sky-100 text-sky-800',
                'button' => 'border-sky-200 text-sky-800',
            ],
            'emerald' => [
                'badge' => 'bg-emerald-100 text-emerald-800',
                'button' => 'border-emerald-200 text-emerald-800',
            ],
            'rose' => [
                'badge' => 'bg-rose-100 text-rose-800',
                'button' => 'border-rose-200 text-rose-800',
            ],
            'indigo' => [
                'badge' => 'bg-indigo-100 text-indigo-800',
                'button' => 'border-indigo-200 text-indigo-800',
            ],
            'cyan' => [
                'badge' => 'bg-cyan-100 text-cyan-800',
                'button' => 'border-cyan-200 text-cyan-800',
            ],
            'amber' => [
                'badge' => 'bg-amber-100 text-amber-800',
                'button' => 'border-amber-200 text-amber-800',
            ],
        ][$value ?? ''] ?? [
            'badge' => 'bg-sky-100 text-sky-800',
            'button' => 'border-sky-200 text-sky-800',
        ];
    }

    public static function heroAlignClass(?string $value): string
    {
        return [
            'left' => 'text-left',
            'center' => 'text-center',
        ][$value ?? ''] ?? 'text-left';
    }

    public static function heroTitleSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-xl lg:text-2xl',
            'sm' => 'text-2xl lg:text-3xl',
            'md' => 'text-3xl lg:text-4xl',
            'lg' => 'text-4xl lg:text-5xl',
            'xl' => 'text-5xl lg:text-6xl',
            '2xl' => 'text-6xl lg:text-7xl',
        ][$value ?? ''] ?? 'text-4xl lg:text-5xl';
    }

    public static function heroTitleColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-900',
            'sky' => 'text-sky-900',
            'emerald' => 'text-emerald-900',
            'indigo' => 'text-indigo-900',
            'cyan' => 'text-cyan-900',
            'amber' => 'text-amber-900',
        ][$value ?? ''] ?? 'text-slate-900';
    }

    public static function heroSubtitleSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-base lg:text-lg',
            'sm' => 'text-lg lg:text-xl',
            'md' => 'text-xl lg:text-2xl',
            'lg' => 'text-2xl lg:text-3xl',
            'xl' => 'text-3xl lg:text-4xl',
            '2xl' => 'text-4xl lg:text-5xl',
        ][$value ?? ''] ?? 'text-xl lg:text-2xl';
    }

    public static function heroSubtitleColorClass(?string $value): string
    {
        return [
            'sky' => 'text-sky-800',
            'emerald' => 'text-emerald-800',
            'rose' => 'text-rose-800',
            'indigo' => 'text-indigo-800',
            'cyan' => 'text-cyan-800',
            'amber' => 'text-amber-800',
        ][$value ?? ''] ?? 'text-sky-800';
    }

    public static function heroLeadSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-sm lg:text-base',
            'sm' => 'text-base lg:text-lg',
            'md' => 'text-lg lg:text-xl',
            'lg' => 'text-xl lg:text-2xl',
            'xl' => 'text-2xl lg:text-3xl',
            '2xl' => 'text-3xl lg:text-4xl',
        ][$value ?? ''] ?? 'text-lg lg:text-xl';
    }

    public static function heroLeadColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-600',
            'sky' => 'text-sky-700',
            'emerald' => 'text-emerald-700',
            'indigo' => 'text-indigo-700',
            'cyan' => 'text-cyan-700',
            'amber' => 'text-amber-700',
        ][$value ?? ''] ?? 'text-slate-600';
    }

    public static function shopTitleSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-base',
            'sm' => 'text-lg',
            'md' => 'text-xl',
            'lg' => 'text-2xl',
            'xl' => 'text-3xl',
            '2xl' => 'text-4xl',
        ][$value ?? ''] ?? 'text-xl';
    }

    public static function shopTitleColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-900',
            'sky' => 'text-sky-900',
            'emerald' => 'text-emerald-900',
            'indigo' => 'text-indigo-900',
            'cyan' => 'text-cyan-900',
            'amber' => 'text-amber-900',
        ][$value ?? ''] ?? 'text-slate-900';
    }

    public static function shopBodySizeClass(?string $value): string
    {
        return [
            'xs' => 'text-[11px]',
            'sm' => 'text-xs',
            'md' => 'text-sm',
            'lg' => 'text-base',
            'xl' => 'text-lg',
            '2xl' => 'text-xl',
        ][$value ?? ''] ?? 'text-sm';
    }

    public static function shopBodyColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-700',
            'sky' => 'text-sky-800',
            'emerald' => 'text-emerald-800',
            'indigo' => 'text-indigo-800',
            'cyan' => 'text-cyan-800',
            'amber' => 'text-amber-800',
        ][$value ?? ''] ?? 'text-slate-700';
    }

    public static function blockTitleSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-xs',
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg',
            'xl' => 'text-xl',
            '2xl' => 'text-2xl',
        ][$value ?? ''] ?? 'text-base';
    }

    public static function blockTitleColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-900',
            'sky' => 'text-sky-900',
            'emerald' => 'text-emerald-900',
            'indigo' => 'text-indigo-900',
            'cyan' => 'text-cyan-900',
            'amber' => 'text-amber-900',
        ][$value ?? ''] ?? 'text-slate-900';
    }

    public static function blockTextSizeClass(?string $value): string
    {
        return [
            'xs' => 'text-[11px]',
            'sm' => 'text-xs',
            'md' => 'text-sm',
            'lg' => 'text-base',
            'xl' => 'text-lg',
            '2xl' => 'text-xl',
        ][$value ?? ''] ?? 'text-sm';
    }

    public static function blockTextColorClass(?string $value): string
    {
        return [
            'slate' => 'text-slate-600',
            'sky' => 'text-sky-700',
            'emerald' => 'text-emerald-700',
            'indigo' => 'text-indigo-700',
            'cyan' => 'text-cyan-700',
            'amber' => 'text-amber-700',
        ][$value ?? ''] ?? 'text-slate-600';
    }

    public static function blockTextAlignClass(?string $value): string
    {
        return [
            'left' => 'text-left',
            'center' => 'text-center',
        ][$value ?? ''] ?? 'text-left';
    }

    public static function paragraphMode(?string $value, bool $allowInherit = false): string
    {
        $allowed = $allowInherit ? ['line', 'paragraph', 'inherit'] : ['line', 'paragraph'];

        return in_array($value, $allowed, true) ? (string) $value : 'line';
    }

    public static function previewResponsiveClass(string $classes, string $viewport): string
    {
        $tokens = preg_split('/\s+/', trim($classes)) ?: [];
        $base = [];
        $desktop = [];

        foreach ($tokens as $token) {
            if (str_starts_with($token, 'lg:')) {
                $desktop[] = substr($token, 3);

                continue;
            }

            $base[] = $token;
        }

        if ($viewport === 'mobile') {
            return implode(' ', $base);
        }

        return implode(' ', ! empty($desktop) ? $desktop : $base);
    }

    public static function cardPaddingHeroClass(?string $value): string
    {
        return [
            'compact' => 'p-5 lg:p-8',
            'normal' => 'p-8 lg:p-12',
            'spacious' => 'p-10 lg:p-16',
        ][$value ?? ''] ?? 'p-8 lg:p-12';
    }

    public static function cardPaddingShopClass(?string $value): string
    {
        return [
            'compact' => 'p-4 lg:p-5',
            'normal' => 'p-6 lg:p-8',
            'spacious' => 'p-8 lg:p-10',
        ][$value ?? ''] ?? 'p-6 lg:p-8';
    }

    public static function cardPaddingBlockClass(?string $value): string
    {
        return [
            'compact' => 'p-3',
            'normal' => 'p-5',
            'spacious' => 'p-7',
        ][$value ?? ''] ?? 'p-5';
    }

    public static function cardRadiusClass(?string $value): string
    {
        return [
            'none' => 'rounded-lg',
            'rounded' => 'rounded-2xl',
            'rounder' => 'rounded-3xl',
        ][$value ?? ''] ?? 'rounded-3xl';
    }

    public static function cardShadowClass(?string $value): string
    {
        return [
            'none' => 'shadow-none',
            'soft' => 'shadow-md',
            'strong' => 'shadow-xl',
        ][$value ?? ''] ?? 'shadow-xl';
    }

    public static function fontStyleClass(?string $value): string
    {
        return [
            'sans' => 'font-sans',
            'serif' => 'font-serif',
        ][$value ?? ''] ?? 'font-sans';
    }
}
