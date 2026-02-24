<?php

namespace Src\Visitor\TriggerEngine;

/**
 * Trigger Engine — evaluates conditions for auto-starting conversations.
 *
 * Rules are stored in DB/config. Evaluated statelessly on each request.
 * Examples: time-on-page, page-url-match, returning-visitor.
 */
class TriggerEngine
{
    /**
     * Evaluate triggers against visitor context.
     *
     * @param array $context ['url', 'time_on_page', 'visit_count', ...]
     * @return bool true if a conversation should be auto-initiated
     */
    public function shouldTrigger(array $context): bool
    {
        // Placeholder — implement rule evaluation from DB/config
        return false;
    }

    /**
     * Get the applicable trigger rule details.
     */
    public function matchingRule(array $context): ?array
    {
        // Placeholder — return matched rule or null
        return null;
    }
}
