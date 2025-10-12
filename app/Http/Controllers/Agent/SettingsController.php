<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function general()
    {
        return view('agent.settings.general');
    }

    public function updateGeneral(Request $request)
    {
        // Implementation for updating general settings
        return redirect()->route('agent.settings.general');
    }
}
