var searchButton = document.getElementById("searchButton");

var showAllButton = document.getElementById("showAllButton");

var searchBox = document.getElementById("collegeSearch");

var colleges = document.querySelectorAll(".college");

var message = document.getElementById("searchMessage");


searchButton.addEventListener("click", function() {

    var searchText = searchBox.value.toLowerCase().trim();

    if (searchText == "") {

        alert("Please enter a college name or stream.");

        return;

    }

    var found = false;

    colleges.forEach(function(college) {

        var collegeText = college.innerText.toLowerCase();

        if (collegeText.includes(searchText)) {

            college.style.display = "block";

            found = true;

        }
        else {

            college.style.display = "none";

        }

    });


    if (found) {

        message.innerText = "College results found.";

    }
    else {

        message.innerText = "No colleges found for: " + searchText;

    }

});


showAllButton.addEventListener("click", function() {

    colleges.forEach(function(college) {

        college.style.display = "block";

    });

    searchBox.value = "";

    message.innerText = "";

});


var detailButtons = document.querySelectorAll(".details-button");


detailButtons.forEach(function(button) {

    button.addEventListener("click", function() {

        var college = button.parentElement;

        var collegeName = college.querySelector("h3").innerText;

        alert("You selected: " + collegeName);

    });

});