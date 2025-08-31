<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StorePreferenceRequest;
use App\Models\Preference;
use App\Services\PreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PreferenceController extends Controller
{
    public function __construct(private PreferenceService $preferenceService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $options = [
            'show_inactive_or_deleted' => $request->boolean('show_inactive_or_deleted'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'date_from'),
            'direction' => $request->input('direction', 'desc'),
        ];
        
        $preferences = $this->preferenceService->getPaginatedPreferences($options);

        return Inertia::render('Preferences/Index', [
            'preferences' => $preferences,
            'show_inactive_or_deleted' => $options['show_inactive_or_deleted'],
            'flash' => session('flash'),
            'breadcrumbs' => $this->getPreferencesBreadcrumbs(),
            'filter' => $options['filter'],
            'sort_by' => $options['sort'],
            'sort_direction' => $options['direction'],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return Inertia::render('Preferences/Create', [
            'breadcrumbs' => $this->getPreferencesBreadcrumbs('Dodaj Preferencje', route('preferences.create')),
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
        $availabilityBoolean = $validatedData['availability'] === 'available';

        $user->preferences()->create([
            'date_from' => $validatedData['date_from'],
            'date_to' => $validatedData['date_to'],
            'description' => $validatedData['description'] ?? null,
            'availability' => $availabilityBoolean,
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

        return Inertia::render('Preferences/Edit', [
            'preference' => [
                'id' => $preference->id,
                'date_from' => $preference->date_from->format('Y-m-d'),
                'date_to' => $preference->date_to->format('Y-m-d'),
                'description' => $preference->description,
                'availability' => $preference->availability,
            ],
            'breadcrumbs' => $this->getPreferencesBreadcrumbs('Edytuj Preferencję', route('preferences.edit', $preference)),
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
        $availabilityBoolean = $validatedData['availability'] === 'available';

        $preference->update([
            'date_from' => $validatedData['date_from'],
            'date_to' => $validatedData['date_to'],
            'description' => $validatedData['description'] ?? null,
            'availability' => $availabilityBoolean,
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

    /**
     * Generates breadcrumbs for preference management.
     */
    protected function getPreferencesBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Moje Preferencje', route('preferences.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
