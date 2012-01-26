<div class="wrap">
    <h1>Slideshow index</h1>

    <p>Select one of the slideshows to edit</p>

    <ul>
        <?php foreach ($slideshows as $slideshow) : ?>
        <li><a href="?page=mief_slideshow_plugin&mp=upload&mid=<?php echo $slideshow->slideshow_id;  ?>"><?php echo $slideshow->title; ?></a></li>
        <?php endforeach; ?>
        <li>
            <form action="" method="post">
                <label for="mief_create">
                    Create a new slideshow
                    <input type="text" id="mief_create" name="mief_create">
                    <input type="submit" value="create">
                </label>
            </form>
        </li>
    </ul>
</div>