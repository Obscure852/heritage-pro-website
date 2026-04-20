<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller {
    public function index(Course $course) {
        $modules = $course->modules()
            ->with(['contentItems' => function ($query) {
                $query->orderBy('sequence');
            }])
            ->withCount('contentItems')
            ->orderBy('sequence')
            ->get();

        return view('lms.modules.index', compact('course', 'modules'));
    }

    public function create(Course $course) {
        Gate::authorize('manage-lms-content');

        $existingModules = $course->modules()->orderBy('sequence')->get();

        return view('lms.modules.create', compact('course', 'existingModules'));
    }

    public function store(Request $request, Course $course) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unlock_date' => 'nullable|date',
            'prerequisite_module_id' => 'nullable|exists:lms_modules,id',
            'require_sequential_completion' => 'boolean',
        ]);

        $module = DB::transaction(function () use ($request, $course, $validated) {
            // Lock to get sequence number atomically
            $maxSequence = Module::where('course_id', $course->id)
                ->lockForUpdate()
                ->max('sequence') ?? 0;

            $validated['sequence'] = $maxSequence + 1;
            $validated['course_id'] = $course->id;
            $validated['require_sequential_completion'] = $request->boolean('require_sequential_completion');

            return Module::create($validated);
        });

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'Module created successfully. You can now add content.');
    }

    public function edit(Module $module) {
        Gate::authorize('manage-lms-content');

        $module->load([
            'course',
            'contentItems' => function ($query) {
                $query->orderBy('sequence');
            },
            'prerequisiteModule',
        ]);

        $existingModules = $module->course->modules()
            ->where('id', '!=', $module->id)
            ->orderBy('sequence')
            ->get();

        return view('lms.modules.edit', compact('module', 'existingModules'));
    }

    public function update(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unlock_date' => 'nullable|date',
            'prerequisite_module_id' => 'nullable|exists:lms_modules,id',
            'require_sequential_completion' => 'boolean',
        ]);

        // Prevent circular prerequisite
        if ($validated['prerequisite_module_id'] == $module->id) {
            return back()->with('error', 'A module cannot be its own prerequisite.');
        }

        $validated['require_sequential_completion'] = $request->boolean('require_sequential_completion');

        $module->update($validated);

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module) {
        Gate::authorize('manage-lms-content');

        $course = $module->course;

        // Check if this module is a prerequisite for other modules
        $dependentModules = Module::where('prerequisite_module_id', $module->id)->exists();
        if ($dependentModules) {
            return back()->with('error', 'Cannot delete this module. Other modules depend on it as a prerequisite.');
        }

        // Delete all content items in this module
        $module->contentItems()->delete();

        $module->delete();

        // Reorder remaining modules
        $course->modules()
            ->orderBy('sequence')
            ->get()
            ->each(function ($m, $index) {
                $m->update(['sequence' => $index + 1]);
            });

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('success', 'Module deleted successfully.');
    }

    public function reorder(Request $request, Course $course) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*' => 'integer|exists:lms_modules,id',
        ]);

        DB::transaction(function () use ($course, $validated) {
            // Lock all modules for this course to prevent concurrent reordering
            Module::where('course_id', $course->id)->lockForUpdate()->get();

            foreach ($validated['modules'] as $sequence => $moduleId) {
                Module::where('id', $moduleId)
                    ->where('course_id', $course->id)
                    ->update(['sequence' => $sequence + 1]);
            }
        });

        return response()->json(['success' => true]);
    }
}
