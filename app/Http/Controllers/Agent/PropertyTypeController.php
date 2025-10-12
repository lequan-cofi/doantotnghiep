<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function index()
    {
        $propertyTypes = PropertyType::where('status', 1)->get();
        return view('agent.property-types.index', compact('propertyTypes'));
    }

    public function create()
    {
        return view('agent.property-types.create');
    }

    public function store(Request $request)
    {
        // Implementation for storing property type
        return redirect()->route('agent.property-types.index');
    }

    public function show($id)
    {
        $propertyType = PropertyType::findOrFail($id);
        return view('agent.property-types.show', compact('propertyType'));
    }

    public function edit($id)
    {
        $propertyType = PropertyType::findOrFail($id);
        return view('agent.property-types.edit', compact('propertyType'));
    }

    public function update(Request $request, $id)
    {
        // Implementation for updating property type
        return redirect()->route('agent.property-types.index');
    }

    public function destroy($id)
    {
        // Implementation for deleting property type
        return redirect()->route('agent.property-types.index');
    }
}
