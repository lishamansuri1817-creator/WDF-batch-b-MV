document.getElementById("registerForm").addEventListener("submit", function(event) {
    event.preventDefault();
    var name = document.getElementById("name").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmPassword").value;
    if (name == "" || email == "" || password == "" || confirmPassword == "") {
        alert("Please fill all the fields.");
    }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Please enter a valid email address.");
    }
    else if (password.length < 7) {
        alert("Password must be at least 7 characters long.");
    }
    else if (!/[0-9]/.test(password)) {
        alert("Password must contain at least 1 number.");
    }
    else if (!/[@#]/.test(password)) {
        alert("Password must contain at least one symbol: @ or #.");
    }
    else if (password != confirmPassword) {
        alert("Passwords do not match.");
    }
    else {
        alert("Registration successful!");
        window.location.href = "dashboard.html";
    }
});