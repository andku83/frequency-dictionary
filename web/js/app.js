var modal = $('<div class="modal fade" tabindex="-1" role="dialog">' +
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
        var elem = $(this);
        showModal(elem.data('id'), elem.attr('href'));
        return false;
    });

    function showModal(id, href) {
        if ($('#' + id).length) {
            $('#' + id).modal('show');
        } else {
            var modal_copy = modal.clone();
            modal_copy
                .attr('id', id)
                .appendTo($('body'))
                .find('.modal-body')
                .load(
                    href,
                    function (response, status, xhr) {
                        if (response && status == 'success') {
                            $('#' + id).modal('show');
                        } else {
                            window.location.reload();
                        }
                    }
                );
        }
    }
}));