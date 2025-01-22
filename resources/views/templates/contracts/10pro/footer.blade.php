<htmlpagefooter name="page-footer">
    <table class="table-full title">
        <tr>
            <td class="text-center" width="100%">
                <p><strong>Centro de Desarrollo y Potencialización de Talento 10+PRO S.A.S.</strong></p>
                @switch($school->id)
                    @case(5)
                        <p><strong>matezuluaga@hotmail.com</strong></p>
                        <p><strong>Contacto: 3045567698</strong></p>
                        @break

                    @case(6)
                        <p><strong>direccion.formativa@10maspro.com</strong></p>
                        <p><strong>Contacto: 3045567698</strong></p>
                        @break

                    @case(7)
                        <p><strong>contacto@10maspro.com</strong></p>
                        <p><strong>Contacto: 3007760365</strong></p>
                        @break
                @endswitch
            </td>
        </tr>
    </table>
</htmlpagefooter>
