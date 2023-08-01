<input 
    type="text" 
    min="0" 
    name="{{$mes}}_amount" 
    value="{{$value}}" 
    class="form-control form-control-sm payments_amount" 
    {{($deleted || isInstructor()) ? 'disabled' : ''}}>