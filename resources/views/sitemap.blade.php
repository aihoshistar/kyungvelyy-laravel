<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>KKYOUNG.PHOTO</title>
        <link>{{ url('/') }}</link>
        <description>Archive of Light by PARK KYOUNG SEO. 개발자의 시선으로 담아낸 세상의 기하학.</description>
        <language>{{ app()->getLocale() }}</language>
        <atom:link href="{{ url('/rss') }}" rel="self" type="application/rss+xml" />
@foreach($posts as $post)
@php
    $titleArr = is_string($post->title) ? json_decode($post->title, true) : $post->title;
    $descArr = is_string($post->description) ? json_decode($post->description, true) : $post->description;
    $title = $titleArr['ko'] ?? $titleArr['en'] ?? 'Untitled';
    $desc = $descArr['ko'] ?? $descArr['en'] ?? '';
    $slug = $post->slug ?? $post->id;
    $link = url("/ko/archive/{$slug}/1");
    $pubDate = $post->created_at ? $post->created_at->toRfc822String() : now()->toRfc822String();
    $photoUrl = $post->photos->first() ? $post->photos->first()->url : '';
@endphp
        <item>
            <title><![CDATA[{{ $title }}]]></title>
            <link>{{ $link }}</link>
            <guid isPermaLink="true">{{ $link }}</guid>
            <description><![CDATA[
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $title }}" style="max-width:100%; border-radius:8px;"><br><br>
                @endif
                {{ $desc }}
            ]]></description>
            <pubDate>{{ $pubDate }}</pubDate>
            @if($photoUrl)
            <media:content url="{{ $photoUrl }}" type="image/jpeg" medium="image"/>
            @endif
        </item>
@endforeach
    </channel>
</rss>