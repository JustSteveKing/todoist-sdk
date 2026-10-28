<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk;

use JustSteveKing\SdkToolbox\Auth\Authenticator;
use JustSteveKing\SdkToolbox\Auth\BearerToken;
use JustSteveKing\SdkToolbox\Connector;
use JustSteveKing\SdkToolbox\Hydration\Hydrator;
use JustSteveKing\SdkToolbox\Hydration\ValinorHydrator;
use JustSteveKing\SdkToolbox\Retries\RetryPolicy;
use JustSteveKing\SdkToolbox\Retries\RetryStrategy;
use JustSteveKing\SdkToolbox\Time\Sleeper;
use JustSteveKing\SdkToolbox\Time\SystemClock;
use JustSteveKing\SdkToolbox\Time\SystemSleeper;
use JustSteveKing\TodoistSdk\Data\User;
use JustSteveKing\TodoistSdk\Errors\TodoistErrors;
use JustSteveKing\TodoistSdk\Requests\User\GetUser;
use JustSteveKing\TodoistSdk\Resources\Comments;
use JustSteveKing\TodoistSdk\Resources\Labels;
use JustSteveKing\TodoistSdk\Resources\Projects;
use JustSteveKing\TodoistSdk\Resources\Sections;
use JustSteveKing\TodoistSdk\Resources\Tasks;
use JustSteveKing\TodoistSdk\Resources\Uploads;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SensitiveParameter;

/**
 * Todoist's API v1, as a PHP SDK built on sdk-toolbox.
 *
 * Not created by, affiliated with, or supported by Todoist.
 *
 *     $todoist = new Todoist(getenv('TODOIST_TOKEN'));
 *
 *     foreach ($todoist->tasks()->all() as $task) {
 *         echo $task->content, PHP_EOL;
 *     }
 *
 * The token is a personal API token from Todoist's integration settings,
 * or an OAuth access token. For a user who authorised your app, pass the
 * authenticator TodoistOAuth::refreshing() gives you instead, and the
 * token stays fresh.
 */
final class Todoist extends Connector
{
    public const string VERSION = '0.0.1-dev';

    public const string BASE_URI = 'https://api.todoist.com/api/v1';

    public function __construct(
        #[SensitiveParameter] string|Authenticator $token,
        ?ClientInterface $http = null,
        RetryStrategy $retry = new RetryPolicy,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null,
        ?EventDispatcherInterface $events = null,
        ClockInterface $clock = new SystemClock,
        Sleeper $sleeper = new SystemSleeper,
        ?Hydrator $hydrator = null,
        string $baseUri = self::BASE_URI,
    ) {
        parent::__construct(
            baseUri: $baseUri,
            auth: is_string($token) ? new BearerToken($token) : $token,
            http: $http,
            userAgent: 'todoist-sdk-php/'.self::VERSION,
            exceptions: TodoistErrors::mapper(),
            retry: $retry,
            clock: $clock,
            sleeper: $sleeper,
            hydrator: $hydrator ?? new ValinorHydrator,
            cache: $cache,
            logger: $logger,
            events: $events,
        );
    }

    /**
     * The user the token belongs to.
     */
    public function user(): User
    {
        return $this->send(new GetUser);
    }

    public function tasks(): Tasks
    {
        return new Tasks($this);
    }

    public function projects(): Projects
    {
        return new Projects($this);
    }

    public function sections(): Sections
    {
        return new Sections($this);
    }

    public function labels(): Labels
    {
        return new Labels($this);
    }

    public function comments(): Comments
    {
        return new Comments($this);
    }

    public function uploads(): Uploads
    {
        return new Uploads($this);
    }
}
