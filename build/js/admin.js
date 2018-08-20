jQuery(function($) {
    $('#add_attachments').click(function(e) {
        e.preventDefault();
        var image_frame;

        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
            title: 'Select Attachments',
            multiple : true
        });

        image_frame.on('close',function() {
            var selection =  image_frame.state().get('selection');

            $('.attachment-item:not(.-original)').remove();

            selection.each(function(attachment) {
                var clone = $('.attachment-item.-original').clone();
                clone.appendTo('.attachment-clones')
                    .removeClass('-original')
                    .find('.attachment-input')
                    .val(attachment['attributes']['id']);

                if (attachment['attributes']['type'] == 'image') {
                    clone.css('background-image', 'url("' + attachment['attributes']['url'] + '")');
                } else {
                    clone.css('background-image', 'url("' + mail_catcher_logs.plugin_url + '/assets/file-icon.png")');
                }
            });
        });

        image_frame.on('open',function() {
            var selection =  image_frame.state().get('selection');

            $('.attachment-input').each(function() {
                var attachment = wp.media.attachment($(this).val());
                attachment.fetch();
                selection.add( attachment ? [ attachment ] : [] );
            });
        });

        image_frame.open();
    });

    $(document).on('click', '.attachment-item .remove', function() {
        $(this).closest('.attachment-item').remove();
        return false;
    });

    $(document).on('click', '.attachments-container .attachment-item', function() {
        $('#add_attachments').trigger('click');
       return false;
    });

    $('.modal-body .nav-tab').on('click', function() {
        var modal_id = $(this).closest('.modal').attr('id');
        var active_index = $(this).index();

        $('#' + modal_id + ' .nav-tab.nav-tab-active').removeClass('nav-tab-active');
        $('#' + modal_id + ' .content-container .content.-active').removeClass('-active');

        $('#' + modal_id + ' .nav-tab').eq(active_index).addClass('nav-tab-active');
        $('#' + modal_id + ' .content-container .content').eq(active_index).addClass('-active');

        return false;
    });

    $('[data-target]').on('click', function() {
        $('.modal.-active').removeClass('-active');
        $($(this).data('target')).addClass('-active');
        return false;
    });

    $('.dismiss-modal').on('click', function() {
        $('.modal.-active').removeClass('-active');
    });

    $('.field-block .add-field').on('click', function() {
        var field_block = $(this).closest('.field-block');
        var cloneable = $(this).closest('.cloneable');

        cloneable.find('.remove-field.-disabled').removeClass('-disabled');

        field_block.clone(true).appendTo(cloneable);

        cloneable.find('.remove-field').eq(0).addClass('-disabled');

        return false;
    });

    $('.field-block .remove-field').on('click', function() {
        if ($(this).hasClass('-disabled')) {
            return false;
        }

        $(this).closest('.field-block').remove();
        return false;
    });
});
