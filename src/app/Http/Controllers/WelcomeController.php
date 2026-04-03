<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $settings = SystemSetting::getSingleton();

        return view('welcome', [
            'settings' => $settings,
            'bodyBlocks' => collect($settings->welcome_body_blocks)
                ->filter(fn ($block) => filled(data_get($block, 'title')) || filled(data_get($block, 'text')))
                ->values(),
        ]);
    }
}
