require(['jquery', 'slick'], function ($) {
    $(document).ready(function () {
        $("#story-gallery .gallery-slider").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: true,
            asNavFor: "#story-gallery .gallery-slider-nav"
        });

        $("#story-gallery .gallery-slider-nav").slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: "#story-gallery .gallery-slider",
            dots: false,
            centerMode: false,
            focusOnSelect: true,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2
                    }
                }
            ]
        });
    });
});
