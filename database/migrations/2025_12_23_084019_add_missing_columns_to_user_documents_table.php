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
        Schema::table('user_documents', function (Blueprint $table) {
            // Add missing columns for document management
            if (! Schema::hasColumn('user_documents', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('user_documents', 'description')) {
                $table->text('description')->nullable()->after('document_type');
            }
            if (! Schema::hasColumn('user_documents', 'file_name')) {
                $table->string('file_name')->nullable()->after('storage_path');
            }
            if (! Schema::hasColumn('user_documents', 'file_path')) {
                $table->string('file_path')->nullable()->after('file_name');
            }
            if (! Schema::hasColumn('user_documents', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('user_documents', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }
            if (! Schema::hasColumn('user_documents', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('meta');
            }
            if (! Schema::hasColumn('user_documents', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('issue_date');
            }
            if (! Schema::hasColumn('user_documents', 'document_number')) {
                $table->string('document_number')->nullable()->after('expiry_date');
            }
            if (! Schema::hasColumn('user_documents', 'issuing_authority')) {
                $table->string('issuing_authority')->nullable()->after('document_number');
            }
            if (! Schema::hasColumn('user_documents', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_primary');
            }
            if (! Schema::hasColumn('user_documents', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('is_verified');
            }
            if (! Schema::hasColumn('user_documents', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_at');
            }
            if (! Schema::hasColumn('user_documents', 'is_shared')) {
                $table->boolean('is_shared')->default(false)->after('verified_by');
            }
            if (! Schema::hasColumn('user_documents', 'shared_with')) {
                $table->json('shared_with')->nullable()->after('is_shared');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $columns = ['title', 'description', 'file_name', 'file_path', 'file_type', 'file_size',
                'issue_date', 'expiry_date', 'document_number', 'issuing_authority',
                'is_verified', 'verified_at', 'verified_by', 'is_shared', 'shared_with'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('user_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
