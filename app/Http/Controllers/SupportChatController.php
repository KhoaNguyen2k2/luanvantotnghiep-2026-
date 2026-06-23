<?php

namespace App\Http\Controllers;

use App\Models\InternalConversation;
use App\Models\InternalMessage;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\SupportNotificationRead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportChatController extends Controller
{
    private const ADVISOR_POSITION = 'Nhân viên tư vấn';

    public function customerState(): JsonResponse
    {
        $customer = $this->customerUser();
        $conversation = $this->openCustomerConversation($customer->id);

        return response()->json($this->conversationPayload($conversation, $customer));
    }

    public function customerStart(Request $request): JsonResponse
    {
        $customer = $this->customerUser();
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $conversation = DB::transaction(function () use ($customer, $data) {
            $conversation = $this->openCustomerConversation($customer->id);

            if (! $conversation) {
                $conversation = SupportConversation::create([
                    'customer_id' => $customer->id,
                    'status' => SupportConversation::STATUS_PENDING,
                ]);
            }

            if (! $conversation->staff_id && $conversation->status === SupportConversation::STATUS_PENDING) {
                $this->assignAvailableAdvisor($conversation);
            }

            if (! empty($data['message']) && ! $conversation->messages()->where('sender_type', SupportMessage::SENDER_CUSTOMER)->exists()) {
                $conversation->messages()->create([
                    'sender_id' => $customer->id,
                    'sender_type' => SupportMessage::SENDER_CUSTOMER,
                    'message' => trim($data['message']),
                ]);
            }

            return $conversation->fresh(['customer', 'staff', 'messages.sender']);
        });

        return response()->json($this->conversationPayload($conversation, $customer));
    }

    public function customerMessage(Request $request): JsonResponse
    {
        $customer = $this->customerUser();
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $conversation = $this->openCustomerConversation($customer->id);
        abort_if(! $conversation, 404);

        if ($conversation->status === SupportConversation::STATUS_CLOSED) {
            return response()->json([
                'message' => 'Cuộc tư vấn đã kết thúc.',
            ], 422);
        }

        if (! $conversation->staff_id && $conversation->status === SupportConversation::STATUS_PENDING) {
            $this->assignAvailableAdvisor($conversation);
        }

        $conversation->messages()->create([
            'sender_id' => $customer->id,
            'sender_type' => SupportMessage::SENDER_CUSTOMER,
            'message' => trim($data['message']),
        ]);

        return response()->json($this->conversationPayload($conversation->fresh(['customer', 'staff', 'messages.sender']), $customer));
    }

    public function customerClose(): JsonResponse
    {
        $customer = $this->customerUser();
        $conversation = $this->openCustomerConversation($customer->id);
        abort_if(! $conversation, 404);

        $this->closeConversation($conversation, 'Khách hàng đã kết thúc cuộc tư vấn.');

        return response()->json($this->conversationPayload($conversation->fresh(['customer', 'staff', 'messages.sender']), $customer));
    }

    public function staffState(): JsonResponse
    {
        $operator = $this->operatorUser();

        return response()->json($this->operatorStatePayload($operator));
    }

    public function staffAccept(SupportConversation $conversation): JsonResponse
    {
        $operator = $this->operatorUser();

        DB::transaction(function () use ($conversation, $operator) {
            $conversation->refresh();

            if ($operator->utype === 'ADM') {
                abort_unless(
                    ($conversation->staff_id === $operator->id && $conversation->status === SupportConversation::STATUS_ASSIGNED)
                    || (! $conversation->staff_id && $conversation->status === SupportConversation::STATUS_PENDING),
                    403
                );

                abort_if(
                    SupportConversation::where('staff_id', $operator->id)
                        ->whereIn('status', [SupportConversation::STATUS_ASSIGNED, SupportConversation::STATUS_ACTIVE])
                        ->where('id', '!=', $conversation->id)
                        ->exists(),
                    422,
                    'Bạn đang tư vấn một khách hàng khác.'
                );

                $conversation->staff_id = $operator->id;
            } else {
                abort_unless($conversation->status === SupportConversation::STATUS_PENDING && ! $conversation->staff_id, 403);
                abort_if(
                    SupportConversation::where('staff_id', $operator->id)
                        ->whereIn('status', [SupportConversation::STATUS_ASSIGNED, SupportConversation::STATUS_ACTIVE])
                        ->where('id', '!=', $conversation->id)
                        ->exists(),
                    422,
                    'Bạn đang tư vấn một khách hàng khác.'
                );
                $conversation->staff_id = $operator->id;
            }

            $conversation->status = SupportConversation::STATUS_ACTIVE;
            $conversation->accepted_at = now();
            $conversation->save();

            $conversation->messages()->create([
                'sender_id' => $operator->id,
                'sender_type' => SupportMessage::SENDER_SYSTEM,
                'message' => $operator->name . ' đã tham gia tư vấn.',
            ]);

            $this->markSupportRead($conversation, $operator);
        });

        return response()->json($this->conversationPayload($conversation->fresh(['customer', 'staff', 'messages.sender']), $operator));
    }

    public function staffDecline(SupportConversation $conversation): JsonResponse
    {
        $operator = $this->operatorUser();
        abort_unless($operator->utype === 'ADM' && $conversation->staff_id === $operator->id && $conversation->status === SupportConversation::STATUS_ASSIGNED, 403);

        DB::transaction(function () use ($conversation, $operator) {
            $rejected = $conversation->rejected_staff_ids ?? [];
            $rejected[] = $operator->id;

            $conversation->rejected_staff_ids = array_values(array_unique($rejected));
            $conversation->staff_id = null;
            $conversation->status = SupportConversation::STATUS_PENDING;
            $conversation->save();

            $this->assignAvailableAdvisor($conversation);
        });

        return response()->json(['ok' => true]);
    }

    public function staffOpen(SupportConversation $conversation): JsonResponse
    {
        $operator = $this->operatorUser();
        abort_unless($this->canViewSupportConversation($operator, $conversation), 403);

        $this->markSupportRead($conversation, $operator);

        return response()->json($this->conversationPayload($conversation->load(['customer', 'staff', 'messages.sender']), $operator));
    }

    public function staffMessage(Request $request, SupportConversation $conversation): JsonResponse
    {
        $operator = $this->operatorUser();
        abort_unless($conversation->staff_id === $operator->id, 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        if ($conversation->status !== SupportConversation::STATUS_ACTIVE) {
            return response()->json(['message' => 'Bạn cần nhận tư vấn trước khi gửi tin nhắn.'], 422);
        }

        $conversation->messages()->create([
            'sender_id' => $operator->id,
            'sender_type' => SupportMessage::SENDER_STAFF,
            'message' => trim($data['message']),
        ]);

        $this->markSupportRead($conversation, $operator);

        return response()->json($this->conversationPayload($conversation->fresh(['customer', 'staff', 'messages.sender']), $operator));
    }

    public function staffClose(SupportConversation $conversation): JsonResponse
    {
        $operator = $this->operatorUser();
        abort_unless($conversation->staff_id === $operator->id, 403);

        $this->closeConversation($conversation, 'Nhân viên đã kết thúc cuộc tư vấn.');
        $this->markSupportRead($conversation, $operator);

        return response()->json($this->conversationPayload($conversation->fresh(['customer', 'staff', 'messages.sender']), $operator));
    }

    public function internalOpen(?User $staff = null): JsonResponse
    {
        $operator = $this->operatorUser();
        $thread = $this->resolveInternalConversation($operator, $staff);

        InternalMessage::where('internal_conversation_id', $thread->id)
            ->where('sender_id', '!=', $operator->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($this->internalPayload($thread->fresh(['admin', 'staff', 'messages.sender']), $operator));
    }

    public function internalMessage(Request $request, ?User $staff = null): JsonResponse
    {
        $operator = $this->operatorUser();
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $thread = $this->resolveInternalConversation($operator, $staff);
        $thread->messages()->create([
            'sender_id' => $operator->id,
            'message' => trim($data['message']),
        ]);

        return response()->json($this->internalPayload($thread->fresh(['admin', 'staff', 'messages.sender']), $operator));
    }

    private function customerUser(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->utype === 'USR', 403);

        return $user;
    }

    private function operatorUser(): User
    {
        $user = Auth::user();
        abort_unless($user && ($this->isSuperAdmin($user) || $user->utype === 'ADM'), 403);

        return $user;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->utype === 'ADMM' && strtolower($user->email) === 'admint@lvtn.vn';
    }

    private function isAdvisor(User $user): bool
    {
        return mb_strtolower(trim((string) $user->position)) === mb_strtolower(self::ADVISOR_POSITION);
    }

    private function openCustomerConversation(int $customerId): ?SupportConversation
    {
        return SupportConversation::with(['customer', 'staff', 'messages.sender'])
            ->where('customer_id', $customerId)
            ->whereIn('status', [
                SupportConversation::STATUS_PENDING,
                SupportConversation::STATUS_ASSIGNED,
                SupportConversation::STATUS_ACTIVE,
            ])
            ->latest()
            ->first();
    }

    private function assignAvailableAdvisor(SupportConversation $conversation): void
    {
        $rejected = $conversation->rejected_staff_ids ?? [];

        $advisor = User::where('utype', 'ADM')
            ->orderBy('id')
            ->get()
            ->first(function ($user) use ($rejected) {
                if (! $this->isAdvisor($user) || in_array($user->id, $rejected, true)) {
                    return false;
                }

                return ! SupportConversation::where('staff_id', $user->id)
                    ->whereIn('status', [SupportConversation::STATUS_ASSIGNED, SupportConversation::STATUS_ACTIVE])
                    ->exists();
            });

        if (! $advisor) {
            $conversation->staff_id = null;
            $conversation->status = SupportConversation::STATUS_PENDING;
            $conversation->save();
            return;
        }

        $conversation->staff_id = $advisor->id;
        $conversation->status = SupportConversation::STATUS_ASSIGNED;
        $conversation->save();
    }

    private function closeConversation(SupportConversation $conversation, string $message): void
    {
        if ($conversation->status === SupportConversation::STATUS_CLOSED) {
            return;
        }

        $conversation->status = SupportConversation::STATUS_CLOSED;
        $conversation->closed_at = now();
        $conversation->save();

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'sender_type' => SupportMessage::SENDER_SYSTEM,
            'message' => $message,
        ]);
    }

    private function operatorStatePayload(User $operator): array
    {
        $active = SupportConversation::with(['customer', 'staff', 'messages.sender'])
            ->where('staff_id', $operator->id)
            ->whereIn('status', [SupportConversation::STATUS_ASSIGNED, SupportConversation::STATUS_ACTIVE])
            ->latest()
            ->first();

        if (! $active && $operator->utype === 'ADM' && $this->isAdvisor($operator)) {
            $active = DB::transaction(function () use ($operator) {
                $pending = SupportConversation::where('status', SupportConversation::STATUS_PENDING)
                    ->whereNull('staff_id')
                    ->oldest()
                    ->get()
                    ->first(fn ($item) => ! in_array($operator->id, $item->rejected_staff_ids ?? [], true));

                if (! $pending) {
                    return null;
                }

                $pending->staff_id = $operator->id;
                $pending->status = SupportConversation::STATUS_ASSIGNED;
                $pending->save();

                return $pending->fresh(['customer', 'staff', 'messages.sender']);
            });
        }

        $requests = collect();
        if ($operator->utype === 'ADMM') {
            $requests = SupportConversation::with(['customer', 'staff'])
                ->where('status', SupportConversation::STATUS_PENDING)
                ->whereNull('staff_id')
                ->oldest()
                ->take(10)
                ->get();
        } else {
            $pendingRequests = SupportConversation::with(['customer', 'staff'])
                ->where('status', SupportConversation::STATUS_PENDING)
                ->whereNull('staff_id')
                ->oldest()
                ->take(10)
                ->get()
                ->reject(fn ($item) => in_array($operator->id, $item->rejected_staff_ids ?? [], true));

            $requests = $pendingRequests->values();

            if ($active && $active->status === SupportConversation::STATUS_ASSIGNED) {
                $requests = collect([$active])->merge($requests);
            }
        }

        $historyQuery = SupportConversation::with(['customer', 'staff'])
            ->where('status', SupportConversation::STATUS_CLOSED)
            ->latest('closed_at')
            ->take(12);

        if ($operator->utype === 'ADM') {
            $historyQuery->where('staff_id', $operator->id);
        }

        $internalThreads = $this->internalThreadsFor($operator);
        $internalUnread = $internalThreads->sum('unread_count');

        return [
            'conversation' => $active ? $this->conversationPayload($active, $operator)['conversation'] : null,
            'messages' => $active ? $this->conversationPayload($active, $operator)['messages'] : [],
            'requests' => $requests->map(fn ($item) => $this->supportSummary($item, $operator))->values(),
            'history' => $historyQuery->get()->map(fn ($item) => $this->supportSummary($item, $operator))->values(),
            'internal_threads' => $internalThreads->values(),
            'notification_count' => $this->supportUnreadCount($operator, $requests, $active) + $internalUnread,
            'is_super_admin' => $operator->utype === 'ADMM',
        ];
    }

    private function canViewSupportConversation(User $operator, SupportConversation $conversation): bool
    {
        if ($operator->utype === 'ADMM') {
            return true;
        }

        return $conversation->staff_id === $operator->id
            || ($conversation->status === SupportConversation::STATUS_PENDING && ! $conversation->staff_id);
    }

    private function supportSummary(SupportConversation $conversation, User $viewer): array
    {
        return [
            'id' => $conversation->id,
            'customer_name' => $conversation->customer?->name,
            'staff_name' => $conversation->staff?->name,
            'status' => $conversation->status,
            'time' => optional($conversation->updated_at)->format('H:i d/m/Y'),
            'unread_count' => $this->isSupportUnreadFor($conversation, $viewer) ? 1 : 0,
        ];
    }

    private function supportUnreadCount(User $operator, $requests, ?SupportConversation $active): int
    {
        $items = collect($requests);

        if ($active) {
            $items->push($active);
        }

        return $items
            ->unique('id')
            ->filter(fn ($conversation) => $this->isSupportUnreadFor($conversation, $operator))
            ->count();
    }

    private function isSupportUnreadFor(SupportConversation $conversation, User $operator): bool
    {
        $latestCustomerMessage = SupportMessage::where('support_conversation_id', $conversation->id)
            ->where('sender_type', SupportMessage::SENDER_CUSTOMER)
            ->latest()
            ->first();

        if (! $latestCustomerMessage && $conversation->status !== SupportConversation::STATUS_PENDING && $conversation->status !== SupportConversation::STATUS_ASSIGNED) {
            return false;
        }

        $read = SupportNotificationRead::where('support_conversation_id', $conversation->id)
            ->where('user_id', $operator->id)
            ->first();

        if (! $read || ! $read->read_at) {
            return true;
        }

        $latestAt = $latestCustomerMessage?->created_at ?? $conversation->updated_at;

        return $latestAt && $latestAt->gt($read->read_at);
    }

    private function markSupportRead(SupportConversation $conversation, User $operator): void
    {
        SupportNotificationRead::updateOrCreate(
            [
                'support_conversation_id' => $conversation->id,
                'user_id' => $operator->id,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    private function resolveInternalConversation(User $operator, ?User $staff): InternalConversation
    {
        if ($operator->utype === 'ADMM') {
            abort_unless($staff && $staff->utype === 'ADM', 404);
            $admin = $operator;
            $employee = $staff;
        } else {
            $admin = User::where('utype', 'ADMM')
                ->whereRaw('LOWER(email) = ?', ['admint@lvtn.vn'])
                ->firstOrFail();
            $employee = $operator;
        }

        return InternalConversation::firstOrCreate([
            'admin_id' => $admin->id,
            'staff_id' => $employee->id,
        ]);
    }

    private function internalThreadsFor(User $operator)
    {
        if ($operator->utype === 'ADMM') {
            return User::where('utype', 'ADM')
                ->orderBy('name')
                ->get()
                ->map(function ($staff) use ($operator) {
                    $thread = InternalConversation::firstOrCreate([
                        'admin_id' => $operator->id,
                        'staff_id' => $staff->id,
                    ]);

                    return $this->internalSummary($thread->load(['staff', 'messages']), $operator);
                });
        }

        $thread = $this->resolveInternalConversation($operator, null);

        return collect([$this->internalSummary($thread->load(['admin', 'messages']), $operator)]);
    }

    private function internalSummary(InternalConversation $thread, User $viewer): array
    {
        $other = $viewer->utype === 'ADMM' ? $thread->staff : $thread->admin;
        $latest = $thread->messages->sortByDesc('created_at')->first();

        return [
            'id' => $thread->id,
            'staff_id' => $thread->staff_id,
            'other_name' => $other?->name,
            'latest_message' => $latest?->message,
            'latest_time' => optional($latest?->created_at)->format('H:i d/m/Y'),
            'unread_count' => $thread->messages
                ->where('sender_id', '!=', $viewer->id)
                ->whereNull('read_at')
                ->count(),
        ];
    }

    private function internalPayload(InternalConversation $thread, User $viewer): array
    {
        $thread->loadMissing(['admin', 'staff', 'messages.sender']);
        $other = $viewer->utype === 'ADMM' ? $thread->staff : $thread->admin;

        return [
            'thread' => [
                'id' => $thread->id,
                'staff_id' => $thread->staff_id,
                'other_name' => $other?->name,
            ],
            'messages' => $thread->messages
                ->sortBy('created_at')
                ->values()
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'sender_name' => $message->sender?->name,
                    'message' => $message->message,
                    'mine' => $message->sender_id === $viewer->id,
                    'time' => optional($message->created_at)->format('H:i'),
                ]),
        ];
    }

    private function conversationPayload(?SupportConversation $conversation, User $viewer): array
    {
        if (! $conversation) {
            return [
                'conversation' => null,
                'messages' => [],
                'notice' => null,
            ];
        }

        $conversation->loadMissing(['customer', 'staff', 'messages.sender']);

        $notice = null;
        if ($conversation->status === SupportConversation::STATUS_PENDING && ! $conversation->staff_id) {
            $notice = $viewer->utype === 'USR'
                ? 'Yêu cầu tư vấn đã được gửi. Nhân viên sẽ phản hồi ngay khi nhận cuộc trò chuyện.'
                : 'Có khách hàng cần tư vấn.';
        } elseif ($conversation->status === SupportConversation::STATUS_ASSIGNED) {
            $notice = $viewer->utype === 'USR'
                ? 'Đang chờ nhân viên tư vấn xác nhận.'
                : 'Có khách hàng cần tư vấn.';
        }

        return [
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'customer_name' => $conversation->customer?->name,
                'staff_name' => $conversation->staff?->name,
                'notice' => $notice,
                'unread_count' => $viewer->utype === 'USR' ? 0 : ($this->isSupportUnreadFor($conversation, $viewer) ? 1 : 0),
            ],
            'messages' => $conversation->messages
                ->sortBy('created_at')
                ->values()
                ->map(function ($message) use ($viewer) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'sender_name' => $message->sender?->name ?? 'Hệ thống',
                        'message' => $message->message,
                        'mine' => $message->sender_id === $viewer->id && $message->sender_type !== SupportMessage::SENDER_SYSTEM,
                        'time' => optional($message->created_at)->format('H:i'),
                    ];
                }),
            'notice' => $notice,
        ];
    }
}
