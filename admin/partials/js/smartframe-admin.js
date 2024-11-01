(function ($) {
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
    }


    var smartframeHelpers = {
        displayThemeInfo: function (height, width) {

            if (SmartFrameStorageExceeded) {
                return;
            }
            $('.smartframe-use-smartframe-caption').parent().find('span').remove();
            $('.smartframe-theme-select').parent().find('span').remove();

            if (height < 120 || width < 320) {


                $('.smartframe-theme-select').hide();
                $('.smartframe-theme-select').parent().parent().hide();


                $('.smartframe-manage-themes').closest('.setting.sf-settings').hide();
                $('.smartframe-use-smartframe-caption').hide();
                $('.smartframe-use-smartframe-caption').parent().parent().hide();

                $('.smartframe--to-small-image').hide();
                $('#smartframe--info-attachment-image').find('p').remove();
                $('#smartframe--info-attachment-image').append('<p>⚠️ SmartFrame theme and caption are not available when image dimensions are smaller than 320x120px.</p>');

            } else {

                $('.smartframe-manage-themes').closest('.setting.sf-settings').show();
                $('.smartframe-theme-select').show();
                $('.smartframe-theme-select').parent().find('span').remove();

                $('.smartframe-use-smartframe-caption').show();

                $('.smartframe-theme-select').parent().parent().show();
                $('.smartframe-use-smartframe-caption').parent().parent().show()
                $('#smartframe--info-attachment-image').find('p').remove();
            }
        },
        insertSmartframe: function (myParam) {
            $.ajax({
                url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
                data: {
                    'action': 'loadSmartFrameByPostId',
                    'imageId': myParam
                },
                success: function (data) {
                    $('.smartframe-attachment-preview').remove();
                    if ($('.smartframe-use-smartframe-checkbox').prop('checked')) {
                        $('.thumbnail.thumbnail-image').prepend('<div class="sf-wrapper">' + data.template + '</div>');
                        $('.thumbnail.thumbnail-image p').remove();
                    }

                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        },
        loadSmartFrame: function (callback, id) {
            $.ajax({
                url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
                data: {
                    'action': 'loadSmartFrameByPostId',
                    'imageId': id
                },
                success: function (data) {
                    callback(data);
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        },
        waitForEl: function (selector, callback, waitAmount) {
            if ($(selector).length) {
                callback();
            } else {

                if (waitAmount >= 0) {
                    setTimeout(function () {
                        smartframeHelpers.waitForEl(selector, callback, --waitAmount);
                    }, 100);
                }
            }
        },

        setWidth: function (template) {
            $('.column-image img').last().show();
            template.css('width', $('.column-image img').last().css('width'));
            $('.column-image img').last().hide()
        }
    };
    $(document).ready(function () {


        let originalUrl = $('#smartframe-preview-page-button').attr('href');

        asd = function () {
            $('#smartframe-preview-page-button').click(function (event) {
                let theme = $('.smartframe--settings-table #properties-theme').find('option:selected').val();
                let disableCss = $($('.smartframe--use-css-classes').filter(function (number, item) {
                    return $(item).is(':checked');
                })[0]).attr('value');
                let disableCssClassList = $('#smartframe--disable-css-classes-list').val().replace(/ +/g, '');
                let enabledCssClassList = $('#smartframe--use-css-classes-list').val().replace(/ +/g, '');
                let useSmartframe = $('#properties-use-smartframe').find('option:selected').val();

                $(this).attr('href', originalUrl
                    + '?theme=' + theme
                    + '&disableCss=' + disableCss
                    + '&enabledCssClassList=' + enabledCssClassList
                    + '&disableCssClassList=' + disableCssClassList
                    + '&useSmartframe=' + useSmartframe);

            });
        };

        $("#smartframe--proporties-page-settings input,#smartframe--proporties-page-settings select").on("change paste keyup", function () {
            $('#submit').prop('disabled', false);
            $('#smartframe-preview-page-button').attr('disabled', false);
            $('#smartframe-preview-page-button').unbind('click');
            asd();
        });

        $("#smartframe--proporties-page-settings input,#smartframe--proporties-page-settings select").change(function () {
            $('#submit').prop('disabled', false);
            $('#smartframe-preview-page-button').attr('disabled', false);
            $('#smartframe-preview-page-button').unbind('click');
            asd();

        });
    });


    // Registration part in Proporties pages JAVA SCRIPTS
    var apiKey = '';
    var email = '';


    $('input[name="smartframe-email"]').on('change paste keyup', function () {
        $('#smartframe--proporties-page-register-form').find('.email-password-error').remove();
    });
    $('input[name="smartframe-token"]').on('change paste keyup', function () {
        $('#smartframe--properties-page-active-token').find('#smartframe-password-error').remove();
    });
    $('input[name="sfm_smartframe_apiKey"]').on('change paste keyup', function () {
        $('#smartframe--proporties-page').find('.error .smartframe-error').remove();
    });

    if (getCookie('smartframe-registration-data')) {
        data = JSON.parse(getCookie('smartframe-registration-data'));
        apiKey = data.apiKey;
        email = data.email;
        $('#smartframe-user-email').html(email);
        $('#smartframe--properties-page-register').hide();
        $('#smartframe--properties-page-active-token').show();
        $('.smartframe-status.wide').removeClass('border');
        $('.smartframe-status.wide .create').addClass('border');
        $('.smartframe-status.wide .update').hide();
        $('.smartframe-no-register.wide').hide();
    } else {
        $('.smartframe-status.wide .update').show();
    }

    $('#smartframe-back-to-register-form').on('click', function (event) {
        eraseCookie('smartframe-registration-data');
        $('#smartframe--properties-page-active-token .spinner.smartframe--loader').addClass('is-active');
        $('#smartframe--properties-page-active-token').addClass('smallOpacity');
        location.reload(true);
    });

    $('#smartframe--proporties-page-register-form').validate({
        rules: {
            // no quoting necessary
            'smartframe-name': {
                required: true,
                minlength: 2,
                maxlength: 255,
            },
            'smartframe-surname': {
                required: true,
                minlength: 2,
                maxlength: 255,
            },
            'smartframe-email': {
                required: true,
                email: true,
                maxlength: 75,
            },
            'smartframe-password': {
                required: true,
                minlength: 6,
                maxlength: 255,
            },
            'smartframe-privacy-policy': {
                required: true
            },
        },
        messages: {
            'smartframe-name': {
                required: 'This field is required',
                minlength: 'The input is less than 2 characters long',
                maxlength: 'The input is more than 255 characters long'
            },
            'smartframe-surname': {
                required: 'This field is required',
                minlength: 'The input is less than 2 characters long',
                maxlength: 'The input is more than 255 characters long',
            },
            'smartframe-email': {
                required: 'This field is required',
                email: "Please enter a valid email address",
                maxlength: 'Please enter a valid email address'
            },
            'smartframe-password': {
                required: 'This field is required',
                minlength: 'Password is less than 6 characters long',
                maxlength: 'Password is more than 255 characters long',
            },
            'smartframe-privacy-policy': {
                required: 'This field is required'
            },
            email: {
                required: "We need your email address to contact you",
                email: "Your email address must be in the format of name@domain.com"
            }
        },
        errorPlacement: function (error, element) {
            if (jQuery(element).prop('name') === 'smartframe-privacy-policy') {
                $(element).parent().find('label[for="smartframe-privacy-policy-id"]').css('color', 'red');
            } else {
                error.insertAfter(element.parent());
            }
        },
        highlight: function (element) {
            if ($(element).is(':radio') || $(element).is(':checkbox')) {
                $(element).parent().find('label[for="smartframe-privacy-policy-id"]').css('color', 'red');
            }
            $(element).addClass("error");
        },
        unhighlight: function (element) {
            if ($(element).is(':radio') || $(element).is(':checkbox')) {
                $(element).parent().find('label[for="smartframe-privacy-policy-id"]').css('color', 'black');
            }
            $(element).removeClass("error");
        },
        invalidHandler: function () {
        },
        showErrors: function (errorMap, errorList) {
            this.defaultShowErrors();  // default labels from errorPlacement
        },
        submitHandler: function (form) {
            $(form).find('button').prop('disabled', true);
            $('#smartframe--properties-page-register').addClass('smallOpacity');
            $('#smartframe--properties-page-register .spinner.smartframe--loader').addClass('is-active');
            let url;
            let method;
            if (!SmartFrameSettings.isApiKeyCorrect) {
                url = SmartFrameUrl.apiRegister;
                method = "POST";
            } else {
                url = ajaxurl;
                method = "GET";
            }
            $.ajax({
                method: method,
                url: url,
                data: {
                    action: 'connectNoRegisteredAccount',
                    email: $(form).find('input[name="smartframe-email"]').val(),
                    name: $(form).find('input[name="smartframe-name"]').val(),
                    surname: $(form).find('input[name="smartframe-surname"]').val(),
                    // companyName: $(form).find('input[name]').val(),
                    password: $(form).find('input[name="smartframe-password"]').val(),
                    externalImageSource: 'wordpress-plugin-image-source',
                    wpPluginApiUrl: SmartFrameUrl.wpPluginApiUrl

                }
            }).done(function (data) {
                $('#smartframe--properties-page-register').hide();
                $('#smartframe--properties-page-active-token').show();

                setCookie('smartframe-registration-data', JSON.stringify({
                    apiKey: data.apiKey,
                    email: data.email
                }), 1);

                apiKey = data.apiKey;
                email = data.email;
                $('#smartframe-user-email').html(email);

                $('.smartframe-status.wide').removeClass('border');
                $('.smartframe-status.wide .create').addClass('border');
                $('.smartframe-status.wide .update').fadeOut(500);
                $('.smartframe-no-register.wide').fadeOut(500);

            }).fail(function (data) {
                if (data.status === 422 && data.responseJSON.validationErrors.email !== undefined) {


                    if (data.responseJSON.validationErrors.email.length === 1) {
                        $.each(data.responseJSON.validationErrors.email, function (index, val) {
                            $(form).find('input[name="smartframe-email"]').parent().after('<label class="error email-password-error"  for="smartframe-password">This email already exists. <a href="https://panel.smartframe.cloud/forgot-password" target="_blank">Forgot password?</a></label>')
                        });
                    } else {

                        $(form).find('input[name="smartframe-email"]').parent().after('<label class="error email-password-error"  for="smartframe-password">Please enter a valid email address</label>')

                    }

                }
            }).always(function () {
                $(form).find('button').prop('disabled', false);
                $('#smartframe--properties-page-register').removeClass('smallOpacity');
                $('#smartframe--properties-page-register .spinner.smartframe--loader').removeClass('is-active');
            });

            // form.submit();
        }
    });

    $('#smartframe--proporties-page-settings').validate({
        rules: {
            // no quoting necessary
            'sfm_smartframe_option_enable_css_classes_list': {
                required:  '.smartframe--use-css-classes[value="include_images"]:checked',
                minlength: 2,
                maxlength: 255,
            },
            'sfm_smartframe_option_disabled_css_classes_list': {
                required: '.smartframe--use-css-classes[value="exclude_images"]:checked',
                minlength: 2,
                maxlength: 255,
            },
        },
        messages: {
            'sfm_smartframe_option_enable_css_classes_list': {
                required: 'This field is required',
                minlength: 'The input is less than 2 characters long',
                maxlength: 'The input is more than 255 characters long'
            },
            'sfm_smartframe_option_disabled_css_classes_list': {
                required: 'This field is required',
                minlength: 'The input is less than 2 characters long',
                maxlength: 'The input is more than 255 characters long',
            }
        },
        errorPlacement: function (error, element) {

                // error.insertAfter(element.parent());
        },
        highlight: function (element) {
            $(element).addClass("error");
        },
        unhighlight: function (element) {
            // if ($(element).is(':radio') || $(element).is(':checkbox')) {
            //     $(element).parent().find('label[for="smartframe-privacy-policy-id"]').css('color', 'black');
            // }
            $(element).removeClass("error");
        },
        invalidHandler: function () {
        },
        showErrors: function (errorMap, errorList) {
            this.defaultShowErrors();  // default labels from errorPlacement
        },
        submitHandler: function (form) {
            form.submit();
        }
    });


    $('#smartframe--proporties-page').validate({
        rules: {
            // no quoting necessary
            'sfm_smartframe_apiKey': {
                required: true
            }
        },
        messages: {
            'sfm_smartframe_apiKey': {
                required: "This field is required",
            }
        },
        submitHandler: function (form) {
            let tokenField = $(form).find('input[name="sfm_smartframe_apiKey"]');

            tokenField.val(tokenField.val().replace(/\s/g, ""));

            $(form).find('button').prop('disabled', true);
            $.ajax({
                method: "GET",
                url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
                data: {
                    'action': 'checkValidAccessCode',
                    'apiKey': tokenField.val()
                },
            }).done(function (data) {
                $.ajax({
                    url: ajaxurl, // or e
                    async: false,
                    data: {
                        'action': 'checkPrivacyPolicy',
                    },
                }).done(function (data) {

                });

                if (data.apiKeyValidation) {
                    form.submit();
                } else {
                    $(form).find('button').prop('disabled', false);

                    if (typeof data.errors.externalImageSource !== 'undefined') {
                        tokenField.after('<label class="error smartframe-error"  >' + data.errors.externalImageSource + '</label>')
                    }
                    if (typeof data.errors.apiKeyValidation !== 'undefined') {
                        tokenField.after('<label class="error smartframe-error"  >' + data.errors.apiKeyValidation + '</label>')
                    }
                }
            }).fail(function (data) {
                $(form).find('button').prop('disabled', false);
            }).always(function () {
            });

            // form.submit();
        }
    });


    $('#smartframe-no-register-checkbox').change(function (event) {
        $(this).prop('disabled', true);

        $('#smartframe--register-spinner').css('visibility', 'visible');


        window.onbeforeunload = function() {
            return "Data will be lost if you leave the page, are you sure?";
        };

        if (SmartFrameSettings.isApiKeyCorrect) {
            $.ajax({
                url: ajaxurl, // or e
                async: false,
                data: {
                    'action': 'checkPrivacyPolicy',
                },
            }).done(function (data) {

                $('#smartframe--register-spinner').css('visibility', 'hidden');
            });
            window.onbeforeunload = null;
            location.reload();
            return;
        }
        $.ajax({
            method: "POST",
            url: SmartFrameUrl.apiRegisterGuest,
            data: {
                // email: SmartFrameSettings.userEmail),
                email: SmartFrameSettings.userEmail.replace('@', '-no-register' + Math.floor(Math.random() * Math.floor(1000)) + '@'),
                wp_only_api_key: '',
                externalImageSource: 'wordpress-plugin-image-source',
                wpPluginApiUrl: SmartFrameUrl.wpPluginApiUrl
            }
        }).done(function (data) {
            $.ajax({
                url: ajaxurl, // or e
                async: false,
                data: {
                    'action': 'checkPrivacyPolicy',
                },
            }).done(function (data) {

            });
            window.onbeforeunload = null;
            let tokenVield = $('#smartframe--proporties-page-no-register').find('input[name="sfm_smartframe_apiKey"]');
            tokenVield.val(data.apiKey);
            $('#smartframe--proporties-page-no-register').submit();
        }).fail(function (data) {
            $('#smartframe--register-spinner').css('visibility', 'hidden');
            $(form).find('button').prop('disabled', false);
            if (data.status === 422 && data.responseJSON.validationErrors.email !== undefined) {
                if (data.responseJSON.validationErrors.email.length === 1) {
                    $.each(data.responseJSON.validationErrors.email, function (index, val) {
                        $(form).find('input[name="smartframe-email"]').parent().after('<label class="error email-password-error"  for="smartframe-password">This email already exists. <a href="https://panel.smartframe.cloud/forgot-password" target="_blank">Forgot password?</a></label>')
                    });
                } else {
                    $(form).find('input[name="smartframe-email"]').parent().after('<label class="error email-password-error"  for="smartframe-password">Please enter a valid email address</label>')
                }
                $(form).find('button').prop('disabled', false);
                $('#smartframe-no-register').removeClass('smallOpacity');
                $('#smartframe-no-register .spinner.smartframe--loader').removeClass('is-active');
            }
            window.onbeforeunload = null;
        }).always(function () {

        });

    });


    $('#smartframe--properties-page-active-token-form').validate({
        rules: {
            // no quoting necessary
            'smartframe-token': {
                required: true
            }
        },
        messages: {
            'smartframe-token': {
                required: "This field is required",
            }
        },
        submitHandler: function (form) {
            let tokenField = $(form).find('input[name="smartframe-token"]');

            tokenField.val(tokenField.val().replace(/\s/g, ""));

            $(form).find('button').prop('disabled', true);
            $('#smartframe--properties-page-active-token').addClass('smallOpacity');
            $('#smartframe--properties-page-active-token .spinner.smartframe--loader').addClass('is-active');
            $.ajax({
                method: "POST",
                url: SmartFrameUrl.apiActivate,

                data: {
                    token: $(form).find('input[name="smartframe-token"]').val(),
                    email: $('#smartframe-user-email').html(),
                }
            }).done(function (data) {
                $.ajax({
                    url: ajaxurl, // or e
                    async: false,
                    data: {
                        'action': 'checkPrivacyPolicy',
                    },
                }).done(function (data) {

                });
                $('#smartframe-without-valid-code').val(apiKey);
                eraseCookie('smartframe-registration-data');
                $('#smartframe--proporties-page-first-register').find('button').prop('disabled', false);
                $('#smartframe--proporties-page-first-register').find('button').click();
                $('#smartframe--proporties-page-first-register').find('button').prop('disabled', true);

            }).fail(function (data) {
                if (data.status === 422) {
                    $(form).find('input[name="smartframe-token"]').after('<label id="smartframe-password-error" class="error" for="smartframe-password">Please provide a valid activation code</label>')
                }
                $(form).find('button').prop('disabled', false);
                $('#smartframe--properties-page-active-token').removeClass('smallOpacity');
                $('#smartframe--properties-page-active-token .spinner.smartframe--loader').removeClass('is-active');
            }).always(function () {

            });

            // form.submit();
        }
    });

    //
    // var smartframImages = [
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-1.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-2.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-3.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-4.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-5.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-6.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-7.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-8.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-9.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-5.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-6.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-7.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-8.jpg',
    //     'https://s3-eu-west-1.amazonaws.com/assets.smartframe.io/onboarding/wordpress_plugin/compressed/background-9.jpg',
    // ];
    // //rotator
    // if ($('#smartframe--proporties-page-register-form').length === 1) {
    //     $('#wpwrap').css('background-image', 'url(' + smartframImages[Math.floor((Math.random() * 9) + 1)] + ')');
    //     $('#wpwrap').css('background-size', 'cover');
    //     preloadImage(smartframImages[0]);
    // }

    function preloadImage(url) {
        var img = new Image();
        img.src = url;
    }

    if ($('#smatframe--proporties-page-register-form').length === 1) {
        // if ($('#smatframe--proporties-page-register-form').length === 1 && SmartFrameCode === 0) {
        var cnt = 0, bg;
        var cnt2 = 1;
        var $body = $('#wpwrap');

        var bgrotater = setInterval(function () {
            if (cnt == 13) cnt = 0;
            if (cnt2 == 13) cnt2 = 0;
            preloadImage(smartframImages[cnt2]);
            bg = 'url("' + smartframImages[cnt] + '")';
            cnt++;
            cnt2++;
            $body.css({'background-image': bg, 'background-size': 'cover', 'transition': '2.5s'});
        }, 8000);
    }


    $('.smartframe-status.wide').fadeIn(500);

    //admin preview


    $(document).ready(function () {
        if ($('#properties-use-smartframe').find('option:selected').val() == 'no') {
            $('#smartframe-settings-area').attr('disabled', true);
        }


        $('#properties-use-smartframe').change(function (event) {
            if ($('#properties-use-smartframe').find('option:selected').val() == 'no') {
                $('#smartframe-settings-area').attr('disabled', true);
            } else {
                $('#properties-theme ,#smartframe--use-css-classes,#smartframe--use-css-classes-list').attr('disabled', false);
                $('#smartframe-settings-area').attr('disabled', false);

            }
        })

    });


    $(document).ready(function () {

        if (!getCookie('smartframe-incopatible-plugins-notification')) {
            $('.smartframe-plugin-incompatibility').show();
        }


        $('.smartframe-plugin-notification-remove').click(function (event) {
            $(this).closest('.is-dismissible').find('button.notice-dismiss').click()
            $.ajax({
                url: ajaxurl, // or e
                data: {
                    'action': 'noSupportedPluginList',
                },
            }).done(function (data) {

            });
        });
    });

    tippy('#smartframe-info-disabled-css-classes', {
        content: "Use this option if you don’t want particular images to be converted to SmartFrames. Just copy the CSS class of the image and paste it here without the dot (e.g. my-css-class, not .my-css-class). If you have multiple CSS classes, separate them with comma."
    });
    tippy('#smartframe-info-enabled-css-classes', {
        content: "Use this option if you want particular images to be converted to SmartFrames. Just copy the CSS class of the image and paste it here without the dot (e.g. my-css-class, not .my-css-class). If you have multiple CSS classes, separate them with comma.",
    });
    tippy('#smartframe-info-image-protection-select', {
        content: "When this option is enabled, your JPEG images are protected from right-click and download attempts, and are completely hidden from malicious web crawlers, regardless of the appearance you select.",
    });

})(jQuery);



