<?php

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    /**
     * Gets the name of the user performing the action.
     */
    protected function getActorName(): string
    {
        return Auth::user()?->name ?? 'System';
    }

    /**
     * Logs the creation of a model.
     */
    protected function logCreationActivity(Model $model): void
    {
        $actor = $this->getActorName();
        $modelName = class_basename($model);
        $modelIdentifier = $model->name ?? "ID: {$model->getKey()}";

        Log::info("{$modelName} created: {$modelIdentifier} by {$actor}.");
    }

    /**
     * Logs the update of a model, detailing all changes.
     */
    protected function logUpdateActivity(Model $model): void
    {
        $dirtyAttributes = $model->getDirty();
        if (empty($dirtyAttributes)) {
            return;
        }

        $changeDetails = [];
        foreach ($dirtyAttributes as $field => $newValue) {
            $oldValue = $model->getOriginal($field);
            $changeDetails[] = "{$field} from '{$oldValue}' to '{$newValue}'";
        }

        $actor = $this->getActorName();
        $modelName = class_basename($model);
        $modelIdentifier = $model->name ?? "ID: {$model->getKey()}";
        $formattedChanges = implode(', ', $changeDetails);

        Log::info("{$modelName} updated: {$modelIdentifier} by {$actor}. Changes: {$formattedChanges}");
    }

    /**
     * Logs the disabling of a model.
     */
    protected function logDeletionActivity(Model $model): void
    {
        $actor = $this->getActorName();
        $modelName = class_basename($model);
        $modelIdentifier = $model->name ?? "ID: {$model->getKey()}";

        Log::info("{$modelName} disabled: {$modelIdentifier} by {$actor}.");
    }

    /**
     * Logs the restore of a model.
     */
    protected function logRestoredActivity(Model $model): void
    {
        $actor = $this->getActorName();
        $modelName = class_basename($model);
        $modelIdentifier = $model->name ?? "ID: {$model->getKey()}";

        Log::info("{$modelName} restore: {$modelIdentifier} by {$actor}.");
    }

    /**
     * Logs the force deletion of a model.
     */
    protected function logForceDeletedActivity(Model $model): void
    {
        $actor = $this->getActorName();
        $modelName = class_basename($model);
        $modelIdentifier = $model->name ?? "ID: {$model->getKey()}";

        Log::info("{$modelName} force deleted: {$modelIdentifier} by {$actor}.");
    }
}
