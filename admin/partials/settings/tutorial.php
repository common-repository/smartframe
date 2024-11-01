<div class="wrap">
    <!--    <h2>--><?php //echo esc_html( get_admin_page_title() ); ?><!--</h2>-->
    <h2>Help</h2>

    <hr>
    <br>

    <div class="tutorial__item">
        <i class="fas fa-book-reader"></i>
        <div>
            <span class="tutorial-title">Getting Started with SmartFrame Plugin</span>
            <p>Learn how to start using SmartFrame Plugin and get the most out of it!
            </p>
            <a href="https://smartframe.io/wordpress?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php

            echo $_SERVER['HTTP_HOST'] ?>&utm_content=Learn%20more" target="_blank" class="button">Learn More</a>
        </div>
    </div>

    <div class="tutorial__item">
        <i class="fas fa-tools"></i>
        <div>
            <span class="tutorial-title">SmartFrame Admin Panel</span>
            <p class="tutorial--description">
                Our plugin was designed and built to give you a full experience without having to use our external
                platform. However, you can manage all your uploaded images, see tracking data (such as views, clicks or
                or even download attempts) of your embedded images, customise Themes and much more in our panel!</p>
            <a target="_blank"
               href="https://panel.smartframe.cloud/login?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Go%20to%20my%20panel"
               class="button">
                Go to my panel
            </a>
        </div>
    </div>

    <div class="tutorial__item">
        <i class="fas fa-images"></i>
        <div>
            <span class="tutorial-title">SmartFrame Features</span>
            <p>
                See examples of what can be achieved with SmartFrame technology.
            </p>
            <a href="<?php echo \SmartFrameLib\App\MenuHandlers\ThemeMenuHandler::menuLinkProvider() ?>"
               class="button">See examples</a>
        </div>
    </div>


    <div class="tutorial__item">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <span class="tutorial-title">Troubleshooting</span>
            <p>Experiencing some problems with the SmartFrame Plugin? <a
                        href="https://smartframe.io/support/wordpress-plugin?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Look%20for%20a%20solution%20here"
                        target="_blank"> Look for a solution
                    here.</a></p>
        </div>
    </div>

    <div class="tutorial__item">
        <i class="fas fa-comments"></i>
        <div>
            <span class="tutorial-title">Do you have more questions?</span>
            <p>Visit our <a target="_blank"
                            href="https://smartframe.io/support?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Support%20pages">support
                    pages</a> or contact us
                directly at <a
                        href="mailto:support@smartframe.io?subject=Support request for WordPress plugin - <?php echo SmartFrameLib\App\SmartFramePlugin::$VERSION ?>">support@smartframe.io</a>
            </p>
        </div>
    </div>
</div>
