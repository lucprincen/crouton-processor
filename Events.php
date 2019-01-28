<?php

	namespace Crouton\Processor;

    use \Crouton\Contracts\EventListener;

	class Events extends EventListener{

		/**
		 * Listen for admin events
		 * 
		 * @return void
		 */
		public function listen(){
            

			add_action( 'admin_init', function(){
				if( isset( $_GET['postImport'] ) ){
                
					( new ImportUi() )->render();
					die();
                    
				}
			});
		}
	}
