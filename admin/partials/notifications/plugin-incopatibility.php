<script></script>

<div class="notice notice-warning notice is-dismissible smartframe-plugin-incompatibility">

    <?php if (count($list) === 1): ?>
        <p>
            Heads up! The SmartFrame plugin might clash with the <?php echo current($list); ?> plugin you’re using.
        </p>
    <?php else: ?>
        <p>
            <?php $last = array_pop($list); ?>

            Heads up! The SmartFrame plugin might clash with some of the other plugins you're
            using: <?php echo trim(implode(', ', $list)); ?> and
            <?php echo $last ?>.
        </p>
    <?php endif; ?>
    <p><strong><a href="<?php echo admin_url() . 'plugins.php' ?>">Manage Plugins</a> | <a
                    class="smartframe-plugin-notification-remove" href="#">Don't show it
                again</a></strong></p>
    <button type="button" class="notice-dismiss"></button>
</div>