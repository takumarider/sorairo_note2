<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::where('is_active', true)->get();
        return view('menus.index', compact('menus'));
    }

    public function show(Menu $menu)
    {
        return view('menus.show', compact('menu'));
    }
}
