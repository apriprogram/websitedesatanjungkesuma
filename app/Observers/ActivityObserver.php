<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logActivity('create', $model, 'Menambahkan data baru');
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Ignore if no changes (though usually updated event fires only on changes)
        if ($model->wasChanged()) {
            $changes = $model->getChanges();
            unset($changes['updated_at']); // Don't log timestamp changes alone

            if (!empty($changes)) {
                $this->logActivity('update', $model, 'Memperbarui data', ['changes' => $changes]);
            }
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logActivity('delete', $model, 'Menghapus data');
    }

    /**
     * Log the activity using the ActivityLogger.
     */
    protected function logActivity(string $action, Model $model, string $baseDescription, array $meta = []): void
    {
        // Generate a readable name for the model
        $modelName = class_basename($model);
        $readableModelName = Str::headline($modelName);

        // Try to find a displayable name or title from the model
        $modelLabel = $this->getModelLabel($model);

        $description = "{$baseDescription} {$readableModelName}";
        if ($modelLabel) {
            $description .= ": {$modelLabel}";
        }

        ActivityLogger::log($action, $model, $description, $meta);
    }


    /**
     * Attempt to get a readable label for the model instance.
     */
    protected function getModelLabel(Model $model): ?string
    {
        // Common attributes for names/titles
        $attributes = ['nama', 'name', 'title', 'judul', 'subject', 'nik'];

        foreach ($attributes as $attr) {
            if ($model->getAttribute($attr)) {
                return (string) $model->getAttribute($attr);
            }
        }

        // Fallback to ID if no name found
        return '#' . $model->getKey();
    }
}
