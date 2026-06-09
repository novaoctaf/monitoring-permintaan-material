<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-roles')->only(['index', 'show']);
        $this->middleware('permission:create-roles')->only(['create', 'store']);
        $this->middleware('permission:edit-roles')->only(['edit', 'update']);
        $this->middleware('permission:delete-roles')->only(['destroy']);
    }

    /**
     * Display a listing of the permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Permission::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Group filter
        if ($request->filled('group')) {
            $group = $request->group;
            $query->where(function($q) use ($group) {
                $q->whereRaw("SUBSTRING_INDEX(name, '-', -1) = ?", [$group]);
            });
        }
        
        // Get all permissions with role count
        $permissions = $query->withCount('roles')->orderBy('name')->paginate(15);
        
        // Get all permission groups for filter dropdown
        $groups = Permission::all()->map(function($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        })->unique()->sort()->values();
        
        return view('admin.permissions.index', compact('permissions', 'groups'));
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get existing permission groups for suggestions
        $permissionGroups = Permission::all()->map(function($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[1] : null;
        })->unique()->filter()->values();
        
        return view('admin.permissions.create', compact('permissionGroups'));
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|string|max:255',
        ]);

        try {
            DB::transaction(function() use ($request) {
                Permission::create(['name' => $request->name]);
            });
            
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Izin berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified permission.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\View\View
     */
    public function show(Permission $permission)
    {
        $rolesWithPermission = $permission->roles()->orderBy('name')->get();
        return view('admin.permissions.show', compact('permission', 'rolesWithPermission'));
    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\View\View
     */
    public function edit(Permission $permission)
    {
        // Get existing permission groups for suggestions
        $permissionGroups = Permission::all()->map(function($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[1] : null;
        })->unique()->filter()->values();
        
        return view('admin.permissions.edit', compact('permission', 'permissionGroups'));
    }

    /**
     * Update the specified permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission)]
        ]);

        try {
            DB::transaction(function() use ($request, $permission) {
                $permission->update(['name' => $request->name]);
            });
            
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Izin berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to roles
        if ($permission->roles->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Izin ini tidak dapat dihapus karena sedang digunakan oleh ' . $permission->roles->count() . ' peran.');
        }

        try {
            $permission->delete();
            
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Izin berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
