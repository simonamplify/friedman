(function($){
    // Nested mobile menu
    function setup_collapsible_submenus() {
        var $menu = $('.et_mobile_menu'),
            top_level_link = '.et_mobile_menu .menu-item-has-children > a';
        $menu.find('a').each(function() {
            $(this).off('click'); 
            if ( $(this).is(top_level_link) ) {
                $(this).attr('href', '#');
            }
            if ( ! $(this).siblings('.sub-menu').length ) {
                $(this).on('click', function(event) {
                    $(this).parents('.mobile_nav').trigger('click');
                });
            } else {
                $(this).on('click', function(event) {
                    event.preventDefault();
                    $(this).parent().toggleClass('visible');
                });
            }
        });
    }
    $(window).load(function() {
        setTimeout(function() {
            setup_collapsible_submenus();
        }, 700);
    });
	$(document).ready( function(){
		// Menu Search
        $.fn.extend({
            toggleText: function(a, b){
                return this.text(this.text() == b ? a : b);
            }
        });
        $('.menuSearchBtn').click(function(){
            $('.menuSearch').slideToggle();
            $('.menuSearch').toggleClass('focus');
            $('.menuSearch.focus .dgwt-wcas-search-input').focus();
            var ms = document.querySelector('.menuSearchBtn .material-icons');
            if (ms.innerHTML === "search") {
                ms.innerHTML = "close";
            } else {
                ms.innerHTML = "search";
            }
        });
        /* Artist list filter */
		var options = {
			valueNames: [ 'artistName' ]
		};
		var artistList = new List('artists', options);
		/* Artist Search Clear */
		$('#artists .search').on('keyup', function() {
			if (this.value.length > 0) {
				$('#artists .clear').show();
			} else {
				$('#artists .clear').hide();
			}
	   	});
		$('#artists .clear').on('click', function(event) {
			artistList.search();
			artistList.filter();
			artistList.update();
			$('#artists .search').val('');
			$('#artists .search').focus();
			$('#artists .clear').hide();
		});
		$('#artists .artistOverlay').hide();
		$('#artists .bioBtn').on('click', function(event) {
			var artistHash = $(this).attr('href').substring(1);
			$('.artistModal.'+artistHash).fadeIn(500);
			$('#artists .artistOverlay').fadeIn(500);
		});
		$('#artists .closeArtist').on('click', function(event) {
			var artistHash = $(this).attr('href').substring(1);
			$('.artistModal.'+artistHash).fadeOut(500);
			$('#artists .artistOverlay').fadeOut(500);
		});
		$('#artists .artist .artistDetails .artistContent a').attr('target', '_blank');
        /* Hover Cards */
        var hoverCard = null;
        var hoverCard = document.querySelector('.cards .card');
        if(hoverCard !== null && hoverCard !== '') {
            document.hoverCard.classList.add('expanded');
            document.hoverCard.onmouseover=function(){
                this.classList.remove('expanded');
            };
        }
	});
})(jQuery)