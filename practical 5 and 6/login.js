document.getElementById("loginForm").addEventListener("submit", function(event) {

    event.preventDefault();

    var email = document.getElementById("loginEmail").value;
    var password = document.getElementById("loginPassword").value;

    if (email == "" || password == "") {

        alert("Please enter email and password.");

    }
    else {

        alert("Login successful!");

        window.location.href = "dashboard.html";

    }

});