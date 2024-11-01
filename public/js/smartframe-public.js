jQuery(document).ready(function () {
    jQuery('smart-frame').each(function (key, val) {
        this.addEventListener('load', function () {
            window.dispatchEvent(new Event('resize'));
        });
    });
    setTimeout(function () {
        jQuery('smart-frame').trigger('resize');
    }, 1500);
});
