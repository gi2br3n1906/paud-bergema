<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAspect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentAspectController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AssessmentAspect::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            });

        $aspects = $query->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();

        // Get unique categories for filter
        $categories = AssessmentAspect::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Admin/AssessmentAspects/Index', [
            'aspects' => $aspects,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        AssessmentAspect::create($validated);

        return redirect()->route('admin.assessment-aspects.index')
            ->with('success', 'Aspek penilaian berhasil ditambahkan.');
    }

    public function update(Request $request, AssessmentAspect $assessmentAspect): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $assessmentAspect->update($validated);

        return redirect()->route('admin.assessment-aspects.index')
            ->with('success', 'Aspek penilaian berhasil diperbarui.');
    }

    public function destroy(AssessmentAspect $assessmentAspect): RedirectResponse
    {
        // Check if aspect is used in report details
        if ($assessmentAspect->reportDetails()->count() > 0) {
            return redirect()->route('admin.assessment-aspects.index')
                ->with('error', 'Tidak dapat menghapus aspek penilaian yang sudah digunakan dalam rapor.');
        }

        $assessmentAspect->delete();

        return redirect()->route('admin.assessment-aspects.index')
            ->with('success', 'Aspek penilaian berhasil dihapus.');
    }
}
