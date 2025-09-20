<div class="modal" id="modify_payment" aria-hidden="true">
    <div class="modal-dialog modal-xl w-100">
        <div class="modal-content">
            <form action="#" id="form_payments_modal" class="form-material m-t-0" method="POST" aria-hidden="true">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="text-uppercase"><strong>Pago Mensualidades</strong></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body m-l-20 m-r-20">

                    @include('payments.payment.fields')

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect text-left" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
</script>
@endpush