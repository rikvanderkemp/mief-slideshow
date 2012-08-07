<div class="mief_slider">
    <ul>
        <?php foreach ($photos as $photo) : ?>
        <li>
            <a href="<?php echo $photo->url; ?>">
                <img src="<?php echo $photo->filename['url']; ?>" alt="">
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php
        if (isset($slideshow->settings['buttons']) && $slideshow->settings['buttons'] == true):
    ?>
    <div class="controls">
        <div class="prev"></div>
        <!--		<div class="pause"></div>-->
        <div class="next"></div>
    </div>
    <?php endif; ?>

    <div class="progress"></div>
</div>