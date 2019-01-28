<?php

    namespace Crouton\Processor;

    class Processor{

        /**
         * Array with all posts
         *
         * @var Array
         */
        protected $posts;

        /**
         * Current post
         *
         * @var WP_Post
         */
        protected $currPost;

        /**
         * Current publication
         *
         * @var Array
         */
        protected $currRemote;

        /**
         * Current post key
         *
         * @var integer
         */
        protected $currPostKey = 0;
        

        /**
         * Array of post ids
         *
         * @var Array
         */
        protected $ids;



        /**
         * Constructor
         */
        public function __construct(  )
        {   
            $this->posts = Query::posts();
            $this->ids = get_option( 'post_generated_ids', [] );
        }

        /**
         * Execute
         *
         * @return void
         */ 
        public function execute( $key = null )
        {
            if( $key == null )
                $key = $this->currPostKey;

           
            $post = $this->posts[ $key ];

            /**
             * Logic here.
             * 
             * Examples below
             */ 

            return $post;
           
        }

      
        /**
         * Set the right post key to process
         *
         * @param Int $key
         * @return void
         */
        public function setKey( $key )
        {
            if( isset( $this->posts[ $key ] ) ){
                $this->currPostKey = $key;
                return true;
            }

            return false;
        }


        /**
         * Create single post
         *
         * @param Array $post
         * @return int PostId
         */
        public function createPost( $post )
        {
            unset( $post['ID'] );
            unset( $post['post_author'] );
            unset( $post['guid'] );
            unset( $post['post_name'] );
            
            //save this ID:
            $id = wp_insert_post( $post );
            $this->ids[] = $id;
            update_option( 'post_generated_ids', $this->ids );

            //set ID to object:
            $object = (object)$post;
            $object->ID = $id;
            return $object;
        }



        /**
         * Handle all taxonomies
         *
         * @return void
         */
        public function handleTaxonomies()
        {
            $terms = Query::postTerms( $this->currRemote['ID'], $this->siteId );
            if( empty( $terms ) || is_null( $terms ) )
                return null;

            $tax = [];
            foreach( $terms as $term ){
                $key = $term['taxonomy'];
                //create a new array per tax:
                if( !isset( $tax[ $key ] ) ){
                    $tax[ $key ] = [];
                }

                //add the term name:
                if( !in_array( $term['name '], $tax[$key] ) ){
                    $tax[$key][] = $term['name'];
                }
            }

            //loop through our array, and set the terms for each tax:
            if( !empty( $tax ) ){
                foreach( $tax as $taxonomy => $terms ){
                    wp_set_post_terms( $this->currPost->ID, $terms, $taxonomy );
                }
            }   

        }

        /**
         * Handle all attachments
         *
         * @return void
         */
        public function handleAttachments()
        {
            $content = $this->currPost->post_content;
            $images = [];

            //filter all images out of the content:
            if( preg_match_all( '/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>/i', $content, $matches, PREG_SET_ORDER ) )
            {
                foreach( $matches as $match ){
                    $images[] = $match[1];
                }
            }

            //loop through 'em
            if( !empty( $images ) ){    
                foreach( $images as $key => $image ){

                    //create a single attachment and add that id to the generated ids:
                    $id = $this->createAttachment( $image );
                    if( !is_wp_error( $id ) ){

                        $this->ids[] = $id;
                        update_option( 'post_generated_ids', $this->ids );

                        //if this is the first image, make this the featured image:
                        if( $key == 0 ){
                            add_post_meta( $this->currPost->ID, '_thumbnail_id', $id );
                        }

                    }

                }
            }
        }


        /**
         * Create an attachment from a url
         *
         * @param String $url
         * @return void
         */
        public function createAttachment( $url, $filename = null )
        {
            // set up a faux file array:
            $file = array();
            $url = str_replace( ' ', '%20', $url );
            if( is_null( $filename ) ){
                $filename = $this->currPost->post_title. '-Image.jpg';
            }

            $file['name'] = $filename;
            $file['tmp_name'] = download_url( $url, 3000 );

            // remove if there's an error grabbing the original url:
            if( is_wp_error( $file['tmp_name'] ) ){
                @unlink($file['tmp_name']);
            }

            // create the attachment:
            $attachmentId = media_handle_sideload($file, $this->currPost->ID );

            // create the thumbnails
            $attach_data = wp_generate_attachment_metadata( $attachmentId,  get_attached_file($attachmentId));
            wp_update_attachment_metadata( $attachmentId,  $attach_data );

            // return the original attachment ID
            return $attachmentId;	
        }


        
        /**
         * Delete old posts
         *
         * @return void
         */
        public function deleteOldPosts()
        {
            if( !empty( $this->ids ) ){
                foreach( $this->ids as $id ){
                    wp_delete_post( $id );
                }
            }
        }

    }