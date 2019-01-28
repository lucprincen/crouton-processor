<?php

    namespace Crouton\Processor;

    class Query{

        /**
         * Return the posts to process
         *
         * @return Array
         */
        public static function posts()
        {
            $posts = new \WP_Query([
                'post_type' => 'post', 
                'posts_per_page' => -1,
                'orderby' => 'ID',
                'order' => 'ASC'
            ]);
            return $posts->posts;
        }
    }