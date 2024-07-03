// script.js
(function() {
    function pigRoll() {
        var rollButton = document.getElementById('roll-button');
        if (rollButton){
            rollButton.addEventListener('click', function() {
                var form = document.getElementById('roll-form');
                form.action = "{{ path('pig_roll') }}";
            });
        }

        Array.from(document.getElementsByClassName("post-link")).forEach(function(element) {
            element.addEventListener("click", function(event) {
                event.preventDefault();
                element.closest("form").submit();
            });
        });
    }

    function fiveCard() {
        const cards = document.querySelectorAll(".interactive-card");
        const selectedCardsInput = document.getElementById('selectedCards');
        let selectedCards = [];

        cards.forEach(card => {
            card.addEventListener('click', () => {
                const cardValue = card.getAttribute('data-card');

                if (selectedCards.includes(cardValue)) {
                    selectedCards = selectedCards.filter(c => c !== cardValue);
                    card.classList.remove('highlighted');
                } else {
                    if (selectedCards.length < 3) {
                        selectedCards.push(cardValue);
                        card.classList.add('highlighted');
                    }
                }

                selectedCardsInput.value = selectedCards.join(',');
            });
        });
    }

    pigRoll();
    fiveCard();
})();

