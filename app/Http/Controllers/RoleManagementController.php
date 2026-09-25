<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountApprovalNotification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $query = User::query();
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('id')->paginate(50)->withQueryString();
        return view('admin.users', ['users' => $users, 'search' => $search ?? '']);
    }

    public function editView(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        return view('admin.user-edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
        ]);

        $user = User::findOrFail($id);
        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function updateRole(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'role' => 'required|string|in:admin,property_custodian,teacher',
        ]);

        $user = User::findOrFail($id);
        $user->role = $data['role'];
        $user->save();

        return redirect()->back()->with('success', 'Role updated successfully');
    }

    public function toggleStatus(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', 'User '.$status.' successfully');
    }

    public function destroy(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $currentUser = $request->user();
        $user = User::findOrFail($id);

        if ($user->id === $currentUser->id) {
            return redirect()->back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function approveUser(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();

        $user->notify(new AccountApprovalNotification($user, 'approved'));

        return redirect()->back()->with('success', 'User approved successfully.');
    }

    public function rejectUser(Request $request, $id)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        $user->is_active = false;
        $user->save();

        $user->notify(new AccountApprovalNotification($user, 'rejected'));

        return redirect()->back()->with('success', 'User rejected successfully.');
    }

    public function export(Request $request)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        $users = User::orderBy('id')->get();

        if ($request->input('format') === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf_users', compact('users'))
                ->download('users_'.now()->format('Ymd_His').'.pdf');
        }

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'email', 'role', 'status', 'created_at']);
            foreach ($users as $u) {
                $status = $u->is_active ? 'active' : 'inactive';
                fputcsv($handle, [$u->id, $u->name, $u->email, $u->role, $status, $u->created_at]);
            }
            fclose($handle);
        });

        $filename = 'users_'.now()->format('Ymd_His').'.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}

