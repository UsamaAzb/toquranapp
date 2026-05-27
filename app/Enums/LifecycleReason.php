<?php

namespace App\Enums;

use InvalidArgumentException;

enum LifecycleReason: string
{
    case SetupComplete = 'setup_complete';
    case PaymentScheduleConfirmed = 'payment_schedule_confirmed';
    case ScheduleConfirmed = 'schedule_confirmed';
    case AdminApproved = 'admin_approved';
    case PaymentIssue = 'payment_issue';
    case SupportHold = 'support_hold';
    case BehaviorOrPolicyIssue = 'behavior_or_policy_issue';
    case SecurityConcern = 'security_concern';
    case AdminReview = 'admin_review';
    case PaymentResolved = 'payment_resolved';
    case SupportIssueResolved = 'support_issue_resolved';
    case PolicyIssueResolved = 'policy_issue_resolved';
    case SecurityIssueResolved = 'security_issue_resolved';
    case FamilyLeft = 'family_left';
    case ServiceCompleted = 'service_completed';
    case DuplicateOrMergedFamily = 'duplicate_or_merged_family';
    case UnreachableOrNoResponse = 'unreachable_or_no_response';
    case AdminDecision = 'admin_decision';
    case ReturningFamily = 'returning_family';
    case ArchivedByMistake = 'archived_by_mistake';
    case DuplicateMergeReversed = 'duplicate_merge_reversed';

    /** @return list<string> */
    public static function forAction(string $action): array
    {
        return match ($action) {
            'activate' => [
                self::SetupComplete->value,
                self::PaymentScheduleConfirmed->value,
                self::ScheduleConfirmed->value,
                self::AdminApproved->value,
            ],
            'suspend' => [
                self::PaymentIssue->value,
                self::SupportHold->value,
                self::BehaviorOrPolicyIssue->value,
                self::SecurityConcern->value,
                self::AdminReview->value,
            ],
            'reactivate' => [
                self::PaymentResolved->value,
                self::SupportIssueResolved->value,
                self::PolicyIssueResolved->value,
                self::SecurityIssueResolved->value,
                self::AdminApproved->value,
            ],
            'archive' => [
                self::FamilyLeft->value,
                self::ServiceCompleted->value,
                self::DuplicateOrMergedFamily->value,
                self::UnreachableOrNoResponse->value,
                self::AdminDecision->value,
            ],
            'restore' => [
                self::ReturningFamily->value,
                self::ArchivedByMistake->value,
                self::DuplicateMergeReversed->value,
                self::AdminApproved->value,
            ],
            default => throw new InvalidArgumentException("Unknown lifecycle action: {$action}"),
        };
    }
}
