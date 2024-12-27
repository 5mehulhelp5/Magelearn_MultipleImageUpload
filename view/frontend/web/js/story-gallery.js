define([
    'jquery',
    'fancybox'
], function ($, fancybox) {
    'use strict';

    return function (config) {
        $(document).ready(function () {
            try {
                if (typeof window.Fancybox !== 'undefined') {
                    window.Fancybox.bind('[data-fancybox]', config.options);
                } else if (fancybox && fancybox.Fancybox) {
                    fancybox.Fancybox.bind('[data-fancybox]', config.options);
                } else if (fancybox && typeof fancybox.bind === 'function') {
                    fancybox.bind('[data-fancybox]', config.options);
                } else {
                    console.error('Fancybox not properly loaded');
                }
            } catch (error) {
                console.error('Fancybox initialization error:', error);
            }
        });
    };
});