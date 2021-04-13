<?php

declare(strict_types=1);

namespace Atymic\Twitter\ApiV1\Traits;

use Carbon\Carbon;

trait FormattingHelpers
{
    public function linkify($tweet): string
    {
        //todo fix this logic
        if (is_object($tweet)) {
            $type = 'object';
            $tweet = json_decode(json_encode($tweet), true);
        } elseif (is_array($tweet)) {
            $type = 'array';
        } else {
            $type = 'text';
            $text = ' ' . $tweet;
        }

        $patterns = [];
        $patterns['url'] = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
        $patterns['mailto'] = '([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))';
        $patterns['user'] = ' +@([a-z0-9_]*)?';
        $patterns['hashtag'] = '(?:(?<=\s)|^)#(\w*[\p{L}\-\d\p{Cyrillic}\d]+\w*)';
        $patterns['long_url'] = '>(([[:alnum:]]+:\/\/)|www\.)?([^[:space:]]{12,22})([^[:space:]]*)([^[:space:]]{12,22})([[:alnum:]#?\/&=])<';

        if ($type === 'text') {
            // URL
            $pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
            $text = preg_replace_callback('#' . $patterns['url'] . '#i', function ($matches) {
                $input = $matches[0];
                $url = preg_match('!^https?://!i', $input) ? $input : "http://${input}";

                return '<a href="' . $url . '" target="_blank" rel="nofollow">' . "${input}</a>";
            }, $text);
        } else {
            $text = $tweet['text'];
            $entities = $tweet['entities'];

            $search = [];
            $replace = [];

            if (array_key_exists('media', $entities)) {
                foreach ($entities['media'] as $media) {
                    $search[] = $media['url'];
                    $replace[] = '<a href="' . $media['media_url_https'] . '" target="_blank">' . $media['display_url'] . '</a>';
                }
            }

            if (array_key_exists('urls', $entities)) {
                foreach ($entities['urls'] as $url) {
                    $search[] = $url['url'];
                    $replace[] = '<a href="' . $url['expanded_url'] . '" target="_blank" rel="nofollow">' . $url['display_url'] . '</a>';
                }
            }

            $text = str_replace($search, $replace, $text);
        }

        // Mailto
        $text = preg_replace('/' . $patterns['mailto'] . '/i', '<a href="mailto:\\1">\\1</a>', $text);

        // User
        $text = preg_replace('/' . $patterns['user'] . '/i', ' <a href="https://twitter.com/\\1" target="_blank">@\\1</a>', $text);

        // Hashtag
        $text = preg_replace('/' . $patterns['hashtag'] . '/ui', '<a href="https://twitter.com/search?q=%23\\1" target="_blank">#\\1</a>', $text);

        // Long URL
        $text = preg_replace('/' . $patterns['long_url'] . '/', '>\\3...\\5\\6<', $text);

        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    // todo figure out how this is used and refactor
    public function ago($timestamp): string
    {
        if (is_numeric($timestamp) && (int) $timestamp === $timestamp) {
            $carbon = Carbon::createFromTimeStamp($timestamp);
        } else {
            $dt = new \DateTime($timestamp);
            $carbon = Carbon::instance($dt);
        }

        return $carbon->diffForHumans();
    }

    // todo redo these helpers

    /**
     * @param object|array|string $user
     *
     * @return string
     */
    public function linkUser($user): string
    {
        $screenName = is_string($user) ? $user : $this->objectToArray($user)['screen_name'];

        return 'https://twitter.com/' . $screenName;
    }

    /**
     * @param object|array $tweet
     *
     * @return string
     */
    public function linkTweet($tweet): string
    {
        $tweet = $this->objectToArray($tweet);

        return $this->linkUser($tweet['user']) . '/status/' . $tweet['id_str'];
    }

    /**
     * @param object|array $tweet
     *
     * @return string
     */
    public function linkRetweet($tweet): string
    {
        $tweet = $this->objectToArray($tweet);

        return 'https://twitter.com/intent/retweet?tweet_id=' . $tweet['id_str'];
    }

    /**
     * @param object|array $tweet
     *
     * @return string
     */
    public function linkAddTweetToFavorites($tweet): string
    {
        $tweet = $this->objectToArray($tweet);

        return 'https://twitter.com/intent/favorite?tweet_id=' . $tweet['id_str'];
    }

    /**
     * @param object|array $tweet
     *
     * @return string
     */
    public function linkReply($tweet): string
    {
        $tweet = $this->objectToArray($tweet);

        return 'https://twitter.com/intent/tweet?in_reply_to=' . $tweet['id_str'];
    }

    /**
     * @param $data
     *
     * @return array|mixed
     */
    protected function objectToArray($data)
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_object($data)) {
            return json_decode(json_encode($data), true);
        }

        // Fallback for non objects
        return $data;
    }
}
