<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Jobs\Notifications;

use App\Models\BeatmapDiscussionPost;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotificationOption;

abstract class BeatmapsetDiscussionPostNotification extends BroadcastNotificationBase
{
    const NOTIFICATION_OPTION_NAME = UserNotificationOption::BEATMAPSET_MODDING;

    protected $beatmapsetDiscussionPost;

    public static function getBaseKey(Notification $notification): string
    {
        $category = $notification->category;

        return $category === 'beatmapset_discussion'
            ? "{$notification->notifiable_type}.{$category}.beatmapset_discussion_post_new"
            : "{$notification->notifiable_type}.{$category}.{$notification->name}";
    }

    public static function getMailLink(Notification $notification): string
    {
        return route('beatmapsets.discussion', ['beatmapset' => $notification->notifiable_id]).'#/'.$notification->details['discussion_id'];
    }

    public function __construct(BeatmapDiscussionPost $beatmapsetDiscussionPost, ?User $source = null)
    {
        parent::__construct($source);

        $this->beatmapsetDiscussionPost = $beatmapsetDiscussionPost;
    }

    public function getDetails(): array
    {
        $beatmapset = $this->beatmapsetDiscussionPost->beatmapset;
        $discussion = $this->beatmapsetDiscussionPost->beatmapDiscussion;

        return [
            'content' => truncate($this->beatmapsetDiscussionPost->message, static::CONTENT_TRUNCATE),
            'title' => $beatmapset->title,
            'title_unicode' => $beatmapset->title_unicode,
            'post_id' => $this->beatmapsetDiscussionPost->getKey(),
            'discussion_id' => $discussion->getKey(),
            'beatmap_id' => $discussion->beatmap_id,
            'cover_url' => $beatmapset->coverURL('card'),
        ];
    }

    public function getListeningUserIds(): array
    {
        return $this->beatmapsetDiscussionPost->beatmapset->watches()->pluck('user_id')->all();
    }

    public function getNotifiable()
    {
        return $this->beatmapsetDiscussionPost->beatmapset;
    }
}
