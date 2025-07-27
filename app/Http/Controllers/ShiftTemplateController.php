<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftTemplateRequest;
use App\Models\ShiftTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Helpers\BreadcrumbsGenerator;

class ShiftTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showDeleted = $request->boolean('show_deleted');

        $query = ShiftTemplate::query();

        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $shiftTemplates = $query->orderBy('name')->paginate(10)->appends([
            'show_deleted' => $showDeleted,
        ]);

        $shiftTemplates->through(function ($shiftTemplate) {
            return [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'time_from' => Carbon::parse($shiftTemplate->time_from)->format('H:i'),
                'time_to' => Carbon::parse($shiftTemplate->time_to)->format('H:i'),
                'duration_hours' => number_format($shiftTemplate->duration_hours, 2),
                'required_staff_count' => $shiftTemplate->required_staff_count,
                'created_at' => $shiftTemplate->created_at->format('Y-m-d H:i'),
                'updated_at' => $shiftTemplate->updated_at->format('Y-m-d H:i'),
                'deleted_at' => $shiftTemplate->deleted_at ? $shiftTemplate->deleted_at->format('Y-m-d H:i') : null,
            ];
        });

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Harmonogramy Zmian', route('shift-templates.index'))
            ->get();

        return Inertia::render('ShiftTemplates/Index', [
            'shiftTemplates' => $shiftTemplates,
            'show_deleted' => $showDeleted,
            'flash' => session('flash'),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Harmonogramy Zmian', route('shift-templates.index'))
            ->add('Dodaj Harmonogram Zmian', route('shift-templates.create'))
            ->get();
        return Inertia::render('ShiftTemplates/Create', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShiftTemplateRequest $request)
    {
        $validatedData = $request->validated();
        ShiftTemplate::create($validatedData);

        return to_route('shift-templates.index')->with('success', 'Zmiana został pomyślnie dodany.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShiftTemplate $shiftTemplate)
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Harmonogramy Zmian', route('shift-templates.index'))
            ->add('Edytuj Harmonogram Zmian', route('shift-templates.edit', $shiftTemplate))
            ->get();
        return Inertia::render('ShiftTemplates/Edit', [
            'shiftTemplate' => [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'time_from' => Carbon::parse($shiftTemplate->time_from)->format('H:i'),
                'time_to' => Carbon::parse($shiftTemplate->time_to)->format('H:i'),
                'duration_hours' => number_format($shiftTemplate->duration_hours, 2),
                'required_staff_count' => $shiftTemplate->required_staff_count,
            ],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreShiftTemplateRequest $request, ShiftTemplate $shiftTemplate)
    {
        $validatedData = $request->validated();
        $shiftTemplate->update($validatedData);

        return to_route('shift-templates.index')->with('success', 'Zmiana została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftTemplate $shiftTemplate)
    {
        $shiftTemplate->delete();
        return back()->with('success', 'Zmiana została pomyślnie usunięta.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $shiftTemplate = ShiftTemplate::withTrashed()->findOrFail($id);
        $shiftTemplate->restore();
        return back()->with('success', 'Zmiana została pomyślnie przywrócona.');
    }
}
