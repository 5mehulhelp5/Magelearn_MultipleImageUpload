var config = {
	map: {
	        "*": {
	            'fancybox': 'Magelearn_Story/js/plugins/fancybox/fancybox.umd'
		}
	},
    paths: {
		'fancybox': 'Magelearn_Story/js/plugins/fancybox/fancybox.umd',
		'slick': 'Magelearn_Story/js/plugins/slick/slick.min'
    },
	shim: {
        'slick': {
            deps: ['jquery']
        }
    }
};