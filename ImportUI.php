<?php

    namespace Crouton\Processor;

    /**
     * Import UI
     * 
     * @package Crouton
     */
    class ImportUi{


        /**
         * Publications
         *
         * @var Array
         */
        protected $posts;


        /**
         * Constructor
         */
        public function __construct()
        {
            $this->posts = Query::posts();
        }


        /**
         * Render the importer
         *
         * @return String (html, echoed)
         */
        public function render()
        {
            ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Import Posts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?php echo includes_url('js/jquery/jquery.js');?>"></script>
</head>
<body style="background:#eee;">
    <div style="max-width:700px;padding:30px;margin:50px auto;background:white;box-shadow: 0 0 10px rgba(0,0,0,.2);">
        <h2>Import Posts</h2>
        <div style="padding-bottom: 30px;">
            <button id="start" style="cursor:pointer">Start import</button>
            <a id="remove" style="margin-left:10px;color:crimson;text-decoration:underline;cursor:pointer">Remove previously imported posts</a>
        </div>
        <div id="starting" style="display:none;padding:15px 0;">Importing...</div>
        <div id="bar" style="border-radius:15px;overflow:hidden;height:30px;background:#eee">
            <div id="progress" style="background:dodgerblue;height:30px;width:0"></div>
        </div>
        <ul id="list" style="list-style:none;padding-left: 0;">
        </ul>
    </div>
    <script>

        var maxPosts = <?php echo count( $this->posts );?>;
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
        var currPost = 0;

        jQuery( document ).ready( function( $ ){

            $('#start').on( 'click tap', function(){
                $('#starting').show();
                $('#list').empty();
                doImport();
            });
            
            $('#remove').on( 'click tap', function(){
                doRemove();
            });


            function doImport(){
                if( currPost < maxPosts ){

                    console.log( 'Next: '+currPost );

                    //set bar:
                    var percent = currPost / ( maxPosts - 1 ) * 100;
                    $('#progress').css({ 'width': percent+'%' });


                    var data = {
                        'action': 'import_publications_post',
                        'key': currPost
                    }

                    $.post( ajaxurl, data, function( response ){

                        console.log( response );

                        response = JSON.parse( response );
                        var _style = 'color:green;';
                        if( response.error ){
                            _style = 'color:crimson;';
                        }
                        
                        var _html = '<li style="'+_style+'">'+response.message+'</li>';
                        $('#list').append( _html );
                        
                        //rinse and repeat:
                        currPost++;
                        doImport();
                    });

                }
            }

            function doRemove(){
                var data = {
                    'action': 'remove_imported_Publications_posts'
                }

                $.post( ajaxurl, data, function( response ){
                    $('#list').append('<li style="color:green">'+response+'</li>');
                });
            }

        });

    </script>
</body>
</html>


            <?php
        }

    }