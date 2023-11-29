let rows = $('tr>td>select[name="january"]')
$.each(rows, function(_, element){
    $(element).trigger('change')
})