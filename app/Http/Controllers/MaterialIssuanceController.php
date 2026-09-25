<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\MaterialIssue;
use App\Models\InventoryTransaction;
use App\Models\Department;
use App\Notifications\IssueApprovedNotification;
use App\Notifications\IssueRejectedNotification;
use App\Notifications\IssueRequestedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialIssuanceController extends Controller
{
    public function index()
    {
        $query = MaterialIssue::with(['item', 'issuedTo'])->latest();
        if (auth()->user()?->isTeacher()) {
            $query->where('issued_to_user_id', auth()->id());
        }

        $issues = $query->paginate(25);
        return response()->json($issues);
    }

    public function show($id)
    {
        $issue = MaterialIssue::with(['item', 'issuedTo'])->findOrFail($id);
        return response()->json($issue);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Only teacher users can create material requests.
        if (! $user || ! $user->isTeacher()) {
            return response()->json(['error' => 'Only teachers can request items.'], 403);
        }

        $data = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'issued_to_user_id' => 'nullable', // may be numeric id or username/email
            'department_id' => 'nullable|integer',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|min:5|max:500',
        ]);

        $data['notes'] = $data['purpose'];
        unset($data['purpose']);

        // Resolve issued_to_user_id which may be a numeric id or a username/email
        $issuedRaw = $request->input('issued_to_user_id');
        $issuedResolved = null;
        if (!is_null($issuedRaw) && $issuedRaw !== '') {
            if (is_numeric($issuedRaw)) {
                $issuedResolved = (int) $issuedRaw;
                if (!User::where('id', $issuedResolved)->exists()) {
                    return response()->json(['error' => 'Issued user id not found'], 422);
                }
            } else {
                // try email or name match
                $u = User::where('email', $issuedRaw)->orWhere('name', $issuedRaw)->first();
                if ($u) {
                    $issuedResolved = $u->id;
                } else {
                    return response()->json(['error' => 'Issued user not found by name or email'], 422);
                }
            }
        }
        // default to current user when not provided
        if (is_null($issuedResolved) && $request->user()) {
            $issuedResolved = $request->user()->id;
        }
        $data['issued_to_user_id'] = $issuedResolved;

        // Prevent duplicate pending requests for same item/user/department
        $exists = MaterialIssue::where('item_id', $data['item_id'])
            ->where('issued_to_user_id', $data['issued_to_user_id'] ?? null)
            ->where('department_id', $data['department_id'] ?? null)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Duplicate pending request'], 409);
        }

        $issue = MaterialIssue::create(array_merge($data, ['status' => 'pending']));

        $managerRoleUsers = User::whereIn('role', ['admin', 'property_custodian'])->get();
        foreach ($managerRoleUsers as $manager) {
            try {
                $manager->notify(new IssueRequestedNotification($issue));
            } catch (\Exception $e) {
            }
        }

        // warn if requested > available stock (not blocking request)
        $item = Item::find($data['item_id']);
        $over = false;
        if ($item && $item->quantity < $data['quantity']) {
            $over = true;
        }

        // return issue attributes at top-level for API compatibility plus over_requested flag
        $payload = array_merge($issue->toArray(), ['over_requested' => $over]);
        return response()->json($payload, 201);
    }

    public function approve(Request $request, $id)
    {
        $issue = MaterialIssue::findOrFail($id);

        if ($issue->status !== 'pending') {
            return response()->json(['error' => 'Only pending requests can be approved'], 422);
        }

        // Authorization: only admin or property custodian may alter inventory quantities
        if (! $request->user() || ! ($request->user()->isAdmin() || $request->user()->isPropertyCustodian())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return DB::transaction(function () use ($issue, $request) {
            $item = Item::lockForUpdate()->findOrFail($issue->item_id);

            if ($item->quantity < $issue->quantity) {
                return response()->json(['error' => 'Insufficient stock'], 422);
            }

            // Deduct stock
            $item->quantity -= $issue->quantity;
            $item->save();

            // Update issue record
            $issue->status = 'approved';
            $issue->approved_at = now();
            $issue->approved_by = $request->user() ? $request->user()->id : null;
            $issue->save();

            // notify requester/issued user
            if ($issue->issuedTo) {
                try { $issue->issuedTo->notify(new IssueApprovedNotification($issue)); } catch (\Exception $e) { }
            }

            // Record inventory transaction for audit trail
            InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => $issue->issued_to_user_id,
                'department_id' => $issue->department_id,
                'transaction_type' => 'issue',
                'quantity' => $issue->quantity,
                'notes' => 'Issued via approval id '.$issue->id,
            ]);

            return response()->json($issue);
        });
    }

    public function reject(Request $request, $id)
    {
        $issue = MaterialIssue::findOrFail($id);

        if ($issue->status !== 'pending') {
            return response()->json(['error' => 'Only pending requests can be rejected'], 422);
        }

        if (! $request->user() || ! ($request->user()->isAdmin() || $request->user()->isApprover() || $request->user()->isPropertyCustodian())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->merge(['reason' => $request->input('reason', $request->input('notes'))]);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $issue->status = 'rejected';
        $issue->rejection_reason = $data['reason'];
        $issue->save();

        // notify requester/issued user
        if ($issue->issuedTo) {
            try { $issue->issuedTo->notify(new IssueRejectedNotification($issue)); } catch (\Exception $e) { }
        }

        return response()->json($issue);
    }

    // UI view for issues list
    public function issuesView()
    {
        return view('inventory.issues', ['departments' => Department::where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, $id)
    {
        if ($request->isMethod('put')) {
            return response()->json(['error' => 'Material issue requests are immutable. Reject this request and create a new one instead.'], 405);
        }

        $issue = MaterialIssue::findOrFail($id);
        $user = $request->user();

        if (!$user || !$user->isTeacher() || $issue->issued_to_user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($issue->status !== 'pending') {
            return response()->json(['error' => 'Only pending requests can be edited.'], 422);
        }

        $data = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|min:5|max:500',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $issue->update([
            'item_id' => $data['item_id'],
            'quantity' => $data['quantity'],
            'department_id' => $data['department_id'] ?? null,
            'notes' => $data['purpose'],
        ]);

        return response()->json($issue->fresh(['item', 'issuedTo']), 200);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['error' => 'Material issue requests are immutable and cannot be deleted.'], 405);
    }
}
