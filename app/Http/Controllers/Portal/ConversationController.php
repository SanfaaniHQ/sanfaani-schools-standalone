<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalConversation;
use App\Services\CurrentSchoolService;
use App\Services\Portals\PortalCommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private PortalCommunicationService $communication
    ) {}

    public function index(Request $request): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        return view('portal.conversations.index', [
            'school' => $school,
            'conversations' => $this->communication->conversationsFor($request->user(), $school),
            'recipients' => $this->communication->eligibleRecipients($request->user(), $school),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'conversation_type' => ['required', Rule::in(['general', 'academic', 'finance', 'result', 'attendance'])],
            'body' => ['required', 'string', 'max:3000'],
            'recipient_user_ids' => ['nullable', 'array'],
            'recipient_user_ids.*' => ['integer'],
        ]);

        $conversation = $this->communication->createConversation($request->user(), $school, $data);

        return redirect()
            ->route('portal.conversations.show', ['conversationId' => $conversation->id])
            ->with('success', 'Conversation started successfully.');
    }

    public function show(Request $request, int|string $conversationId): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $conversation = PortalConversation::query()
            ->whereKey($conversationId)
            ->with(['participants.user', 'messages.sender'])
            ->firstOrFail();

        abort_if(! $this->communication->canAccessConversation($request->user(), $conversation, $school), 403);

        $conversation->participants()
            ->where('user_id', $request->user()->id)
            ->update(['last_read_at' => now()]);

        return view('portal.conversations.show', [
            'school' => $school,
            'conversation' => $conversation->fresh(['participants.user', 'messages.sender']),
        ]);
    }

    public function message(Request $request, int|string $conversationId): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $conversation = PortalConversation::query()
            ->whereKey($conversationId)
            ->firstOrFail();

        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $this->communication->createMessage($conversation, $request->user(), $school, $data['body']);

        return redirect()
            ->route('portal.conversations.show', ['conversationId' => $conversation->id])
            ->with('success', 'Message sent.');
    }
}
