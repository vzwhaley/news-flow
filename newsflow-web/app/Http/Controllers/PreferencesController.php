<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferencesController extends Controller
{
    /**
     * Update the user's news preferences: what hour their feed refreshes and
     * which timezone that hour is interpreted in.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'refresh_hour'   => ['required', 'integer', 'between:0,23'],
            'timezone'       => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'digest_enabled' => ['required', 'boolean'],
        ]);

        $request->user()->forceFill($validated)->save();

        return back()->with('success', 'Your news preferences were saved.');
    }
}
