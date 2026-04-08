<?php

return [
    'feeds' => [
        'ko' => [
            'items' => 'App\Models\Post@getFeedItems',
            'url' => '/ko/rss',
            'title' => 'KKYOUNG.PHOTO (KR)',
            'description' => '개발자의 시선으로 담아낸 세상의 기하학.',
            'language' => 'ko',
            'format' => 'rss',
            'view' => 'feed::rss',
            'type' => 'application/rss+xml',
        ],
        'en' => [
            'items' => 'App\Models\Post@getFeedItems',
            'url' => '/en/rss',
            'title' => 'KKYOUNG.PHOTO (EN)',
            'description' => 'Archive of Light by PARK KYOUNG SEO.',
            'language' => 'en',
            'format' => 'rss',
            'view' => 'feed::rss',
            'type' => 'application/rss+xml',
        ],
        'ja' => [
            'items' => 'App\Models\Post@getFeedItems',
            'url' => '/ja/rss',
            'title' => 'KKYOUNG.PHOTO (JP)',
            'description' => '目に見えない構造를 설계하고, 보이는 찰나를 기록합니다.',
            'language' => 'ja',
            'format' => 'rss',
            'view' => 'feed::rss',
            'type' => 'application/rss+xml',
        ],
    ],
];