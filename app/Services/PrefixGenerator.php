<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Folder;
use App\Models\PrefixCounter;
use Illuminate\Support\Facades\DB;

class PrefixGenerator
{
    public function generate(Category $category, Folder $folder, ?int $year = null): string
    {
        $year ??= now()->year;

        $nextNumber = DB::transaction(function () use ($category, $folder, $year): int {
            $counter = PrefixCounter::query()
                ->where('folder_id', $folder->id)
                ->where('category_id', $category->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = PrefixCounter::query()->create([
                    'folder_id' => $folder->id,
                    'category_id' => $category->id,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $counter->increment('last_number');

            return (int) $counter->fresh()->last_number;
        }, 3);

        return $this->buildPrefix($folder, $category, $year, $nextNumber);
    }

    public function preview(Category $category, Folder $folder, ?int $year = null): string
    {
        $year ??= now()->year;

        $lastNumber = (int) PrefixCounter::query()
            ->where('folder_id', $folder->id)
            ->where('category_id', $category->id)
            ->where('year', $year)
            ->value('last_number');

        return $this->buildPrefix($folder, $category, $year, $lastNumber + 1);
    }

    private function buildPrefix(Folder $folder, Category $category, int $year, int $number): string
    {
        return sprintf(
            'ECP-%s/%s-%d/%04d',
            strtoupper($folder->code),
            strtoupper($category->code),
            $year,
            $number
        );
    }
}
