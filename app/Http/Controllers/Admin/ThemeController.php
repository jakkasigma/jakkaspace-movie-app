<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ThemeRequest;
use App\Models\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ThemeController extends Controller
{
    public function index(): View
    {
        $themes = Theme::latest()->paginate(20);

        return view('admin.themes.index', ['themes' => $themes]);
    }

    public function create(): View
    {
        return view('admin.themes.create', ['theme' => new Theme]);
    }

    public function store(ThemeRequest $request): RedirectResponse
    {
        Theme::create($request->validated());

        return redirect()->route('admin.themes.index')
            ->with('success', 'Tema berhasil ditambahkan.');
    }

    public function edit(Theme $theme): View
    {
        return view('admin.themes.edit', ['theme' => $theme]);
    }

    public function update(ThemeRequest $request, Theme $theme): RedirectResponse
    {
        $theme->update($request->validated());

        return redirect()->route('admin.themes.index')
            ->with('success', 'Tema berhasil diupdate.');
    }

    public function destroy(Theme $theme): RedirectResponse
    {
        $theme->delete();

        return redirect()->route('admin.themes.index')
            ->with('success', 'Tema berhasil dihapus.');
    }
}
