(function () {
    function eraseCookie(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
    }

    eraseCookie('smartframe-registration-data');
})();