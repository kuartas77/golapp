<input
    type="text"
    min="0"
    name="value"
    value="{{$value}}"
    class="form-control form-control-sm payments_amount"
    style="width: 20%;"
    {{($deleted || isInstructor()) ? 'disabled' : ''}}>