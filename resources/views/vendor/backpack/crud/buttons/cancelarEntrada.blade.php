@if ($crud->hasAccess('update'))
    <a href="#" data-route="{{ url($crud->route.'/'.$entry->getKey()).'/cancelar' }}" onclick="deleteEntry(this)" class="btn btn-sm btn-link"><i class="la la-ban"></i> Cancelar entrada</a>
@endif

<script>
    if (typeof deleteEntry != 'function') {
        $("[data-button-type=delete]").unbind('click');

        function deleteEntry(button) {
            var route = $(button).attr('data-route');

            swal({
                title: "{!! trans('backpack::base.warning') !!}",
                text: "Tem certeza que deseja cancelar entrada?",
                icon: "warning",
                buttons: true,
            }).then((willDelete) => {
                if (willDelete) {
                    // Redirecionar após a confirmação
                    window.location.href = route;
                }
            });
        }
    }
</script>
