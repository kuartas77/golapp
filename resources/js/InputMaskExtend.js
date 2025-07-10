import Inputmask from "inputmask";

Inputmask.extendAliases({
    pesos: {
        prefix: "$ ",
        groupSeparator: ".",
        alias: "numeric",
        placeholder: "0",
        autoGroup: !0,
        digits: 0,
        digitsOptional: false,
        clearMaskOnLostFocus: false,
    },
})
