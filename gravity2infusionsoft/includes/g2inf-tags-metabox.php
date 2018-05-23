<div class="form-grp">
    <div class="half first">
        <label>Add Tags</label> <br />
        <select name="_add_tags[]" multiple>
            <option>---</option>
            <?php
            foreach ($tags as $tag) {
                $selected = in_array($tag['id'], json_decode(get_post_meta($post->ID, '_add_tags', true))) ? 'selected' : '';
                echo '<option value="'.$tag['id'].'" '.$selected.'>'.$tag['GroupName'].'</option>';
            }
            ?>
        </select>
    </div>
    <div class="half">
        <label>Remove Tags</label> <br />
        <select name="_remove_tags[]" multiple>
            <option>---</option>
            <?php
            foreach ($tags as $tag) {
                $selected = in_array($tag['id'], json_decode(get_post_meta($post->ID, '_remove_tags', true))) ? 'selected' : '';
                echo '<option value="'.$tag['id'].'" '.$selected.'>'.$tag['GroupName'].'</option>';
            }
            ?>
        </select>
    </div>
</div>
