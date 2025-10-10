<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if tables exist before trying to drop foreign keys
        if (Schema::hasTable('user_roles')) {
            // Drop foreign key constraints first with correct names
            try {
                Schema::table('user_roles', function (Blueprint $table) {
                    $table->dropForeign('fk_ur_user');
                    $table->dropForeign('fk_ur_role');
                });
            } catch (\Exception $e) {
                // Try alternative foreign key names
                try {
                    Schema::table('user_roles', function (Blueprint $table) {
                        $table->dropForeign(['user_id']);
                        $table->dropForeign(['role_id']);
                    });
                } catch (\Exception $e2) {
                    // If foreign keys don't exist, continue
                }
            }
        }

        if (Schema::hasTable('role_permissions')) {
            try {
                Schema::table('role_permissions', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                    $table->dropForeign(['permission_id']);
                });
            } catch (\Exception $e) {
                // If foreign keys don't exist, continue
            }
        }

        // Drop the tables
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key_code')->unique();
            $table->string('description');
            $table->timestamps();
        });

        // Recreate role_permissions table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'permission_id']);
        });

        // Recreate user_roles table
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'role_id']);
        });
    }
};
