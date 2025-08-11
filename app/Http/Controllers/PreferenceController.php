<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StorePreferenceRequest;
use App\Models\Preference;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $showInactiveOrDeleted = $request->boolean('show_inactive_or_deleted');

        $preferencesQuery = $user->preferences()->orderBy('date_from', 'desc');

        if ($showInactiveOrDeleted) {
            $preferencesQuery->withTrashed()
                ->where(function ($query) {
                    $query->whereDate('date_to', '<', Carbon::today())
                        ->orWhereNotNull('deleted_at');
                });
        } else {
            $preferencesQuery->whereDate('date_to', '>=', Carbon::today())
                ->whereNull('deleted_at');
        }

        $preferences = $preferencesQuery->paginate(10)->appends([
            'show_inactive_or_deleted' => $showInactiveOrDeleted,
        ]);

        $preferences->through(function ($preference) {
            $dateTo = Carbon::parse($preference->date_to);

            return [
                'id' => $preference->id,
                'description' => $preference->description,
                'date_from' => $preference->date_from->format('Y-m-d'),
                'date_to' => $preference->date_to->format('Y-m-d'),
                'is_active' => $dateTo->gte(Carbon::today()) && $preference->deleted_at === null,
                'deleted_at' => $preference->deleted_at,
                'availability' => $preference->availability,
            ];
        });

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Moje Preferencje', route('preferences.index'))
            ->get();

        return Inertia::render('Preferences/Index', [
            'preferences' => $preferences,
            'show_inactive_or_deleted' => $showInactiveOrDeleted,
            'flash' => session('flash'),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Moje Preferencje', route('preferences.index'))
            ->add('Dodaj Preferencje', route('preferences.create'))
            ->get();

        return Inertia::render('Preferences/Create', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePreferenceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validatedData = $request->validated();

        $user->preferences()->create([
            'date_from' => $validatedData['date_from'],
            'date_to' => $validatedData['date_to'],
            'description' => $validatedData['description'] ?? null,
            'availability' => $validatedData['availability'],
        ]);

        return to_route('preferences.index')->with('success', 'Preferencja grafiku została pomyślnie dodana.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Preference $preference)
    {
        if ($preference->user_id !== Auth::id()) {
            return to_route('preferences.index')->with('error', 'Nie masz uprawnień do edycji tej preferencji.');
        }

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Moje Preferencje', route('preferences.index'))
            ->add('Edytuj Preferencję', route('preferences.edit', $preference))
            ->get();

        return Inertia::render('Preferences/Edit', [
            'preference' => [
                'id' => $preference->id,
                'date_from' => $preference->date_from->format('Y-m-d'),
                'date_to' => $preference->date_to->format('Y-m-d'),
                'description' => $preference->description,
                'availability' => $preference->availability,
            ],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePreferenceRequest $request, Preference $preference)
    {
        if ($preference->user_id !== Auth::id()) {
            return to_route('preferences.index')->with('error', 'Nie masz uprawnień do zaktualizowania tej preferencji.');
        }

        $validatedData = $request->validated();

        $preference->update([
            'date_from' => $validatedData['date_from'],
            'date_to' => $validatedData['date_to'],
            'description' => $validatedData['description'] ?? null,
            'availability' => $validatedData['availability'],
        ]);

        return to_route('preferences.index')->with('success', 'Preferencja grafiku została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Preference $preference)
    {
        if ($preference->user_id !== Auth::id()) {
            return to_route('preferences.index')->with('error', 'Nie masz uprawnień do usunięcia tej preferencji.');
        }

        $preference->delete();

        return to_route('preferences.index')->with('success', 'Preferencja grafiku została pomyślnie usunięta.');
    }

    /**
     * Restore the specified soft-deleted resource from storage.
     */
    public function restore(Preference $preference)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $preference = $user->preferences()->withTrashed()->find($preference->id);

        if (! $preference || $preference->user_id !== Auth::id()) {
            return to_route('preferences.index')->with('error', 'Preferencja nie została znaleziona lub nie masz do niej uprawnień.');
        }

        if (! $preference->trashed()) {
            return to_route('preferences.index')->with('error', 'Preferencja nie jest usunięta.');
        }

        $preference->restore();

        return to_route('preferences.index')->with('success', 'Preferencja grafiku została pomyślnie przywrócona.');
    }
}
