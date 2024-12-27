define([
    'jquery',
    'Magelearn_Story/js/story-gallery',
    'Magelearn_Story/js/slick-slider'
], function($, storyGallery, slickSlider) {
    'use strict';
    
    return function (config) {
        $(document).ready(function() {
            // Initialize Fancybox
            storyGallery({
                options: {
                    groupAll: true,
                    animated: true,
                    showClass: "f-fadeIn",
                    hideClass: "f-fadeOut",
                    Toolbar: {
                        display: {
                            left: ["prev", "next"],
                            middle: ["zoomIn", "zoomOut", "toggle1to1", "slideshowStart", "slideshowStop"],
                            right: ["close"]
                        }
                    },
                    Carousel: {
                        infinite: true,
                        friction: 0.8
                    }
                }
            });

            // Initialize Slick Slider
            slickSlider();
        });
    };
});