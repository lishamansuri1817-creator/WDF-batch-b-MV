var buttons = document.querySelectorAll(".discussion-button");


buttons.forEach(function(button) {

    button.addEventListener("click", function() {

        var post = button.parentElement;

        var title = post.querySelector("h3").innerText;

        if (button.innerText == "Join Discussion") {

            alert("You joined the discussion:\n" + title);

            button.innerText = "Joined";

        }

        else if (button.innerText == "View Post") {

            alert("Opening post:\n" + title);

            button.innerText = "Viewed";

        }

        else if (button.innerText == "Explore") {

            alert("Exploring internship opportunities:\n" + title);

            button.innerText = "Explored";

        }

    });

});