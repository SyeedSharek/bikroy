<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AllResponse;
    // Search of Product
    public function role_search(Request $request)
    {
        // Extract the search term from the request
        $search = $request->name;

        // Search for roles where the guard is 'admin' and the name matches the search term
        $roles = Role::where('guard_name', 'admin')
            ->where('name', 'like', "%$search%")
            ->orWhereHas('permissions', function ($query) use ($search) {
                // Search for roles that have permissions with names matching the search term
                $query->where('name', 'like', "%$search%");
            })
            ->with('permissions') // Eager load the permissions relationship
            ->get();

        // Check if roles were found
        if (count($roles) > 0) {
            // Return a JSON response with the found roles
            return $this->PostsResponse($roles, 200);
        }

    }


    // All Permissions
    public function permissions()
    {
        // Retrieve all permissions
        $permissions = Permission::all();

        // Check if permissions were found
        if (count($permissions) > 0) {
            // Return a JSON response with the found permissions
            return $this->PostsResponse($permissions, 200);
        }

        // If no permissions were found, return a response indicating that
        return $this->Response(false, "Permissions not found.", 404);
    }


    // All Roles Index in Admin
    public function index()
    {
        // Check if the authenticated admin user has permission to list roles
        if (auth('admin')->user()->hasPermissionTo('role list')) {
            // Retrieve roles with their associated permissions, limited to the 'admin' guard
            $roles = Role::with('permissions')->where('guard_name', 'admin')->paginate(10);

            // Check if roles were found
            if (count($roles) > 0) {
                // Return a JSON response with the found roles
                return $this->PostsResponse($roles, 200);
            }

            // If no roles were found, return a response indicating that
            return $this->Response(false, "Roles not found.", 404);

        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }


    // All Roles
    public function allRole()
    {
        // Retrieve all roles in the 'admin' guard
        $roles = Role::where('guard_name', 'admin')->get();

        // Check if roles were found
        if (count($roles) > 0) {
            // Return a JSON response with the found roles
            return $this->PostsResponse($roles, 200);
        }

        // If no roles were found, return a response indicating that
        return $this->Response(false, "Roles not found.", 404);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    // All Permissions
    // public function create()
    // {
    //     $permissions = Permission::all();

    //     return response()->json(
    //         [
    //             'permissions' => $permissions,
    //         ],
    //         200,
    //     );
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */

    // Create Role
    public function store(RoleCreateRequest $request)
    {
        // Check if the authenticated admin user has permission to create a role
        if (auth('admin')->user()->hasPermissionTo('create role')) {

            // Create a new role
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'admin',
            ]);

            // Sync the associated permissions for the role
            $role->syncPermissions($request->permissions);

            // Check if the role was created successfully
            if ($role) {
                // Return a response indicating success
                return $this->Response(true, "Role Created", 201);
            }

            // If the role creation fails, return a response indicating that
            return $this->Response(false, "Role not added.", 400);

        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    // Edit Role
    public function edit($id)
    {
        // Retrieve all permissions
        $permissions = Permission::all();

        // Retrieve the role with associated permissions by its ID
        $role = Role::with('permissions')->find($id);

        // Check if the role was found
        if ($role) {
            // Extract the IDs of permissions associated with the role
            $checkedPermissions = $role->permissions()->pluck('id')->toArray();

            // Return a JSON response with permissions, role name, and checked permissions
            return response()->json([
                'permissions' => $permissions,
                'role' => $role->name,
                'checkedPermissions' => $checkedPermissions,
            ], 200);
        }

        // If the role was not found, return a response indicating that
        return $this->Response(false, "Role not found.", 404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // Update Role
    public function update(RoleUpdateRequest $request, $id)
    {
        // Check if the authenticated admin user has permission to edit a role
        if (auth('admin')->user()->hasPermissionTo('edit role')) {
            // Find the role by its ID
            $role = Role::find($id);

            // Update the role's name
            $role->update([
                'name' => $request->name,
            ]);

            // Sync the associated permissions for the role
            $role->syncPermissions($request->permissions);

            // Check if the role was updated successfully
            if ($role) {
                // Return a response indicating success
                return $this->Response(true, "Role Updated", 200);
            }

            // If the role update fails, return a response indicating that
            return $this->Response(false, "Role not Updated.", 400);

        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // Delete Role
    public function destroy($id)
    {
        // Check if the authenticated admin user has permission to delete a role
        if (auth('admin')->user()->hasPermissionTo('delete role')) {
            // Find the role by its ID
            $role = Role::find($id);

            // Check if the role was found
            if ($role != null) {
                // Delete the role
                $role->delete();

                // Return a response indicating success
                return $this->Response(true, "Role deleted successfully.", 200);
            }

            // If the role was not found, return a response indicating that
            return $this->Response(false, "Role not found.", 404);

        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }

}
