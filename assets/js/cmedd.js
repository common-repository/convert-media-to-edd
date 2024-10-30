(function( $ ) { 'use strict';
    /**
     * global CMEDD_Cron
     */

    var cmedd_form          = $( '#cmedd-cron-form' );
    var error_el            = cmedd_form.parent().find( '.error' );
    var reset_btn           = $( '#cmedd-cron-reset-btn' );
    var status_msg_el       = $( '#status-message' );
    var cmedd_msg_el        = $( '#cmedd-message' );
    var cmedd_submit_btn    = $( '#cmedd-cron-submit-btn' );
    var cmedd_media_ids     = $( '#cmedd_media_ids' );

    openMediaLibrary();

    // Start polling for current status
    cronPolling();

    cmedd_form.on( 'submit', cmedd_from_action);

    reset_btn.on( 'click', reset_btn_action);

    $('#cmedd_download_cat').multiSelect();

    function cmedd_from_action(e) {
        e.preventDefault();

        // Validation 
        if( cmedd_media_ids.val() == '' )
        {
            alert('No Media Attachment has been added!');
            return false;
        }

        error_el.addClass( 'hidden' );

        cmedd_submit_btn.attr( 'disabled', 'disabled' );

        /**
         * If cron is running, don't push another scheduler
         */
        if ( CMEDD_Cron.running ) {
            return;
        }

        tinyMCE.triggerSave();

        var form_data = cmedd_form.serializeArray();

        $.ajax( {
            url: wp.ajax.settings.url,
            type: 'POST',
            data: form_data,
            error: function ( data ) {
                cmedd_submit_btn.removeAttr( 'disabled', 'disabled' );
                throw new Error( data );
            },
            success: function ( data ) {
                CMEDD_Cron = data;

                if ( data.error ) {
                    return errorMessage();
                }

                var msg_el = $( '#cmedd-cron-msg' );
                msg_el.text( '' );

                if ( CMEDD_Cron.msg ) {
                    msg_el.text( CMEDD_Cron.msg );
                }

                statusMessage();
                cronPolling();
            }
        } );
    }

    function reset_btn_action() {
        reset_btn.attr( 'disabled', 'disabled' );

        $.ajax( {
            url: wp.ajax.settings.url,
            type: 'POST',
            data: {
                action: 'cmedd_cron_reset'
            },
            error: function ( data ) {
                reset_btn.removeAttr( 'disabled' );
                throw new Error( data );
            },
            success: function ( data ) {
                if ( 'msg' in data ) {
                    $( '#cmedd-cron-reset' ).text( data.msg );

                    cmedd_msg_el
                        .removeClass( 'hidden' )
                        .find( '#cmedd-cron-reset' )
                        .removeClass( 'hidden' )
                        .parent()
                        .find( '#status-message' )
                        .addClass( 'hidden' );
                }
            }
        } );
    }

    function openMediaLibrary() {
        if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.editor ) {
            throw new Error( 'window.object not loaded' );
        }

        var media_ids = wp.media({
            title: 'Insert Media',
            library: {type: 'image'},
            multiple: true,
            button: {text: 'Add'}
        });

        media_ids.on( 'select', function() {
            var collection = media_ids.state().get( 'selection' ).toJSON();
            collection.forEach( function( attachment ) {
                if ( cmedd_media_ids.val() != '' ) {
                    return cmedd_media_ids.val( cmedd_media_ids.val() + '\n' + attachment.id );
                }

                cmedd_media_ids.val( attachment.id );
            } );
        } );

        $( document ).on( 'click', '.cmedd_media_ids', function( e ) {
            e.preventDefault();
            media_ids.open();
        });
    }

    function cronPolling() {
        var data = { action: 'cmedd_cron_polling' };

        $.post( wp.ajax.settings.url, data, function( data ) {
            CMEDD_Cron = data;

            statusMessage();

            if ( CMEDD_Cron.running ) {
                reset_btn.attr( 'disabled', 'disabled' );
                cmedd_submit_btn.attr( 'disabled', 'disabled' );
                return setTimeout( cronPolling, 2000 );
            } else {
                cmedd_submit_btn.removeAttr( 'disabled' );
            }

            if ( ! CMEDD_Cron.running && CMEDD_Cron.data ) {
                reset_btn.removeAttr( 'disabled' );
            }
        } );
    }

    function statusMessage() {
        if ( ! CMEDD_Cron || ! CMEDD_Cron.data ) return;

        cmedd_msg_el
            .removeClass( 'hidden' )
            .find( '#cmedd-cron-reset' )
            .addClass( 'hidden' )
            .parent()
            .find( '#status-message' )
            .removeClass( 'hidden' );

        if ( CMEDD_Cron.data.status ) {
            $( '#cmedd-cron-status' ).text( CMEDD_Cron.data.status );
        }

        if ( 'processed' in CMEDD_Cron.data ) {
            $( '#cmedd-cron-processed' ).text( CMEDD_Cron.data.processed );
        }

        if ( 'remaining' in CMEDD_Cron.data ) {
            $( '#cmedd-cron-remaining' ).text( CMEDD_Cron.data.remaining );
        }

        if ( 'total' in CMEDD_Cron.data ) {
            $( '#cmedd-cron-total' ).text( CMEDD_Cron.data.total );
        }

        if ( CMEDD_Cron.log_url ) {
            $( '#cmedd-cron-url' ).find( 'a' ).attr( 'href', CMEDD_Cron.log_url );
        }

    }

    function errorMessage() {
        error_el.removeClass( 'hidden' );
        error_el.html( '<p>' + CMEDD_Cron.msg + '</p>' );
    }

})( jQuery );
