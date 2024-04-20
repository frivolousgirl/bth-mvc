// script.js
(function() {
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
})();