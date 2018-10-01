let modal = $('<div class="modal fade" tabindex="-1" role="dialog">' +
    '<div class="modal-dialog" role="document">' +
    '<div class="modal-content">' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span></span></button>' +
    '<div class="modal-body"></div></div></div></div>');

$(document).ready($(function () {

    // Bootstrap 3 Modal
    $('.modal').modal({
        show: false,
        backdrop: true,
    });

    // show modal form
    $(document).on('click', '.show-modal', function () {
        let elem = $(this);
        showModal(elem.data('id'), elem.attr('href'));
        return false;
    });

    function showModal(id, href) {
        if ($('#' + id).length) {
            $('#' + id).modal('show');
        } else {
            let modal_copy = modal.clone();
            modal_copy
                .attr('id', id)
                .appendTo($('body'))
                .find('.modal-body')
                .load(
                    href,
                    function (response, status, xhr) {
                        if (response && status === 'success') {
                            $('#' + id).modal('show');
                        } else {
                            window.location.reload();
                        }
                    }
                );
        }
    }

    let state = 'paused';
    $(document).on('click', '.btn-control button', function(){
        let action = $(this).data('action');

        if ('reset' === action) {
            state = "paused";
            $.ajax({
                url: '/reset?reset=1',
                success: function (data) {
                    $.pjax.reload('#process', {
                        url: '/process',
                        replace: false,
                    }).done(function () {
                        $.pjax.reload('#text-list', {
                            url: '/dictionary',
                            replace: false,
                        }).done(function () {
                            $.pjax.reload('#word-list', {
                                url: '/dictionary',
                                replace: false,
                            });
                        });
                    });
                }
            });
        } else if ('start' === action) {
            state = 'process';

            $(this).prop('disabled', true);
            process();

        } else if ('pause' === action) {
            state = 'paused';
        } else if ('load-glossary' === action) {
            $(this).prop('disabled', true);
            $.ajax({
                url: '/load-glossary',
                success: function (data) {
                    $('.btn-control button[data-action=load-glossary]').prop('disabled', false);
                    $.pjax.reload('#glossary-pjax', {
                        url: '/glossary',
                        replace: false,
                    });
                },
                error: function () {
                    $('.btn-control button[data-action=load-glossary]').prop('disabled', false);
                }
            });
        }

    });

    function process() {
        $('.btn-control button[data-action=start]').prop('disabled', true);

        $.ajax({
            url: "/processing",
            success: function (data) {
                $('.btn-control button[data-action=start]').prop('disabled', false);

                if (data['html']) {
                    $('#process .all-progress').html(data['html']);
                }
                if (data['status'] === 'complete') {
                    state = "paused";
                    $.pjax.reload('#word-list', {
                        replace: false,
                    });
                }

                if (state === 'process') {
                    setTimeout(process, 1);
                }
            },
            error: function (data) {
                $('.btn-control button[data-action=start]').prop('disabled', false);

                alert('Error!')
            }
        });
    }
}));