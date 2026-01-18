<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'ahmedasad1317@gmail.com',
            'password' => Hash::make('password@123'),
            'email_verified_at' => now(),
        ]);

        // Create team
        $team = Team::create([
            'name' => 'Demo Team',
            'description' => 'A demo team for testing WikiTeqrious',
        ]);

        $team->addUser($user, 'owner');

        // Create tags
        $tags = collect([
            ['name' => 'Getting Started', 'color' => '#22c55e'],
            ['name' => 'Important', 'color' => '#ef4444'],
            ['name' => 'Technical', 'color' => '#3b82f6'],
            ['name' => 'Process', 'color' => '#f59e0b'],
            ['name' => 'Reference', 'color' => '#8b5cf6'],
        ])->map(function ($tag) use ($team) {
            return Tag::create([
                'team_id' => $team->id,
                'name' => $tag['name'],
                'color' => $tag['color'],
            ]);
        });

        // Create collections
        $gettingStarted = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Getting Started',
            'description' => 'Everything you need to know to get started with WikiTeqrious',
            'icon' => '🚀',
            'color' => '#22c55e',
        ]);

        $engineering = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Engineering',
            'description' => 'Technical documentation and guides',
            'icon' => '⚙️',
            'color' => '#3b82f6',
        ]);

        $processes = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Processes',
            'description' => 'Company processes and procedures',
            'icon' => '📋',
            'color' => '#f59e0b',
        ]);

        // Sub-collections
        $backend = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'parent_id' => $engineering->id,
            'name' => 'Backend',
            'description' => 'Backend development guides',
            'icon' => '🔧',
            'color' => '#6366f1',
        ]);

        $frontend = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'parent_id' => $engineering->id,
            'name' => 'Frontend',
            'description' => 'Frontend development guides',
            'icon' => '🎨',
            'color' => '#ec4899',
        ]);

        // Create documents
        $welcomeDoc = Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'collection_id' => $gettingStarted->id,
            'title' => 'Welcome to WikiTeqrious',
            'emoji' => '👋',
            'content' => "# Welcome to WikiTeqrious!\n\nThis is your team's new knowledge base. Here you can:\n\n- Create and organize documents\n- Collaborate with your team\n- Search across all your content\n- Share documents externally\n\n## Getting Started\n\n1. Create your first collection\n2. Add some documents\n3. Invite your team members\n\nHappy documenting!",
            'is_published' => true,
            'published_at' => now(),
        ]);
        $welcomeDoc->tags()->attach($tags->where('name', 'Getting Started')->first()->id);

        Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'collection_id' => $gettingStarted->id,
            'title' => 'Creating Documents',
            'emoji' => '📝',
            'content' => "# Creating Documents\n\nDocuments are the building blocks of your knowledge base.\n\n## How to Create\n\n1. Click the **New Document** button\n2. Give it a title\n3. Start writing!\n\n## Tips\n\n- Use clear, descriptive titles\n- Add to a collection for organization\n- Use tags for cross-cutting topics\n- Link to related documents",
            'is_published' => true,
            'published_at' => now(),
        ]);

        Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'collection_id' => $backend->id,
            'title' => 'API Documentation',
            'emoji' => '🔌',
            'content' => "# API Documentation\n\n## Authentication\n\nAll API requests require authentication using Bearer tokens.\n\n```\nAuthorization: Bearer your-token-here\n```\n\n## Endpoints\n\n### GET /api/documents\n\nReturns a list of all documents.\n\n### POST /api/documents\n\nCreates a new document.\n\n### GET /api/documents/{id}\n\nReturns a specific document.",
            'is_published' => true,
            'published_at' => now(),
        ]);

        Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'collection_id' => $frontend->id,
            'title' => 'UI Components',
            'emoji' => '🧩',
            'content' => "# UI Components\n\nOur component library uses Bootstrap 5.\n\n## Buttons\n\nUse `.btn` class with variants:\n- `.btn-primary`\n- `.btn-secondary`\n- `.btn-success`\n\n## Cards\n\nCards are used for content containers:\n\n```html\n<div class=\"card\">\n  <div class=\"card-body\">\n    Content here\n  </div>\n</div>\n```",
            'is_published' => true,
            'published_at' => now(),
        ]);

        Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'collection_id' => $processes->id,
            'title' => 'Code Review Guidelines',
            'emoji' => '✅',
            'content' => "# Code Review Guidelines\n\n## Before Submitting\n\n- Run all tests locally\n- Self-review your changes\n- Update documentation if needed\n\n## Review Checklist\n\n- [ ] Code follows our style guide\n- [ ] Tests are included\n- [ ] No security vulnerabilities\n- [ ] Performance considered\n\n## Response Time\n\nReviewers should respond within 24 hours.",
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Create a template
        Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'title' => 'Meeting Notes Template',
            'emoji' => '📋',
            'content' => "# Meeting Notes: [Title]\n\n**Date:** [Date]\n**Attendees:** [Names]\n\n## Agenda\n\n1. Item 1\n2. Item 2\n\n## Discussion\n\n### Topic 1\n\nNotes here...\n\n## Action Items\n\n- [ ] Action 1 - @person\n- [ ] Action 2 - @person\n\n## Next Meeting\n\n[Date and time]",
            'is_template' => true,
        ]);

        echo "Demo data seeded successfully!\n";
        echo "Login: demo@example.com / password\n";
    }
}