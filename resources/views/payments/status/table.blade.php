<div class="table-responsive-md">
    <table class="display compact cell-border" id="active_table" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">Deportista</th>
                <th class="text-center">Grupo</th>
                <th class="text-center">Enero</th>
                <th class="text-center">Febrero</th>
                <th class="text-center">Marzo</th>
                <th class="text-center">Abril</th>
                <th class="text-center">Mayo</th>
                <th class="text-center">Junio</th>
                <th class="text-center">Julio</th>
                <th class="text-center">Agosto</th>
                <th class="text-center">Septiembre</th>
                <th class="text-center">Octubre</th>
                <th class="text-center">Noviembre</th>
                <th class="text-center">Diciembre</th>
            </tr>
        </thead>
        <tbody id="table_body">
            @foreach($payments as $payment)
            <tr>
                <td class="text-center">{{$payment->names}}</td>
                <td class="text-center">{{$payment->group_name}}</td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->january]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->january_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->february]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->february_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->march]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->march_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->april]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->april_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->may]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->may_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->june]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->june_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->july]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->july_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->august]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->august_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->september]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->september_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->october]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->october_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->november]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->november_amount}}</span> -->
                </td>
                <td class="text-center">
                    {{config('variables.KEY_PAYMENTS_SELECT')[$payment->december]}}<br/>
                    <!-- <span class="payment_amount">{{$payment->december_amount}}</span> -->
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
                <th style="text-align:center"></th>
            </tr>
        </tfoot>
    </table>
</div>