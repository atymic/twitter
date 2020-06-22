<?php

declare(strict_types=1);

namespace Thujohn\Twitter;

abstract class Utils
{
    public static function linkify($tweet)
    {
        if (is_object($tweet)) {
            $type = 'object';
            $tweet = self::jsonDecode(json_encode($tweet), true);
        } elseif (is_array($tweet)) {
            $type = 'array';
        } else {
            $type = 'text';
            $text = ' '.$tweet;
        }

        $patterns = [];
        $patterns['url'] = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
        $patterns['mailto'] = '([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))';
        $patterns['user'] = '(^| +)(\.)?@([a-z0-9_]*)?';
        $patterns['hashtag'] = '(?:(?<=\s)|^)#(\w*[\p{L}\-\d\x{200c}_\p{Cyrillic}\d]+\w*)';
        $patterns['long_url'] = '>(([[:alnum:]]+:\/\/)|www\.)?([^[:space:]]{12,22})([^[:space:]]*)([^[:space:]]{12,22})([[:alnum:]#?\/&=])<';

        if ($type === 'text') {
            // URL
            $pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
            $text = preg_replace_callback('#'.$patterns['url'].'#i', function ($matches) {
                $input = $matches[0];
                $url = preg_match('!^https?://!i', $input) ? $input : "http://$input";

                return '<a href="'.$url.'" target="_blank" rel="nofollow">'."$input</a>";
            }, $text);
        } else {
            $text = $tweet['text'] ?? $tweet['full_text'];
            $entities = $tweet['entities'];

            $search = [];
            $replace = [];

            if (array_key_exists('media', $entities)) {
                foreach ($entities['media'] as $media) {
                    $search[] = $media['url'];
                    $replace[] = '<a href="'.$media['media_url_https'].'" target="_blank">'.$media['display_url'].'</a>';
                }
            }

            if (array_key_exists('urls', $entities)) {
                foreach ($entities['urls'] as $url) {
                    $search[] = $url['url'];
                    $replace[] = '<a href="'.$url['expanded_url'].'" target="_blank" rel="nofollow">'.$url['display_url'].'</a>';
                }
            }

            $text = str_replace($search, $replace, $text);
        }

        // Mailto
        $text = preg_replace('/'.$patterns['mailto'].'/i', '<a href="mailto:\\1">\\1</a>', $text);

        // User
        $text = preg_replace('/'.$patterns['user'].'/i', '\\1\\2<a href=\"https://twitter.com/\\3\" target=\"_blank\">@\\3</a>', $text);

        // Hashtag
        $text = preg_replace('/'.$patterns['hashtag'].'/ui', '<a href="https://twitter.com/search?q=%23\\1" target="_blank">#\\1</a>', $text);

        // Long URL
        $text = preg_replace('/'.$patterns['long_url'].'/', '>\\3...\\5\\6<', $text);

        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private static function jsonDecode($json, $assoc = false)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
        } else {
            return json_decode($json, $assoc);
        }
    }
}
