<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'permissions',
        'description',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public static function getAvailablePermissions(): array
    {
        return [
            // Dashboard
            'dashboard.view' => 'View Dashboard',
            
            // Water Tax
            'water_tax.view' => 'View Water Tax Records',
            'water_tax.manage' => 'Manage Water Tax Records',
            'water_tax.export' => 'Export Water Tax Data',
            
            // Property Tax
            'property_tax.view' => 'View Property Tax Records',
            'property_tax.manage' => 'Manage Property Tax Records',
            'property_tax.export' => 'Export Property Tax Data',
            
            // Grievances
            'grievances.view' => 'View Grievances',
            'grievances.manage' => 'Manage Grievances',
            
            // Content Management
            'sliders.view' => 'View Sliders',
            'sliders.create' => 'Create Sliders',
            'sliders.edit' => 'Edit Sliders',
            'sliders.delete' => 'Delete Sliders',
            'quick_links.view' => 'View Quick Links',
            'quick_links.create' => 'Create Quick Links',
            'quick_links.edit' => 'Edit Quick Links',
            'quick_links.delete' => 'Delete Quick Links',
            
            // Settings
            'settings.view' => 'View Settings',
            'settings.edit' => 'Edit Settings',
            
            // Penalty Settings
            'penalty.manage' => 'Manage Penalty Settings',
            
            // Roles & Admins
            'roles.view' => 'View Roles',
            'roles.create' => 'Create Roles',
            'roles.edit' => 'Edit Roles',
            'roles.delete' => 'Delete Roles',
            'admins.view' => 'View Admins',
            'admins.create' => 'Create Admins',
            'admins.edit' => 'Edit Admins',
            'admins.delete' => 'Delete Admins',
            'admins.impersonate' => 'Secret Login as Admin',

            // Citizens
            'citizens.view' => 'View Citizens',
            'citizens.create' => 'Create Citizens',
            'citizens.edit' => 'Edit Citizens',
            'citizens.delete' => 'Delete Citizens',
            'citizens.impersonate' => 'Secret Login as Citizen',
        ];
    }

    /**
     * Get permissions grouped by category
     */
    public static function getPermissionsByGroup(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'View Dashboard',
            ],
            'Citizens' => [
                'citizens.view' => 'View Citizens',
                'citizens.create' => 'Create Citizens',
                'citizens.edit' => 'Edit Citizens',
                'citizens.delete' => 'Delete Citizens',
                'citizens.impersonate' => 'Secret Login as Citizen',
            ],
            'Water Tax' => [
                'water_tax.view' => 'View Water Tax Records',
                'water_tax.manage' => 'Manage Water Tax Records',
                'water_tax.export' => 'Export Water Tax Data',
            ],
            'Property Tax' => [
                'property_tax.view' => 'View Property Tax Records',
                'property_tax.manage' => 'Manage Property Tax Records',
                'property_tax.export' => 'Export Property Tax Data',
            ],
            'Grievances' => [
                'grievances.view' => 'View Grievances',
                'grievances.manage' => 'Manage Grievances',
            ],
            'Content Management' => [
                'sliders.view' => 'View Sliders',
                'sliders.create' => 'Create Sliders',
                'sliders.edit' => 'Edit Sliders',
                'sliders.delete' => 'Delete Sliders',
                'quick_links.view' => 'View Quick Links',
                'quick_links.create' => 'Create Quick Links',
                'quick_links.edit' => 'Edit Quick Links',
                'quick_links.delete' => 'Delete Quick Links',
            ],
            'Settings' => [
                'settings.view' => 'View Settings',
                'settings.edit' => 'Edit Settings',
            ],
            'Penalty Settings' => [
                'penalty.manage' => 'Manage Penalty Settings',
            ],
            'User Management' => [
                'roles.view' => 'View Roles',
                'roles.create' => 'Create Roles',
                'roles.edit' => 'Edit Roles',
                'roles.delete' => 'Delete Roles',
                'admins.view' => 'View Admins',
                'admins.create' => 'Create Admins',
                'admins.edit' => 'Edit Admins',
                'admins.delete' => 'Delete Admins',
                'admins.impersonate' => 'Secret Login as Admin',
            ],
        ];
    }
}
