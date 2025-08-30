<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Holidays = Holiday::query()
            ->orderBy('date', 'desc')
            ->orderBy('day_month', 'asc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Holidays/Index', [
            'Holidays' => $Holidays,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHolidayRequest $request)
    {
        Holiday::create($request->validated());

        return to_route('non-working-days.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $Holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $Holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHolidayRequest $request, Holiday $Holiday)
    {
        $Holiday->update($request->validated());

        return to_route('non-working-days.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $Holiday)
    {
        $Holiday->delete();

        return to_route('non-working-days.index');
    }
}
