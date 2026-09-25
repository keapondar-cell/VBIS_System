<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Notifications</h1>
                <p class="text-sm text-slate-500">All notifications in one place.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.notifications.index', ['filter' => 'all']) }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ $filter === 'all' ? 'border-sky-600 bg-sky-50 text-sky-700' : 'border-slate-200 text-slate-600' }}">All ({{ $total_count }})</a>
                <a href="{{ route('inventory.notifications.index', ['filter' => 'unread']) }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ $filter === 'unread' ? 'border-amber-600 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600' }}">Unread ({{ $unread_count }})</a>
                <a href="{{ route('inventory.notifications.index', ['filter' => 'read']) }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ $filter === 'read' ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600' }}">Read ({{ $read_count }})</a>
            </div>
        </div>

        @if (empty($notifications))
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-500">
                No notifications found.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    @php
                        $message = $notification['message'] ?? 'You have a new notification.';
                        $read = $notification['read'] ?? false;
                        $createdAt = $notification['created_at'] ?? null;
                        $notificationId = $notification['id'] ?? null;
                        $issueId = $notification['issue_id'] ?? null;
                        $transactionId = $notification['transaction_id'] ?? null;
                        $userId = $notification['user_id'] ?? null;
                        $status = $notification['status'] ?? null;
                        $destination = $issueId
                            ? route('inventory.issues.list', ['notification_issue' => $issueId])
                            : ($transactionId ? route('inventory.transactions.list', ['notification_transaction' => $transactionId])
                                : ($userId ? ($status === 'pending' ? route('admin.users') : route('login')) : route('inventory.notifications.index')));
                    @endphp
                    <a href="{{ $destination }}" data-notification-id="{{ $notificationId }}" class="notification-entry block rounded-2xl border {{ $read ? 'border-slate-200 bg-white' : 'border-sky-100 bg-sky-50' }} p-4 shadow-sm transition hover:border-sky-300 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 inline-flex h-10 w-10 items-center justify-center rounded-full {{ $read ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700' }} text-lg">
                                    {{ $read ? '✓' : '•' }}
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-800">{{ $message }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('F d, Y h:i A') : 'Recently' }}
                                    </p>
                                </div>
                            </div>
                            @if (! $read)
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">New</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    <script>
        document.querySelectorAll('.notification-entry[data-notification-id]').forEach((entry) => {
            entry.addEventListener('click', async (event) => {
                event.preventDefault();
                const destination = entry.href;
                const notificationId = entry.dataset.notificationId;
                try {
                    await fetch('{{ url('/inventory/notifications') }}/' + encodeURIComponent(notificationId) + '/read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                } finally {
                    window.location.href = destination;
                }
            });
        });
    </script>
</x-app-layout>
