<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->orderedByTypeForDisplay()
            ->get();

        return view('menus.index', compact('menus'));
    }

    public function show(Menu $menu)
    {
        // オプションを積極的にロード
        $menu->load(['options' => fn ($query) => $query->active()]);

        return view('menus.show', compact('menu'));
    }
}
