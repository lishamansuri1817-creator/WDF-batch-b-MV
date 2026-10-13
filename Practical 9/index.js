document.getElementById("searchButton").addEventListener("click", function() {

    var searchText = document.getElementById("searchBox").value.toLowerCase().trim();

    var cards = document.querySelectorAll(".card");

    var message = document.getElementById("searchMessage");

    if (searchText == "") {

        alert("Please enter something to search.");

        return;
    }

    var found = false;

    cards.forEach(function(card) {

        var cardText = card.innerText.toLowerCase();

        if (cardText.includes(searchText)) {

            card.style.display = "block";

            found = true;

        }
        else {

            card.style.display = "none";

        }

    });

    if (found) {

        message.innerText = "Search results found.";

    }
    else {

        message.innerText = "No results found for: " + searchText;

    }

});