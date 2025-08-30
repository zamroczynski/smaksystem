<?php

namespace App\Http\Controllers;

use App\Models\NonWorkingDay;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNonWorkingDayRequest;
use App\Http\Requests\UpdateNonWorkingDayRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NonWorkingDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nonWorkingDays = NonWorkingDay::query()
            ->orderBy('date', 'desc')
            ->orderBy('day_month', 'asc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('NonWorkingDays/Index', [
            'nonWorkingDays' => $nonWorkingDays,
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
    public function store(StoreNonWorkingDayRequest $request)
    {
        NonWorkingDay::create($request->validated());

        return to_route('non-working-days.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(NonWorkingDay $nonWorkingDay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NonWorkingDay $nonWorkingDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNonWorkingDayRequest $request, NonWorkingDay $nonWorkingDay)
    {
        $nonWorkingDay->update($request->validated());

        return to_route('non-working-days.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NonWorkingDay $nonWorkingDay)
    {
        $nonWorkingDay->delete();

        return to_route('non-working-days.index');
    }
}
