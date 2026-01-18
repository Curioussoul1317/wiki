<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if (!$team) {
            return redirect()->route('teams.create');
        }

        // Recent documents
        $recentDocuments = $user->recentDocuments()
            ->where('team_id', $team->id)
            ->limit(10)
            ->get();

        // Starred documents
        $starredDocuments = $user->starredDocuments()
            ->where('team_id', $team->id)
            ->limit(5)
            ->get();

        // Recently updated documents in team
        $updatedDocuments = Document::where('team_id', $team->id)
            ->notTemplates()
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Recent activity
        $activities = ActivityLog::forTeam($team)
            ->with(['user', 'subject'])
            ->recent(7)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Stats
        $stats = [
            'total_documents' => Document::where('team_id', $team->id)->notTemplates()->count(),
            'total_collections' => $team->collections()->count(),
            'my_documents' => Document::where('team_id', $team->id)
                ->where('created_by', $user->id)
                ->count(),
        ];

        return view('dashboard.index', compact(
            'team',
            'recentDocuments',
            'starredDocuments',
            'updatedDocuments',
            'activities',
            'stats'
        ));
    }
}