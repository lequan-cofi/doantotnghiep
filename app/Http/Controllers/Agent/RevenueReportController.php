<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    public function index()
    {
        return view('agent.revenue-reports.index');
    }

    public function show($id)
    {
        return view('agent.revenue-reports.show', compact('id'));
    }
}
