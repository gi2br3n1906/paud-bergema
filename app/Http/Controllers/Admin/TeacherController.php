<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('role', UserRole::TEACHER)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            });

        $teachers = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => ['required', Password::defaults()],
            'is_active' => 'required|boolean',
        ]);

        $validated['role'] = UserRole::TEACHER;
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        // Ensure we're only updating teachers
        if ($teacher->role !== UserRole::TEACHER) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User bukan guru.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teacher->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => ['nullable', Password::defaults()],
            'is_active' => 'required|boolean',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $teacher->update($validated);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        // Ensure we're only deleting teachers
        if ($teacher->role !== UserRole::TEACHER) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User bukan guru.');
        }

        // Check if teacher has classrooms
        if ($teacher->classrooms()->count() > 0) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Tidak dapat menghapus guru yang memiliki kelas.');
        }

        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
