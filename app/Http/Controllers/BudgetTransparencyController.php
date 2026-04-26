<?php

namespace App\Http\Controllers;

use App\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BudgetTransparencyController extends Controller
{
    public function __invoke(Request $request, ?int $year = null): JsonResponse
    {
        $year = $year ?: $request->integer('year') ?: BudgetItem::published()->max('year') ?? now()->year;

        $items = BudgetItem::published()
            ->forYear($year)
            ->ordered()
            ->get()
            ->map(function (BudgetItem $item) {
                return [
                    'id' => $item->id,
                    'year' => $item->year,
                    'category' => $item->category,
                    'subcategory' => $item->subcategory,
                    'description' => $item->description,
                    'anggaran' => $item->anggaran,
                    'realisasi' => $item->realisasi,
                    'progress_percent' => $item->progress_percent,
                    'icon' => $item->icon,
                    'order_no' => $item->order_no,
                ];
            });

        return response()->json([
            'year' => $year,
            'items' => $items,
        ]);
    }
}
