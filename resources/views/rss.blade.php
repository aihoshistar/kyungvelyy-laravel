@php
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">' . PHP_EOL;
    $xml .= '    <channel>' . PHP_EOL;
    $xml .= '        <title>KKYOUNG.PHOTO</title>' . PHP_EOL;
    $xml .= '        <link>' . url('/') . '</link>' . PHP_EOL;
    $xml .= '        <description>Archive of Light by PARK KYOUNG SEO. 개발자의 시선으로 담아낸 세상의 기하학.</description>' . PHP_EOL;
    $xml .= '        <language>' . app()->getLocale() . '</language>' . PHP_EOL;
    $xml .= '        <atom:link href="' . url('/rss') . '" rel="self" type="application/rss+xml" />' . PHP_EOL;

    foreach($posts as $post) {
        $titleArr = is_string($post->title) ? json_decode($post->title, true) : $post->title;
        $descArr = is_string($post->description) ? json_decode($post->description, true) : $post->description;
        $title = $titleArr['ko'] ?? $titleArr['en'] ?? 'Untitled';
        $desc = $descArr['ko'] ?? $descArr['en'] ?? '';
        $slug = $post->slug ?? $post->id;
        $link = url("/ko/archive/{$slug}/1");
        $pubDate = $post->created_at ? $post->created_at->toRfc822String() : now()->toRfc822String();
        $photoUrl = $post->photos->first() ? $post->photos->first()->url : '';

        $xml .= '        <item>' . PHP_EOL;
        $xml .= '            <title><![CDATA[' . $title . ']]></title>' . PHP_EOL;
        $xml .= '            <link>' . $link . '</link>' . PHP_EOL;
        $xml .= '            <guid isPermaLink="true">' . $link . '</guid>' . PHP_EOL;
        $xml .= '            <description><![CDATA[';
        if($photoUrl) {
            $xml .= '<img src="' . $photoUrl . '" alt="' . $title . '" style="max-width:100%; border-radius:8px;"><br><br>';
        }
        $xml .= $desc . ']]></description>' . PHP_EOL;
        $xml .= '            <pubDate>' . $pubDate . '</pubDate>' . PHP_EOL;
        if($photoUrl) {
            $xml .= '            <media:content url="' . $photoUrl . '" type="image/jpeg" medium="image"/>' . PHP_EOL;
        }
        $xml .= '        </item>' . PHP_EOL;
    }

    $xml .= '    </channel>' . PHP_EOL;
    $xml .= '</rss>';

    echo trim($xml);
@endphp