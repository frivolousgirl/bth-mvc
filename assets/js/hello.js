export default () => {
    return `Yo yo - welcome to Encore!`;
};

(function () {
    "use strict";

    const getGreeting = function () {
        const date = new Date();

        if (date.getHours() >= 5 && date.getHours() <= 9) {
            return "God morgon";
        }
        if (date.getHours() >= 10 && date.getHours() <= 16) {
            return "Hejsan";
        }
        if (date.getHours() >= 17 && date.getHours() <= 21) {
            return "God kväll";
        }
        return "God natt";
    };

    const greeting = getGreeting();
    let greetingElement = document.getElementById("greeting");

    greetingElement.textContent = greeting;
})();
