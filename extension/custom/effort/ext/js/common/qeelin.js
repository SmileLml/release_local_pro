function roundToTwoDecimals(num, method = "round") {
    const factor = 100;
    switch (method) {
        case "ceil":
            return Math.ceil(num * factor) / factor;
        case "floor":
            return Math.floor(num * factor) / factor;
        case "round":
        default:
            return Math.round(num * factor) / factor;
    }
}