<?php

	namespace Crouton\Processor;

	use \Crouton\Contracts\AjaxListener;

	class Ajax extends AjaxListener{


		/**
		 * All backend-ajax events for this plugin
		 * 
		 * @return string, echoed
		 */
		public function listen(){


			/**
			 * Below are just some examples
			 */
			add_action( 'wp_ajax_import_publications_post', function(){

				//check if there's a key:
				if( !isset( $_POST['key'] ) )
					$this->throwError( 'No key set' );

				$processor = new Processor();
				$key = $processor->setKey( $_POST['key'] );

				//check if key is valid:
				if( !$key )
					$this->throwError( 'Key not valid' );

				//execute the processor:
                $_post = $processor->execute();
                
                if( !is_null( $_post ) ){
				    echo json_encode(['error' => false, 'message' => 'IMPORTED: '.$_post->post_title ]);
                }else{
                    echo json_encode(['error' => true, 'message' => 'no links' ]);
                }
				die();

			});


			add_action( 'wp_ajax_remove_imported_Publications_posts', function(){

				$processor = new Processor();
				$processor->deleteOldPosts();

				echo 'Old posts deleted.';
				die();
			});
		}

		/**
		 * Throw an ajax error
		 *
		 * @param String $message
		 * @return void
		 */
		public function throwError( $message )
		{
			echo json_encode([ 'error' => true, 'message' => $message ]);
			die();
		}
	}