<?php

namespace Src\Api\Controllers;

use Illuminate\Http\Request;
use Src\Channel\WebsiteWidgetAdapter\WebsiteWidgetAdapter;
use Src\Core\MessageTimeline\MessageTimeline;

class VisitorController extends ApiController
{
    public function __construct(private readonly WebsiteWidgetAdapter $widget) {}

    /**
     * POST /api/v1/visitor/session
     */
    public function initSession(Request $request)
    {
        $request->validate(['session_token' => 'required|string|uuid']);
        return $this->success($this->widget->initSession($request->session_token));
    }

    /**
     * POST /api/v1/visitor/conversations
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'visitor_id' => 'required|integer|exists:visitors,id',
            'subject'    => 'nullable|string|max:255',
        ]);

        return $this->created(
            $this->widget->startConversation($request->visitor_id, $request->subject)
        );
    }

    /**
     * POST /api/v1/visitor/conversations/{id}/messages
     */
    public function sendMessage(Request $request, int $conversationId)
    {
        $request->validate([
            'visitor_id' => 'required|integer|exists:visitors,id',
            'body'       => 'required|string',
        ]);

        return $this->created(
            $this->widget->sendMessage($conversationId, $request->visitor_id, $request->body)
        );
    }

    /**
     * GET /api/v1/visitor/conversations/{id}/messages
     */
    public function messages(int $conversationId, MessageTimeline $timeline)
    {
        return $this->success($timeline->recent($conversationId));
    }
}
