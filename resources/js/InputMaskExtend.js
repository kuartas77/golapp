import Inputmask from "inputmask";

Inputmask.extendAliases({
    pesos: {
        prefix: "$",
        groupSeparator: ".",
        alias: "numeric",
        placeholder: "0",
        autoGroup: true,
        digits: false,
        digitsOptional: false,
        clearMaskOnLostFocus: true,
    },
})
