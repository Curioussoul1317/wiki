<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teams/Workspaces
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Users table (extending Laravel's default)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Team Members (pivot with roles)
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'editor', 'viewer'])->default('viewer');
            $table->timestamps();
            
            $table->unique(['team_id', 'user_id']);
        });

        // Collections (folders/categories)
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('collections')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('permission', ['public', 'team', 'private'])->default('team');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'parent_id']);
        });

        // Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('collection_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->string('emoji')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'collection_id']);
            $table->index(['team_id', 'parent_id']);
            $table->fullText(['title', 'content']);
        });

        // Document Versions (history)
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->integer('version');
            $table->text('change_summary')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'version']);
        });

        // Comments
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('content');
            $table->json('position')->nullable(); // For inline comments
            $table->boolean('resolved')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'parent_id']);
        });

        // Starred Documents
        Schema::create('starred_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'document_id']);
        });

        // Recent Documents (view history)
        Schema::create('document_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at');

            $table->index(['user_id', 'viewed_at']);
            $table->unique(['user_id', 'document_id']);
        });

        // Attachments/Media
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('path');
            $table->timestamps();

            $table->index('document_id');
        });

        // Share Links
        Schema::create('share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->enum('permission', ['view', 'edit'])->default('view');
            $table->boolean('requires_password')->default(false);
            $table->string('password')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index('token');
        });

        // Document Tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
        });

        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');

            $table->primary(['document_id', 'tag_id']);
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Activity Log
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // created, updated, deleted, viewed, etc.
            $table->morphs('subject'); // The model being acted on
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'created_at']);
        });

        // Password Resets
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('share_links');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('document_views');
        Schema::dropIfExists('starred_documents');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('teams');
    }
};