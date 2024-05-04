import Inputmask from "inputmask";

Inputmask.extendAliases({
    pesos: {
        prefix: "$ ",
        alias: "numeric",
        placeholder: "0.00",
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        clearMaskOnLostFocus: false,
        radixPoint: ','
    },
})
