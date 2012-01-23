<div class="wrap">
	<h1>Mief.nl - Slideshow</h1>

	<div class="upload_form">
		<form action="" method="post" enctype="multipart/form-data">
			<label for="mief_slideshow_file">
				<span>Add a new photo</span>
				<input type="file" id="mief_slideshow_file" name="mief_slideshow_file">
			</label>

			<input type="submit" value="Save Photo">
		</form>
	</div>
	<hr>
	<div class="overview">
		<form action="" method="post">
			<input action="delete" type="hidden">
			<input type="submit" value="Save changes">
			<table>
				<tr>
					<th>Weight</th>
					<th>Thumbnail</th>
					<th>url</th>
				</tr>
				<?php foreach ( $photos as $photo ) : ?>
				<tr>
					<td>
						<?php echo $photo->weight; ?>
					</td>
					<td>
						<img src="<?php echo $photo->filename['url']; ?>" width="150"><br>
						<label for="mief_slideshow_weight<?php echo $photo->id ?>">
							<input type="text" size="2" name="mief_slideshow_weight[<?php echo $photo->id ?>]" id="mief_slideshow_weight<?php echo $photo->id ?>" value="<?php echo $photo->weight ?>">
						</label>
						<label for="mief_slideshow_delete<?php echo $photo->id?>">
							<input type="checkbox" name="mief_slideshow_delete[<?php echo $photo->id ?>]"
								   id="mief_slideshow_delete<?php echo $photo->id ?>">
							&nbsp; Delete
						</label>
					</td>
					<td>
						<input type="text" name="mief_slideshow_url[<?php echo $photo->id ?>]" id="mief_slideshow_<?php echo $photo->id ?>" value="<?php echo $photo->url; ?>" size="60">
					</td>
				</tr>
				<?php endforeach; ?>
			</table>

			<input type="submit" value="Save changes">
		</form>
	</div>
</div>