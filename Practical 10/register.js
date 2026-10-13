
document.getElementById("registerForm").addEventListener("submit", function(event) {

    var name = document.getElementById("name").value.trim();
    var username = document.getElementById("username").value.trim();
    var email = document.getElementById("email").value.trim();
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmPassword").value;

    if (name === "" || username === "" || email === "" ||
        password === "" || confirmPassword === "") {
        event.preventDefault();
        alert("Please fill all the fields.");
    }

    else if (username.length < 3 || username.length > 50 ||
             !/^[A-Za-z0-9_]+$/.test(username)) {
        event.preventDefault();
        alert("Username must be 3-50 characters and contain only letters, numbers or underscores.");
    }

    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        event.preventDefault();
        alert("Please enter a valid email address.");
    }

    else if (password.length < 7) {
        event.preventDefault();
        alert("Password must be at least 7 characters long.");
    }

    else if (!/[0-9]/.test(password)) {
        event.preventDefault();
        alert("Password must contain at least 1 number.");
    }

    else if (!/[@#]/.test(password)) {
        event.preventDefault();
        alert("Password must contain at least one symbol: @ or #.");
    }

    else if (password !== confirmPassword) {
        event.preventDefault();
        alert("Passwords do not match.");
    }

});
