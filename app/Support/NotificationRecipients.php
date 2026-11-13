<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * تصفية مستلمي الإشعارات حسب NOTIFY_ONLY_TEST_ACCOUNTS (انظر config/notifications.php).
 */
class NotificationRecipients
{
    public static function onlyTestAccounts(): bool
    {
        return (bool) config('notifications.only_test_accounts') && ! app()->isProduction();
    }

    /**
     * @template T of object
     *
     * @param  iterable<T>  $recipients  نماذج مستخدمين (يجب أن تحمل email)
     * @return Collection<int, T>
     */
    public static function filter(iterable $recipients, string $context = ''): Collection
    {
        $recipients = collect($recipients)->filter()->values();

        if (! self::onlyTestAccounts()) {
            return $recipients;
        }

        $suffix = '@'.ltrim((string) config('notifications.test_account_domain', 'solarhub.test'), '@');
        $kept = $recipients->filter(fn ($user) => str_ends_with(mb_strtolower((string) ($user->email ?? '')), $suffix))->values();

        if ($kept->count() !== $recipients->count()) {
            Log::debug('NOTIFY_ONLY_TEST_ACCOUNTS: skipped '.($recipients->count() - $kept->count())." non-test recipient(s) {$context}");
        }

        return $kept;
    }
}
