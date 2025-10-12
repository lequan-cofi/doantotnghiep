<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommissionEventController extends Controller
{
    public function index()
    {
        return view('agent.commission-events.index');
    }

    public function create()
    {
        return view('agent.commission-events.create');
    }

    public function store(Request $request)
    {
        // Implementation for storing commission event
        return redirect()->route('agent.commission-events.index');
    }

    public function show($id)
    {
        return view('agent.commission-events.show', compact('id'));
    }

    public function edit($id)
    {
        return view('agent.commission-events.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Implementation for updating commission event
        return redirect()->route('agent.commission-events.index');
    }

    public function destroy($id)
    {
        // Implementation for deleting commission event
        return redirect()->route('agent.commission-events.index');
    }
}
