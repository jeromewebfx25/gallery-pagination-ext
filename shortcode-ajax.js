/* ---------------------------------------------------------------------
	Global Js
	Target Browsers: All

	HEADS UP! This script is for general functionality found on ALL pages and not tied to specific components, blocks, or
	plugins. 

	If you need to add JS for a specific block or component, create a script file in js/components or js/blocks and
	add your JS there. (Don't forget to enqueue it!)
------------------------------------------------------------------------ */

var FX = ( function( FX, $ ) {

	$( () => {
		FX.GalleryPagination.init()
	})

	})

	FX.GalleryPagination = {
		init() {
			function galleryAjax(filter, res_id) {
		        var filter = $(filter);
		        if( filter ) {
		            $.ajax({
		                url:filter.attr('action'),
		                data:filter.serialize(), 
		                type:filter.attr('method'), 
		                cache: false,
		                beforeSend: function() {
		                    $('.gallery-loader').show();
		                },
		                complete: function(){
		                    $('.gallery-loader').hide();
		                },
		                success:function(data){
		           			$(res_id).append(data);
		           			var grid = "";
					    	grid = document.querySelector('.media-gallery.media-gallery--stacked-layout.grid');
					    	if (grid !== null) {    
					    	    var msnry = new Masonry( grid, {
					    	        itemSelector: '.media-gallery__item.grid-item',
					    	        columnWidth: '.grid-sizer',
					    	        percentPosition: true
					    	    });
					    	    imagesLoaded( grid ).on( 'progress', function() {
					    	        // layout Masonry after each image loads
					    	        msnry.layout();
					    	    });
					    	}

					    	var media_count = $('.media-gallery__item').length;
					   		var gallery_total = parseInt( $('.gallery-total').val() );
					    	if( media_count >= gallery_total ) {
					    		$('#fx-load-more-pagination').hide();
					    	}
		                },
		                async: "false",
		            });
		        }
		    }

		    $('.fx-gallery-form').submit(function(){
		   		var gallery_counter = parseInt( $('.gallery-counter').val() );
           		var gallery_per_load = parseInt( $('.gallery-per-load').val() );

           		$('.gallery-counter').val( gallery_counter + gallery_per_load );
				var res_id = '.fx-gallery-result .media-gallery';
				galleryAjax(this, res_id );
				
				return false;
		    });
		},
	};

	

	return FX;

} ( FX || {}, jQuery ) );
