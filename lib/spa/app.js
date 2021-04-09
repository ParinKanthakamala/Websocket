'use strict';

(function () {
    function init() {
        var router = new Router([
            new Route('home', '/spa/home', true),            
            new Route('about', '/spa/about')
        ]);
    }
    init();
}());
