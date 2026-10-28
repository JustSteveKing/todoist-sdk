<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * The user the token belongs to.
 *
 * Five of the API's fields are left out on purpose. `token` is the
 * user's own API token, and `websocket_url` carries it too, so neither
 * should end up in a dump or a log; `urgent_reminder_device` carries a
 * device's push token. `features` and `joinable_workspace` are untyped
 * objects in Todoist's spec. The contract tests hold that list.
 *
 * Fields the spec doesn't mark as required default to null.
 */
final readonly class User
{
    /**
     * @param  list<int>  $daysOff
     * @param  list<string>|null  $onboardingUseCases
     * @param  list<string>|null  $gettingStartedGuideProjects
     */
    public function __construct(
        public string $id,
        public string $email,
        public string $fullName,
        public bool $hasPassword,
        public string $verificationStatus,
        public bool $mfaEnabled,
        public bool $isPremium,
        public bool $hasStartedATrial,
        public int $dateFormat,
        public int $sortOrder,
        public string $themeId,
        public string $themeAppearance,
        public int $startDay,
        public int $weekendStartDay,
        public int $nextWeek,
        public int $autoReminder,
        public string $startPage,
        public string $lang,
        public TimeZone $tzInfo,
        public float $karma,
        public int $dailyGoal,
        public int $weeklyGoal,
        public array $daysOff,
        public bool $isCelebrationsEnabled,
        public int $completedCount,
        public int $completedToday,
        public int $shareLimit,
        public string $featureIdentifier,
        public bool $onboardingCompleted,
        public bool $onboardingInitiated,
        public bool $onboardingStarted,
        public bool $activatedUser,
        public bool $hasMagicNumber,
        public ?string $premiumStatus = null,
        public ?string $premiumUntil = null,
        public ?string $freeTrialExpires = null,
        public ?string $joinedAt = null,
        public ?bool $isDeleted = null,
        public ?string $deletedAt = null,
        public ?int $businessAccountId = null,
        public ?int $timeFormat = null,
        public ?UserStatus $status = null,
        public ?string $inboxProjectId = null,
        public ?string $karmaTrend = null,
        public ?string $onboardingLevel = null,
        public ?string $onboardingPersona = null,
        public ?string $onboardingRole = null,
        public ?string $onboardingTeamMode = null,
        public ?array $onboardingUseCases = null,
        public ?array $gettingStartedGuideProjects = null,
        public ?bool $onboardedDatedTasksCreated = null,
        public ?bool $onboardedTasksCompleted = null,
        public ?bool $onboardedMobileAccessed = null,
        public ?bool $onboardedDesktopAccessed = null,
        public ?bool $isOnboarded = null,
        public ?string $imageId = null,
        public ?string $avatarBig = null,
        public ?string $avatarMedium = null,
        public ?string $avatarS640 = null,
        public ?string $avatarSmall = null,
    ) {}
}
