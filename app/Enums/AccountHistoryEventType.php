<?php

namespace App\Enums;

enum AccountHistoryEventType: string
{
    case ChildTransferredIntoFamily = 'child_transferred_into_family';
    case FamilyActivated = 'family_activated';
    case FamilySuspended = 'family_suspended';
    case FamilyReactivated = 'family_reactivated';
    case FamilyArchived = 'family_archived';
    case FamilyRestored = 'family_restored';
    case ChildActivated = 'child_activated';
    case ChildSuspended = 'child_suspended';
    case ChildReactivated = 'child_reactivated';
    case ChildArchived = 'child_archived';
    case ChildRestored = 'child_restored';
    case ParentActivationEmailQueued = 'parent_activation_email_queued';
    case ParentActivationEmailSent = 'parent_activation_email_sent';
    case ParentActivationEmailFailed = 'parent_activation_email_failed';
    case ParentActivationEmailSkipped = 'parent_activation_email_skipped';
    case ChildActivationEmailQueued = 'child_activation_email_queued';
    case ChildActivationEmailSent = 'child_activation_email_sent';
    case ChildActivationEmailFailed = 'child_activation_email_failed';
    case ChildActivationEmailSkipped = 'child_activation_email_skipped';
    case ParentPasswordRevealed = 'parent_password_revealed';
    case ChildPasswordRevealed = 'child_password_revealed';
    case CredentialRevealDenied = 'credential_reveal_denied';
    case ParentPasswordChangedByUser = 'parent_password_changed_by_user';
    case ParentPasswordResetLinkFailed = 'parent_password_reset_link_failed';
    case ParentPasswordResetLinkSent = 'parent_password_reset_link_sent';
    case ChildPasswordResetLinkFailed = 'child_password_reset_link_failed';
    case ChildPasswordResetLinkSent = 'child_password_reset_link_sent';
    case ParentPasswordResetByAdmin = 'parent_password_reset_by_admin';
    case ChildPasswordResetByAdmin = 'child_password_reset_by_admin';
    case ActivationEmailResent = 'activation_email_resent';
    case LegacyAccountClassified = 'legacy_account_classified';
    case BackfillCredentialMigrated = 'backfill_credential_migrated';
    case BackfillCredentialSkipped = 'backfill_credential_skipped';
}
