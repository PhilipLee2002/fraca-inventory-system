<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Change from 'description' to 'permissions' to match your database
    protected $fillable = ['name', 'permissions'];
    
    // Add this cast to automatically convert JSON to array
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Relationship with Users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has permission (works with JSON array from your database)
     */
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        
        // If role has wildcard (*), return true for everything
        if (in_array('*', $permissions)) {
            return true;
        }
        
        // Check for specific permission in the array
        return in_array($permission, $permissions);
    }

    /**
     * Get all permissions as array
     */
    public function getPermissionsArray()
    {
        return $this->permissions ?? [];
    }

    /**
     * Assign permission to role
     */
    public function givePermissionTo($permission)
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Revoke permission from role
     */
    public function revokePermissionTo($permission)
    {
        $permissions = $this->permissions ?? [];
        
        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions); // Reindex array
            $this->save();
        }
        
        return $this;
    }

    /**
     * Sync permissions (replace all)
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        $this->save();
        
        return $this;
    }

    /**
     * Check if role has specific name
     */
    public function is($roleName)
    {
        return $this->name === $roleName;
    }
}