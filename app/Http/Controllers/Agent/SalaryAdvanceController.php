<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        return view('agent.salary-advances.index');
    }

    public function create()
    {
        return view('agent.salary-advances.create');
    }

    public function store(Request $request)
    {
        // Implementation for storing salary advance
        return redirect()->route('agent.salary-advances.index');
    }

    public function show($id)
    {
        return view('agent.salary-advances.show', compact('id'));
    }

    public function edit($id)
    {
        return view('agent.salary-advances.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Implementation for updating salary advance
        return redirect()->route('agent.salary-advances.index');
    }

    public function destroy($id)
    {
        // Implementation for deleting salary advance
        return redirect()->route('agent.salary-advances.index');
    }
}
