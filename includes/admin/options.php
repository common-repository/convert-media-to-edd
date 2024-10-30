<div class="wrap">
    <h2>Options</h2>

    <form method="post" action="" id="cmedd-cron-form">
        <div id="cmedd-message" class="updated hidden">
            <p>
                <span id="status-message" class="hidden">
                    <strong>Status:</strong> <span id="cmedd-cron-status"></span><br>
                    <strong>Processed:</strong> <span id="cmedd-cron-processed"></span><br>
                    <strong>Remaining:</strong> <span id="cmedd-cron-remaining"></span><br>
                    <strong>Total:</strong> <span id="cmedd-cron-total"></span><br>
                    <span id="cmedd-cron-url">
                        <a href="javascript:void(0);" target="_blank">View log file</a>
                    </span><br>
                    <span id="cmedd-cron-msg"></span>
                </span>
                <span id="cmedd-cron-reset" class="hidden"></span>
            </p>
        </div>
        <div class="error hidden"></div>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_media_ids">Media Attachments</label>
                </th>
                <td>
                    <div><button class="cmedd_media_ids button" type="button">Add media IDs</button></div>
                    <textarea name="cmedd_media_ids" id="cmedd_media_ids" cols="30" rows="10"></textarea>
                    <p class="description">
                        Media ids should contain one per each line. Only attachment type image will be processed.<br>
                        Note: This field will be cleared out, once reset.
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_description">Description</label>
                </th>
                <td>
                    <?php wp_editor( '', 'cmedd_description' ); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_download_cat">Download Categories</label>
                </th>
                <td>               
                    <select multiple="multiple" id="cmedd_download_cat" name="cmedd_download_cat[]">
                    <?php foreach( $download_categories as $cats ) : ?>
                        <option value="<?php echo $cats->term_id; ?>"><?php echo $cats->name; ?></option>
                    <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_price">Price</label>
                </th>
                <td>
                    <input type="text" placeholder="0.00" id="cmedd_price" name="cmedd_price">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_download_limit">Download Limit</label>
                </th>
                <td>
                    <input type="text" value="0" id="cmedd_download_limit" name="cmedd_download_limit">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="cmedd_download_notes">Download Notes</label>
                </th>
                <td>
                    <textarea id="cmedd_download_notes" name="cmedd_download_notes" style="width: 100%" rows="4"></textarea>
                </td>
            </tr>
        </table>

        <input type="hidden" value="<?php echo wp_create_nonce( CMEDD_Options::NONCE ); ?>" name="_wp_nonce">
        <input type="hidden" value="cmedd_cron" name="action">

        <p class="submit">
		    <?php
            $attrs = array( 'id' => 'cmedd-cron-submit-btn' );
            if ( CMEDD_Cron::last_data() && CMEDD_Cron::is_running() ) {
                $attrs['disabled'] = 'disabled';
            }
            submit_button( 'Execute', 'primary', '', false, $attrs ); ?>
            <input class="button button-primary" value="Reset" id="cmedd-cron-reset-btn" type="button"
                <?php echo ( CMEDD_Cron::last_data() && ! CMEDD_Cron::is_running() ) ? '' : 'disabled'; ?>>
            <span alt="f223" class="edd-help-tip dashicons dashicons-editor-help"
                  title="Clear out the pending/completed job"></span>
        </p>

    </form>
</div>
