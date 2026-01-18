<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teams = $user->teams()->withPivot('role')->get();

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $team->addUser($user, 'owner');

        session(['current_team_id' => $team->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        $this->authorizeTeam($team, 'view');

        $team->load(['users' => function ($q) {
            $q->orderBy('name');
        }]);

        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $this->authorizeTeam($team, 'admin');

        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorizeTeam($team, 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $this->authorizeTeam($team, 'owner');

        $team->documents()->delete();
        $team->collections()->delete();
        $team->tags()->delete();
        $team->activityLogs()->delete();
        $team->users()->detach();
        $team->delete();

        $user = auth()->user();
        $newTeam = $user->teams()->first();

        if ($newTeam) {
            session(['current_team_id' => $newTeam->id]);
            return redirect()->route('dashboard')
                ->with('success', 'Team deleted successfully.');
        }

        return redirect()->route('teams.create')
            ->with('info', 'Please create a new team to continue.');
    }

    public function switch(Team $team)
    {
        $user = auth()->user();

        if (!$user->belongsToTeam($team)) {
            abort(403, 'You are not a member of this team.');
        }

        $user->switchTeam($team);

        return redirect()->route('dashboard')
            ->with('success', 'Switched to ' . $team->name);
    }

    public function members(Team $team)
    {
        $this->authorizeTeam($team, 'admin');

        $team->load(['users' => function ($q) {
            $q->orderBy('name');
        }]);

        return view('teams.members', compact('team'));
    }

    public function inviteMember(Request $request, Team $team)
    {
        $this->authorizeTeam($team, 'admin');
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,editor,viewer',
        ]);
    
        // Check if user already exists
        $user = User::where('email', $validated['email'])->first();
    
        if ($user) {
            // User exists, check if already in team
            if ($team->hasUser($user)) {
                return back()->withErrors(['email' => 'This user is already a member of the team.']);
            }
        } else {
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);
        }
    
        $team->addUser($user, $validated['role']);
    
        return back()->with('success', 'User "' . $user->name . '" added to team successfully.');
    }

    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        $this->authorizeTeam($team, 'admin');

        $validated = $request->validate([
            'role' => 'required|in:admin,editor,viewer',
        ]);

        if ($user->isTeamOwner($team)) {
            return back()->withErrors(['error' => 'Cannot change the owner\'s role.']);
        }

        $team->updateUserRole($user, $validated['role']);

        return back()->with('success', 'Role updated successfully.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorizeTeam($team, 'admin');

        if ($user->isTeamOwner($team)) {
            return back()->withErrors(['error' => 'Cannot remove the team owner.']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot remove yourself from the team.']);
        }

        $team->removeUser($user);

        return back()->with('success', 'Member removed from team.');
    }

    public function leave(Team $team)
    {
        $user = auth()->user();

        if (!$user->belongsToTeam($team)) {
            abort(403);
        }

        if ($user->isTeamOwner($team)) {
            return back()->withErrors(['error' => 'As the owner, you must transfer ownership before leaving the team.']);
        }

        $team->removeUser($user);

        $newTeam = $user->teams()->first();
        if ($newTeam) {
            session(['current_team_id' => $newTeam->id]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'You have left the team.');
    }

    protected function authorizeTeam(Team $team, string $level = 'view'): void
    {
        $user = auth()->user();

        if (!$user->belongsToTeam($team)) {
            abort(403, 'You are not a member of this team.');
        }

        if ($level === 'admin' && !$user->isTeamAdmin($team)) {
            abort(403, 'You must be an admin to perform this action.');
        }

        if ($level === 'owner' && !$user->isTeamOwner($team)) {
            abort(403, 'You must be the owner to perform this action.');
        }
    }
}